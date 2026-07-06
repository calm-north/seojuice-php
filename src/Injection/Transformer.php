<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class Transformer
{
    public const SKIP_TAG_RE = '/^<(a|script|style|title|h[1-6])[\s\/>]/i';
    public const CLOSE_TAG_RE = '/^<\/(a|script|style|title|h[1-6])>/i';

    public static function escapeHtml(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function normalizeImageUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $url = explode('?', $url)[0];

        if (str_starts_with($url, 'https:')) {
            return substr($url, 6);
        }

        if (str_starts_with($url, 'http:')) {
            return substr($url, 5);
        }

        return $url;
    }

    /**
     * @return array<int, array{type: string, value: string}>
     */
    public static function tokenizeHtml(string $html): array
    {
        $parts = preg_split('/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $segments = [];

        foreach ($parts as $part) {
            $isTag = $part !== '' && $part[0] === '<' && str_ends_with($part, '>');
            $segments[] = ['type' => $isTag ? 'tag' : 'text', 'value' => $part];
        }

        return $segments;
    }
}
