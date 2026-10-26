<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Services\FeedService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with generic tech blogs.
     */
    public function run(FeedService $feedService): void
    {
        $blogs = [
            [
                'title' => 'Laravel News',
                'url' => 'https://laravel-news.com',
                'feed_url' => 'https://feed.laravel-news.com/',
                'notify_email' => 'reader@example.com',
                'is_active' => true,
            ],
            [
                'title' => 'The GitHub Blog',
                'url' => 'https://github.blog',
                'feed_url' => 'https://github.blog/feed/',
                'notify_email' => 'reader@example.com',
                'is_active' => true,
            ],
            [
                'title' => 'Mozilla Hacks',
                'url' => 'https://hacks.mozilla.org',
                'feed_url' => 'https://hacks.mozilla.org/feed/',
                'notify_email' => 'reader@example.com',
                'is_active' => true,
            ],
        ];

        foreach ($blogs as $data) {
            $blog = Blog::firstOrCreate(
                ['feed_url' => $data['feed_url']],
                $data
            );

            try {
                $feedService->checkBlog($blog, notify: false);
            } catch (\Exception) {
                // Ignore network issues during offline seeding
            }
        }
    }
}
