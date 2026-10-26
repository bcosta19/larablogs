<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\BlogPost;
use App\Notifications\NewBlogPostNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class FeedService
{
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Larablogs/1.0';

    /**
     * Tenta descobrir a URL do feed e o título do blog a partir de uma URL informada.
     *
     * @return array{title: string, feed_url: string, website_url: string}
     */
    public function discoverFeed(string $url): array
    {
        $url = trim($url);
        if (! preg_match('~^https?://~i', $url)) {
            $url = 'https://'.$url;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,application/rss+xml,application/atom+xml,*/*;q=0.8',
            ])->timeout(12)->get($url);
        } catch (Exception $e) {
            throw new Exception("Não foi possível acessar a URL ({$url}): ".$e->getMessage());
        }

        if (! $response->successful()) {
            throw new Exception('URL retornou status HTTP '.$response->status());
        }

        $body = $response->body();
        $contentType = strtolower((string) $response->header('Content-Type'));

        $fallbackHost = (string) (parse_url($url, PHP_URL_HOST) ?: 'Blog');

        // Caso a própria URL informada já seja um Feed XML (RSS / Atom)
        if ($this->isXmlContent($contentType, $body)) {
            $parsed = $this->parseXml($body);

            return [
                'title' => $parsed['title'] !== '' ? $parsed['title'] : $fallbackHost,
                'feed_url' => $url,
                'website_url' => $parsed['website_url'] !== '' ? $parsed['website_url'] : $url,
            ];
        }

        // Caso seja página HTML, procurar tags <link rel="alternate">
        $extracted = $this->findFeedInHtml($body, $url);
        if ($extracted !== null) {
            return $extracted;
        }

        // Tentar rotas comuns de RSS
        $candidates = ['/feed', '/rss', '/rss.xml', '/feed.xml', '/atom.xml', '/index.xml'];
        $base = rtrim($url, '/');

        foreach ($candidates as $path) {
            $candidateUrl = $base.$path;
            try {
                $candidateRes = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout(6)->get($candidateUrl);

                if ($candidateRes->successful() && $this->isXmlContent($candidateRes->header('Content-Type'), $candidateRes->body())) {
                    $parsed = $this->parseXml($candidateRes->body());
                    $siteTitle = $this->extractHtmlTitle($body);

                    return [
                        'title' => $parsed['title'] !== '' ? $parsed['title'] : ($siteTitle ?? $fallbackHost),
                        'feed_url' => $candidateUrl,
                        'website_url' => $url,
                    ];
                }
            } catch (Exception) {
                continue;
            }
        }

        throw new Exception("Não foi possível encontrar nenhum feed RSS ou Atom válido em '{$url}'. Verifique a URL ou informe o link direto do feed.");
    }

    /**
     * Verifica se o conteúdo parece ser XML ou Feed.
     */
    protected function isXmlContent(?string $contentType, string $body): bool
    {
        if ($contentType && (
            str_contains($contentType, 'xml') ||
            str_contains($contentType, 'rss') ||
            str_contains($contentType, 'atom')
        )) {
            return true;
        }

        $trimmed = ltrim($body);

        return str_starts_with($trimmed, '<?xml') ||
               str_contains(substr($trimmed, 0, 500), '<rss') ||
               str_contains(substr($trimmed, 0, 500), '<feed');
    }

    /**
     * Procura tags <link rel="alternate" ...> no HTML.
     *
     * @return array{title: string, feed_url: string, website_url: string}|null
     */
    protected function findFeedInHtml(string $html, string $originalUrl): ?array
    {
        $siteTitle = $this->extractHtmlTitle($html);
        $fallbackHost = (string) (parse_url($originalUrl, PHP_URL_HOST) ?: 'Blog');

        // Regex para capturar <link> com feed
        $pattern = '/<link\s+[^>]*rel=["\']alternate["\'][^>]*>/i';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches[0] as $linkTag) {
                $isRssOrAtom = preg_match('/type=["\'](application\/(rss\+xml|atom\+xml|xml)|text\/xml)["\']/i', $linkTag);
                if ($isRssOrAtom && preg_match('/href=["\']([^"\']+)["\']/i', $linkTag, $hrefMatch)) {
                    $feedUrl = $this->resolveUrl($hrefMatch[1], $originalUrl);

                    // Testar se feed responde
                    try {
                        $feedRes = Http::withHeaders(['User-Agent' => $this->userAgent])->timeout(8)->get($feedUrl);
                        if ($feedRes->successful()) {
                            $parsed = $this->parseXml($feedRes->body());

                            return [
                                'title' => $siteTitle ?: ($parsed['title'] !== '' ? $parsed['title'] : $fallbackHost),
                                'feed_url' => $feedUrl,
                                'website_url' => $originalUrl,
                            ];
                        }
                    } catch (Exception) {
                        continue;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extrai o título da página HTML.
     */
    protected function extractHtmlTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            return preg_replace('/\s+/', ' ', $title);
        }

        return null;
    }

    /**
     * Resolve URLs relativas para absolutas.
     */
    protected function resolveUrl(string $relativeOrAbsolute, string $baseUrl): string
    {
        if (preg_match('~^https?://~i', $relativeOrAbsolute)) {
            return $relativeOrAbsolute;
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        if (str_starts_with($relativeOrAbsolute, '//')) {
            return $scheme.':'.$relativeOrAbsolute;
        }

        if (str_starts_with($relativeOrAbsolute, '/')) {
            return "{$scheme}://{$host}{$port}{$relativeOrAbsolute}";
        }

        $basePath = $parts['path'] ?? '/';
        $baseDir = rtrim(dirname($basePath), '/');

        return "{$scheme}://{$host}{$port}".($baseDir ? "{$baseDir}/" : '/').$relativeOrAbsolute;
    }

    /**
     * Faz parse do XML (RSS 2.0 / Atom).
     *
     * @return array{title: string, website_url: string, items: list<array{guid: string, title: string, url: string, published_at: ?Carbon}>}
     */
    public function parseXml(string $xmlContent): array
    {
        $xmlContent = trim($xmlContent);
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (! $xml) {
            libxml_clear_errors();
            throw new Exception('Conteúdo XML inválido ou malformado.');
        }

        $feedTitle = '';
        $websiteUrl = '';
        $items = [];

        // Verifica se é RSS
        if (isset($xml->channel)) {
            $feedTitle = (string) $xml->channel->title;
            $websiteUrl = (string) $xml->channel->link;

            foreach ($xml->channel->item as $item) {
                $title = trim((string) $item->title);
                $link = trim((string) $item->link);
                $guid = trim((string) ($item->guid ?: ($link ?: md5($title.$link))));

                $pubDateRaw = (string) ($item->pubDate ?: ($item->children('http://purl.org/dc/elements/1.1/')->date ?: ''));
                $pubDate = null;
                if ($pubDateRaw) {
                    try {
                        $pubDate = Carbon::parse($pubDateRaw);
                    } catch (Exception) {
                        $pubDate = null;
                    }
                }

                if ($title !== '') {
                    $items[] = [
                        'guid' => substr($guid, 0, 250),
                        'title' => $title,
                        'url' => $link ?: $guid,
                        'published_at' => $pubDate,
                    ];
                }
            }
        } // Verifica se é Atom
        elseif ($xml->getName() === 'feed') {
            $feedTitle = (string) $xml->title;

            // Encontra o link alternate
            foreach ($xml->link as $link) {
                $rel = (string) $link['rel'];
                if (! $rel || $rel === 'alternate') {
                    $websiteUrl = (string) $link['href'];
                    break;
                }
            }

            foreach ($xml->entry as $entry) {
                $title = trim((string) $entry->title);
                $entryLink = '';
                foreach ($entry->link as $link) {
                    $rel = (string) $link['rel'];
                    if (! $rel || $rel === 'alternate') {
                        $entryLink = (string) $link['href'];
                        break;
                    }
                }
                if (! $entryLink && isset($entry->link['href'])) {
                    $entryLink = (string) $entry->link['href'];
                }

                $id = trim((string) $entry->id);
                $guid = $id ?: ($entryLink ?: md5($title.$entryLink));

                $dateRaw = (string) ($entry->published ?: ($entry->updated ?: ''));
                $pubDate = null;
                if ($dateRaw) {
                    try {
                        $pubDate = Carbon::parse($dateRaw);
                    } catch (Exception) {
                        $pubDate = null;
                    }
                }

                if ($title !== '') {
                    $items[] = [
                        'guid' => substr($guid, 0, 250),
                        'title' => $title,
                        'url' => $entryLink ?: $guid,
                        'published_at' => $pubDate,
                    ];
                }
            }
        }

        libxml_clear_errors();

        return [
            'title' => trim($feedTitle),
            'website_url' => trim($websiteUrl),
            'items' => $items,
        ];
    }

    /**
     * Faz a verificação de um Blog, sincroniza posts e dispara notificações para novos itens.
     *
     * @return array{new_posts_count: int, total_posts_in_feed: int, new_posts: list<BlogPost>}
     */
    public function checkBlog(Blog $blog, bool $notify = true): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
            ])->timeout(15)->get($blog->feed_url);

            if (! $response->successful()) {
                throw new Exception('HTTP status '.$response->status().' ao acessar feed.');
            }

            $parsed = $this->parseXml($response->body());
            $newPosts = [];

            // Se o título do blog estava genérico ou vazio, atualiza
            if (empty($blog->title) && ! empty($parsed['title'])) {
                $blog->update(['title' => $parsed['title']]);
            }

            foreach ($parsed['items'] as $itemData) {
                $existing = BlogPost::where('blog_id', $blog->id)
                    ->where('guid', $itemData['guid'])
                    ->first();

                if (! $existing) {
                    $newPost = BlogPost::create([
                        'blog_id' => $blog->id,
                        'guid' => $itemData['guid'],
                        'title' => $itemData['title'],
                        'url' => $itemData['url'],
                        'published_at' => $itemData['published_at'] ?? now(),
                        'notified_at' => null,
                    ]);

                    if ($notify && $blog->is_active && ! empty($blog->notify_email)) {
                        try {
                            Notification::route('mail', $blog->notify_email)
                                ->notify(new NewBlogPostNotification($blog, $newPost));

                            $newPost->update(['notified_at' => now()]);
                            Log::info("Notificação de novo post enviada com sucesso para {$blog->notify_email}", [
                                'blog' => $blog->title,
                                'post' => $newPost->title,
                            ]);
                        } catch (Exception $e) {
                            Log::error('Erro ao enviar notificação de novo post: '.$e->getMessage(), [
                                'blog_id' => $blog->id,
                                'post_id' => $newPost->id,
                            ]);
                        }
                    }

                    $newPosts[] = $newPost;
                }
            }

            $blog->update([
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            return [
                'new_posts_count' => count($newPosts),
                'total_posts_in_feed' => count($parsed['items']),
                'new_posts' => $newPosts,
            ];
        } catch (Exception $e) {
            $blog->update([
                'last_checked_at' => now(),
                'last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
