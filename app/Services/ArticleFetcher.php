<?php

namespace App\Services;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ArticleFetcher
{
    private const MAX_BYTES = 2 * 1024 * 1024;

    public function __construct(private PublicArticleUrl $urls) {}

    public function fetch(string $url): string
    {
        for ($redirect = 0; $redirect < 5; $redirect++) {
            $target = $this->urls->resolve($url);
            $addresses = array_map(fn (string $ip): string => str_contains($ip, ':') ? "[{$ip}]" : $ip, $target['addresses']);
            $response = Http::accept('text/html,application/xhtml+xml')
                ->withUserAgent('Larablogs/1.0 (personal article reader)')
                ->connectTimeout(5)->timeout(15)
                ->withOptions([
                    'allow_redirects' => false,
                    'proxy' => '',
                    'curl' => [CURLOPT_RESOLVE => ["{$target['host']}:{$target['port']}:".implode(',', $addresses)]],
                    'progress' => function (float $total, float $downloaded): void {
                        if ($total > self::MAX_BYTES || $downloaded > self::MAX_BYTES) {
                            throw new RuntimeException('Article exceeds size limit.');
                        }
                    },
                ])->get($url);

            if ($response->redirect()) {
                $location = $response->header('Location');
                if (! $location) {
                    throw new RuntimeException('Missing redirect target.');
                }
                $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                continue;
            }

            $type = strtolower($response->header('Content-Type'));
            if (! $response->successful() || ! (str_contains($type, 'text/html') || str_contains($type, 'application/xhtml+xml'))) {
                throw new RuntimeException('Article content is unavailable.');
            }
            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                throw new RuntimeException('Article exceeds size limit.');
            }

            return $body;
        }

        throw new RuntimeException('Too many article redirects.');
    }
}
