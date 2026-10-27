<?php

use App\Models\Blog;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->environment('testing') || ! str_starts_with((string) config('database.connections.sqlite.database'), sys_get_temp_dir().'/larablogs-dusk-')) {
    throw new RuntimeException('Browser fixtures require the isolated Dusk database.');
}
User::factory()->create(['email' => 'browser@example.com', 'password' => 'browser-fixture-password', 'is_owner' => true]);
User::factory()->create(['email' => 'temporary@example.com', 'password' => 'temporary-fixture-password', 'is_owner' => true, 'must_change_password' => true]);
$blog = Blog::create(['title' => 'Reader fixture', 'url' => 'https://example.com', 'feed_url' => 'https://example.com/feed', 'notify_email' => 'reader@example.com', 'is_active' => true]);
$post = BlogPost::create(['blog_id' => $blog->id, 'title' => 'An article for reading', 'url' => 'https://example.com/reader-fixture', 'guid' => 'fixture', 'published_at' => now()]);
Cache::put('reader:'.hash('sha256', $post->url), [['type' => 'paragraph', 'text' => 'This is the isolated reader content.']], 3600);
$other = Blog::create(['title' => 'Other blog', 'url' => 'https://example.org', 'feed_url' => 'https://example.org/feed', 'notify_email' => 'reader@example.com', 'is_active' => true]);
BlogPost::create(['blog_id' => $other->id, 'title' => 'Other blog article', 'url' => 'https://example.org/article', 'guid' => 'other', 'published_at' => now()]);
