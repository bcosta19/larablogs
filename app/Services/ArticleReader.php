<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

class ArticleReader
{
    /** @return list<array{type: string, text: string}> */
    public function extract(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        if (! $loaded) {
            throw new RuntimeException('Cannot parse article.');
        }
        $xpath = new DOMXPath($document);
        foreach ($xpath->query('//script|//style|//nav|//footer|//aside|//form|//iframe|//noscript|//button|//*[@hidden]|//*[@aria-hidden="true"]') ?: [] as $node) {
            if ($node instanceof DOMNode) {
                $node->parentNode?->removeChild($node);
            }
        }

        $candidates = $xpath->query('//article|//main|//*[@role="main"]|//*[contains(@class,"entry-content") or contains(@class,"post-content") or contains(@class,"article-body")]');
        $root = null;
        $score = 0;
        foreach ($candidates ?: [] as $candidate) {
            if (! $candidate instanceof DOMElement) {
                continue;
            }
            $length = mb_strlen($candidate->textContent);
            if ($length > $score) {
                $root = $candidate;
                $score = $length;
            }
        }
        $root ??= $document->getElementsByTagName('body')->item(0);
        if (! $root) {
            throw new RuntimeException('No article content.');
        }

        $blocks = [];
        $this->walk($root, $blocks);
        if ($blocks === []) {
            throw new RuntimeException('No readable article content.');
        }

        return array_slice($blocks, 0, 600);
    }

    /** @param list<array{type: string, text: string}> $blocks */
    private function walk(DOMNode $node, array &$blocks): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            $types = ['h1' => 'heading', 'h2' => 'heading', 'h3' => 'heading', 'h4' => 'heading', 'p' => 'paragraph', 'li' => 'list', 'blockquote' => 'quote', 'pre' => 'code'];
            if (isset($types[$tag])) {
                $text = $tag === 'pre' ? trim($node->textContent) : trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
                if ($text !== '') {
                    $blocks[] = ['type' => $types[$tag], 'text' => $text];
                }

                return;
            }
        }
        foreach ($node->childNodes as $child) {
            $this->walk($child, $blocks);
        }
    }
}
