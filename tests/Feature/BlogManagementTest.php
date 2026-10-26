<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogPost;
use App\Notifications\NewBlogPostNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlogManagementTest extends TestCase
{
    use RefreshDatabase;

    protected string $sampleRss = <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <rss version="2.0">
      <channel>
        <title>Tech Blog Exemplo</title>
        <link>https://techblog.exemplo.com</link>
        <description>Artigos sobre tecnologia</description>
        <item>
          <title>Primeiro Artigo Incrível</title>
          <link>https://techblog.exemplo.com/posts/primeiro</link>
          <guid>guid-12345</guid>
          <pubDate>Sun, 25 Oct 2026 12:00:00 +0000</pubDate>
        </item>
      </channel>
    </rss>
    XML;

    protected string $sampleHtml = <<<'HTML'
    <!DOCTYPE html>
    <html>
      <head>
        <title>Meu Blog de Tecnologia</title>
        <link rel="alternate" type="application/rss+xml" title="Feed RSS" href="https://techblog.exemplo.com/feed.xml" />
      </head>
      <body><h1>Bem-vindo ao blog</h1></body>
    </html>
    HTML;

    public function test_can_view_home_page_with_inertia()
    {
        $blog = Blog::create([
            'title' => 'Blog de Teste',
            'url' => 'https://teste.com',
            'feed_url' => 'https://teste.com/feed',
            'notify_email' => 'dev@teste.com',
            'is_active' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('blogs', 1)
            ->where('blogs.0.title', 'Blog de Teste')
            ->has('stats')
            ->where('stats.total_blogs', 1)
            ->where('stats.active_blogs', 1)
        );
    }

    public function test_can_store_blog_discovering_feed_from_html()
    {
        Http::fake([
            'https://techblog.exemplo.com' => Http::response($this->sampleHtml, 200, ['Content-Type' => 'text/html']),
            'https://techblog.exemplo.com/feed.xml' => Http::response($this->sampleRss, 200, ['Content-Type' => 'application/rss+xml']),
        ]);

        Notification::fake();

        $response = $this->post(route('blogs.store'), [
            'url' => 'https://techblog.exemplo.com',
            'notify_email' => 'leitor@exemplo.com',
            'title' => '',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('blogs', [
            'feed_url' => 'https://techblog.exemplo.com/feed.xml',
            'notify_email' => 'leitor@exemplo.com',
        ]);

        // Baseline não deve disparar notificação inicial
        Notification::assertNothingSent();

        // O post inicial deve ter sido salvo no banco como ponto de partida
        $this->assertDatabaseHas('blog_posts', [
            'guid' => 'guid-12345',
            'title' => 'Primeiro Artigo Incrível',
        ]);
    }

    public function test_can_check_blog_for_new_posts_and_sends_notification()
    {
        Notification::fake();

        $blog = Blog::create([
            'title' => 'Tech Blog Exemplo',
            'url' => 'https://techblog.exemplo.com',
            'feed_url' => 'https://techblog.exemplo.com/feed.xml',
            'notify_email' => 'leitor@exemplo.com',
            'is_active' => true,
        ]);

        // Já existe o post 1 arquivado
        BlogPost::create([
            'blog_id' => $blog->id,
            'guid' => 'guid-12345',
            'title' => 'Primeiro Artigo Incrível',
            'url' => 'https://techblog.exemplo.com/posts/primeiro',
            'published_at' => now()->subDay(),
            'notified_at' => now()->subDay(),
        ]);

        // Novo feed traz o post 2
        $updatedRss = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0">
          <channel>
            <title>Tech Blog Exemplo</title>
            <link>https://techblog.exemplo.com</link>
            <item>
              <title>Primeiro Artigo Incrível</title>
              <link>https://techblog.exemplo.com/posts/primeiro</link>
              <guid>guid-12345</guid>
            </item>
            <item>
              <title>Segundo Artigo Fresquinho</title>
              <link>https://techblog.exemplo.com/posts/segundo</link>
              <guid>guid-67890</guid>
              <pubDate>Mon, 26 Oct 2026 01:00:00 +0000</pubDate>
            </item>
          </channel>
        </rss>
        XML;

        Http::fake([
            'https://techblog.exemplo.com/feed.xml' => Http::response($updatedRss, 200, ['Content-Type' => 'application/rss+xml']),
        ]);

        $response = $this->post(route('blogs.check', $blog));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('blog_posts', [
            'blog_id' => $blog->id,
            'guid' => 'guid-67890',
            'title' => 'Segundo Artigo Fresquinho',
        ]);

        Notification::assertSentOnDemand(
            NewBlogPostNotification::class,
            function ($notification, $channels, $notifiable) use ($blog) {
                return $notifiable->routes['mail'] === $blog->notify_email &&
                       $notification->post->guid === 'guid-67890';
            }
        );
    }

    public function test_can_toggle_blog_active_status()
    {
        $blog = Blog::create([
            'title' => 'Blog Ativo',
            'url' => 'https://ativo.com',
            'feed_url' => 'https://ativo.com/feed',
            'notify_email' => 'dev@ativo.com',
            'is_active' => true,
        ]);

        $response = $this->patch(route('blogs.toggle', $blog));
        $response->assertRedirect();

        $this->assertFalse($blog->fresh()->is_active);

        $this->patch(route('blogs.toggle', $blog));
        $this->assertTrue($blog->fresh()->is_active);
    }

    public function test_can_destroy_blog_and_associated_posts()
    {
        $blog = Blog::create([
            'title' => 'Blog Temporário',
            'url' => 'https://temp.com',
            'feed_url' => 'https://temp.com/feed',
            'notify_email' => 'dev@temp.com',
            'is_active' => true,
        ]);

        $post = BlogPost::create([
            'blog_id' => $blog->id,
            'guid' => 'temp-1',
            'title' => 'Post Temp',
            'url' => 'https://temp.com/1',
        ]);

        $response = $this->delete(route('blogs.destroy', $blog));
        $response->assertRedirect();

        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_artisan_blogs_check_command_executes_successfully()
    {
        Notification::fake();

        $blog = Blog::create([
            'title' => 'Tech Blog Exemplo',
            'url' => 'https://techblog.exemplo.com',
            'feed_url' => 'https://techblog.exemplo.com/feed.xml',
            'notify_email' => 'cli@exemplo.com',
            'is_active' => true,
        ]);

        Http::fake([
            'https://techblog.exemplo.com/feed.xml' => Http::response($this->sampleRss, 200, ['Content-Type' => 'application/rss+xml']),
        ]);

        $this->artisan('blogs:check')
            ->expectsOutputToContain('Iniciando verificação de 1 blog(s)...')
            ->expectsOutputToContain('Verificação concluída!')
            ->assertSuccessful();

        $this->assertDatabaseHas('blog_posts', [
            'blog_id' => $blog->id,
            'guid' => 'guid-12345',
        ]);
    }
}
