<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogPost;
use App\Services\ArticleFetcher;
use App\Services\ArticleReader;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ReaderController extends Controller
{
    public function blog(Blog $blog): Response
    {
        return Inertia::render('BlogPosts', [
            'blog' => $blog->only(['id', 'title', 'url']),
            'posts' => $blog->posts()->orderByDesc('published_at')->orderByDesc('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function post(BlogPost $post, ArticleFetcher $fetcher, ArticleReader $reader): Response
    {
        $post->load('blog:id,title,url');
        $blocks = [];
        $unavailable = false;
        try {
            $blocks = Cache::remember('reader:'.hash('sha256', $post->url), now()->addHours(6), fn () => $reader->extract($fetcher->fetch($post->url)));
        } catch (Throwable) {
            $unavailable = true;
        }

        return Inertia::render('Reader', [
            'post' => $post->only(['id', 'title', 'url', 'published_at']),
            'blog' => $post->blog->only(['id', 'title', 'url']),
            'blocks' => $blocks,
            'unavailable' => $unavailable,
        ]);
    }
}
