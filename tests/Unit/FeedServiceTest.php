<?php

use App\Services\FeedService;

test('parses standard RSS 2.0 feed xml correctly', function () {
    $feedService = new FeedService;

    $xml = <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <rss version="2.0">
      <channel>
        <title>Laravel News</title>
        <link>https://laravel-news.com</link>
        <item>
          <title>Laravel 13 Released</title>
          <link>https://laravel-news.com/laravel-13</link>
          <guid>ln-13001</guid>
          <pubDate>Mon, 26 Oct 2026 10:00:00 +0000</pubDate>
        </item>
      </channel>
    </rss>
    XML;

    $result = $feedService->parseXml($xml);

    expect($result['title'])->toBe('Laravel News')
        ->and($result['website_url'])->toBe('https://laravel-news.com')
        ->and($result['items'])->toHaveCount(1)
        ->and($result['items'][0]['title'])->toBe('Laravel 13 Released')
        ->and($result['items'][0]['guid'])->toBe('ln-13001')
        ->and($result['items'][0]['url'])->toBe('https://laravel-news.com/laravel-13');
});

test('parses standard Atom feed xml correctly', function () {
    $feedService = new FeedService;

    $xml = <<<'XML'
    <?xml version="1.0" encoding="utf-8"?>
    <feed xmlns="http://www.w3.org/2005/Atom">
      <title>Overreacted</title>
      <link rel="alternate" href="https://overreacted.io"/>
      <entry>
        <title>A Chain Reaction</title>
        <link rel="alternate" href="https://overreacted.io/a-chain-reaction/"/>
        <id>https://overreacted.io/a-chain-reaction/</id>
        <updated>2026-10-20T00:00:00Z</updated>
      </entry>
    </feed>
    XML;

    $result = $feedService->parseXml($xml);

    expect($result['title'])->toBe('Overreacted')
        ->and($result['website_url'])->toBe('https://overreacted.io')
        ->and($result['items'])->toHaveCount(1)
        ->and($result['items'][0]['title'])->toBe('A Chain Reaction')
        ->and($result['items'][0]['guid'])->toBe('https://overreacted.io/a-chain-reaction/');
});

test('throws exception on malformed xml', function () {
    $feedService = new FeedService;

    $feedService->parseXml('<malformed><unclosed>');
})->throws(Exception::class);
