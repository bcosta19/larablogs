<?php

use App\Models\Blog;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\ArticleReader;
use App\Services\PublicArticleUrl;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['is_owner' => true]));
});

function readerBlog(string $title): Blog
{
    return Blog::create(['title' => $title, 'url' => 'https://example.com', 'feed_url' => 'https://example.com/feed', 'notify_email' => 'reader@example.com', 'is_active' => true]);
}

function readerPost(Blog $blog, string $title, string $url = 'https://example.com/article'): BlogPost
{
    return BlogPost::create(['blog_id' => $blog->id, 'title' => $title, 'url' => $url, 'guid' => $title, 'published_at' => now()]);
}

test('blog page lists only its own posts newest first', function () {
    $blog = readerBlog('First blog');
    readerPost($blog, 'Older')->update(['published_at' => now()->subDay()]);
    readerPost($blog, 'Newest');
    readerPost(readerBlog('Other blog'), 'Other article');

    $this->get('/blogs/'.$blog->id)->assertInertia(fn (Assert $page) => $page
        ->component('BlogPosts')->has('posts.data', 2)
        ->where('posts.data.0.title', 'Newest')->where('posts.data.1.title', 'Older'));
});

test('reader extracts text without running or returning scripts and navigation', function () {
    $blocks = (new ArticleReader)->extract('<html><body><nav><p>Navigation</p></nav><article><h2>Olá &amp; hello</h2><p>Real <strong>article</strong> text<script>alert(1)</script>.</p><pre>first\nsecond</pre></article></body></html>');
    expect($blocks[0]['text'])->toBe('Olá & hello')
        ->and($blocks[1]['text'])->toBe('Real article text.')
        ->and(json_encode($blocks))->not->toContain('alert(1)')->not->toContain('Navigation');
});

test('reader fetches a public article and returns structured blocks', function () {
    $this->app->instance(PublicArticleUrl::class, new class extends PublicArticleUrl
    {
        protected function addresses(string $host): array
        {
            return ['93.184.216.34'];
        }
    });
    Http::fake(['example.com/article' => Http::response('<article><h2>Article section</h2><p>Readable content.</p></article>', 200, ['Content-Type' => 'text/html'])]);
    $post = readerPost(readerBlog('A blog'), 'An article');

    $this->get('/posts/'.$post->id.'/read')->assertInertia(fn (Assert $page) => $page
        ->component('Reader')->where('unavailable', false)->where('blocks.1.text', 'Readable content.'));
});

test('reader blocks redirects into the private network', function () {
    Http::fake(['https://93.184.216.34/article' => Http::response('', 302, ['Location' => 'http://127.0.0.1/admin'])]);
    $post = readerPost(readerBlog('A blog'), 'Private redirect', 'https://93.184.216.34/article');
    $this->get('/posts/'.$post->id.'/read')->assertInertia(fn (Assert $page) => $page->where('unavailable', true));
    Http::assertSent(fn ($request) => $request->url() === 'https://93.184.216.34/article');
    Http::assertNotSent(fn ($request) => $request->url() === 'http://127.0.0.1/admin');
});

test('reader rejects non-public or non-http targets', function (string $url) {
    expect(fn () => (new PublicArticleUrl)->resolve($url))->toThrow(RuntimeException::class);
})->with(['http://127.0.0.1', 'http://10.0.0.1', 'http://100.100.80.231', 'http://[::1]', 'http://[::ffff:127.0.0.1]', 'file:///etc/passwd', 'https://user:pass@example.com', 'http://192.168.1.1:8080']);
