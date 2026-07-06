<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class Transformer
{
    public const SKIP_TAG_RE = '/^<(a|script|style|title|h[1-6])[\s\/>]/i';
    public const CLOSE_TAG_RE = '/^<\/(a|script|style|title|h[1-6])>/i';
    private const SINGLE_ROOT_RE = '/^<(\w+)(\s[^>]*)?>/';

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

    /**
     * @param array<string, mixed> $data
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function replaceMetaTags(string $html, array $data, array &$manifest): string
    {
        $title = (string) ($data['title'] ?? '');
        $metaDescription = (string) ($data['meta_description'] ?? '');
        $metaKeywords = (string) ($data['meta_keywords'] ?? '');
        $ogTitle = (string) ($data['og_title'] ?? '');
        $ogDescription = (string) ($data['og_description'] ?? '');
        $ogUrl = (string) ($data['og_url'] ?? '');
        $ogImage = (string) ($data['og_image'] ?? '');
        $structuredData = (string) ($data['structured_data'] ?? '');

        if ($title !== '' && !preg_match('/<title[\s>]/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<title data-seojuice="title">' . self::escapeHtml($title) . "</title>\n</head>",
                $html,
                1,
            );
            $manifest['meta'][] = 'title';
        }

        if ($metaDescription !== '' && !preg_match('/<meta\s+name=["\']description["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta name="description" content="' . self::escapeHtml($metaDescription) . "\" data-seojuice=\"meta-description\">\n</head>",
                $html,
                1,
            );
            $manifest['meta'][] = 'meta-description';
        }

        if ($metaKeywords !== '' && !preg_match('/<meta\s+name=["\']keywords["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta name="keywords" content="' . self::escapeHtml($metaKeywords) . "\" data-seojuice=\"meta-keywords\">\n</head>",
                $html,
                1,
            );
            $manifest['meta'][] = 'meta-keywords';
        }

        if ($ogTitle !== '' && !preg_match('/<meta\s+property=["\']og:title["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta property="og:title" content="' . self::escapeHtml($ogTitle) . "\" data-seojuice=\"og-title\">\n</head>",
                $html,
                1,
            );
            $manifest['meta'][] = 'og-title';
        }

        if ($ogDescription !== '' && !preg_match('/<meta\s+property=["\']og:description["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta property="og:description" content="' . self::escapeHtml($ogDescription) . "\" data-seojuice=\"og-description\">\n</head>",
                $html,
                1,
            );
            $manifest['meta'][] = 'og-description';
        }

        if ($ogUrl !== '' && !preg_match('/<meta\s+property=["\']og:url["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta property="og:url" content="' . self::escapeHtml($ogUrl) . "\">\n</head>",
                $html,
                1,
            );
        }

        if ($ogImage !== '' && !preg_match('/<meta\s+property=["\']og:image["\']/i', $html)) {
            $html = (string) preg_replace(
                '/<\/head>/i',
                '<meta property="og:image" content="' . self::escapeHtml($ogImage) . "\">\n</head>",
                $html,
                1,
            );
        }

        if ($structuredData !== '' && $structuredData !== 'null') {
            $inner = json_decode($structuredData, true);
            $obj = is_string($inner) ? json_decode($inner, true) : null;

            if (is_array($obj) && !preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>/i', $html)) {
                $json = json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $html = (string) preg_replace(
                    '/<\/head>/i',
                    '<script type="application/ld+json" data-seojuice="schema">' . $json . "</script>\n</head>",
                    $html,
                    1,
                );
                $manifest['schema'] = 1;
            }
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $data
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function replaceImages(string $html, array $data, array &$manifest): string
    {
        $images = $data['images'] ?? null;

        if (!is_array($images)) {
            return $html;
        }

        $imageMap = [];
        foreach ($images as $image) {
            $url = (string) ($image['url'] ?? '');
            $altText = (string) ($image['alt_text'] ?? '');

            if ($url !== '' && $altText !== '') {
                $imageMap[self::normalizeImageUrl($url)] = $altText;
            }
        }

        if ($imageMap === []) {
            return $html;
        }

        return (string) preg_replace_callback(
            '/<img([^>]+)>/i',
            static function (array $matches) use ($imageMap, &$manifest): string {
                $match = $matches[0];
                $attributes = $matches[1];

                if (!preg_match('/(?:src|data-src)=["\']([^"\']+)["\']/', $attributes, $srcMatch)) {
                    return $match;
                }

                $normalizedSrc = self::normalizeImageUrl($srcMatch[1]);

                if (!isset($imageMap[$normalizedSrc])) {
                    return $match;
                }

                $altMatch = [];
                $hasAlt = (bool) preg_match('/alt=["\']([^"\']*)["\']/', $match, $altMatch);
                $existingAlt = $hasAlt ? $altMatch[1] : '';

                if ($existingAlt !== '' && mb_strlen($existingAlt) >= 5) {
                    return $match;
                }

                $altText = self::escapeHtml($imageMap[$normalizedSrc]);
                $manifest['img']++;

                if ($hasAlt) {
                    $replaced = (string) preg_replace('/alt=["\'][^"\']*["\']/', 'alt="' . $altText . '"', $match, 1);
                    if (!str_contains($replaced, 'data-seojuice=')) {
                        $replaced = (string) preg_replace('/<img/', '<img data-seojuice="alt"', $replaced, 1);
                    }

                    return $replaced;
                }

                return (string) preg_replace('/<img/', '<img alt="' . $altText . '" data-seojuice="alt"', $match, 1);
            },
            $html,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function injectInternalLinks(string $html, array $data, array &$manifest): string
    {
        $suggestions = $data['suggestions'] ?? null;

        if (!is_array($suggestions)) {
            return $html;
        }

        $isAsian = (bool) ($data['isAsian'] ?? false);
        $customLinkClass = (string) ($data['custom_link_class'] ?? '');

        $links = [];
        $seenKeywords = [];
        foreach ($suggestions as $suggestion) {
            $keyword = (string) ($suggestion['keyword'] ?? '');
            $url = (string) ($suggestion['url'] ?? '');

            if ($keyword === '' || $url === '') {
                continue;
            }

            $kl = mb_strtolower($keyword, 'UTF-8');

            if (isset($seenKeywords[$kl])) {
                continue;
            }
            $seenKeywords[$kl] = true;

            $escapedKeyword = preg_quote($keyword, '/');
            $pattern = $isAsian
                ? '/(?<=[\p{Han}\p{Hiragana}\p{Katakana}]|^)(' . $escapedKeyword . ')(?=[\p{Han}\p{Hiragana}\p{Katakana}.!?)\]\/]|$)/u'
                : '/(?<=^|\s|[([{<>"\'«‹„"\'|\/]|\-|:)(' . $escapedKeyword . ')(?=$|\s|[)\]}>"\'»›"\'|\/]|\-|[.,:;!?])/i';

            $links[] = [
                'keyword' => $keyword,
                'kl' => $kl,
                'url' => $url,
                'id' => $suggestion['id'] ?? null,
                'pattern' => $pattern,
            ];
        }

        if ($links === []) {
            return $html;
        }

        $replacedKeywords = [];
        $segments = self::tokenizeHtml($html);
        $skipDepth = 0;
        $result = [];

        foreach ($segments as $segment) {
            if ($segment['type'] === 'tag') {
                if (preg_match(self::SKIP_TAG_RE, $segment['value'])) {
                    $skipDepth++;
                } elseif ($skipDepth > 0 && preg_match(self::CLOSE_TAG_RE, $segment['value'])) {
                    $skipDepth--;
                }
                $result[] = $segment['value'];
                continue;
            }

            $text = $segment['value'];

            if ($skipDepth === 0) {
                foreach ($links as $link) {
                    if (isset($replacedKeywords[$link['kl']])) {
                        continue;
                    }

                    $text = (string) preg_replace_callback(
                        $link['pattern'],
                        static function (array $matches) use ($link, $customLinkClass, &$replacedKeywords, &$manifest): string {
                            if (isset($replacedKeywords[$link['kl']])) {
                                return $matches[0];
                            }

                            $classAttr = $customLinkClass !== '' ? ' class="seojuice-link ' . $customLinkClass . '"' : '';
                            $csAttr = $link['id'] !== null ? ' data-seojuice-cs="' . $link['id'] . '"' : '';
                            $replacement = '<a href="' . self::escapeHtml($link['url']) . '"' . $classAttr . $csAttr . '>' . self::escapeHtml($link['keyword']) . '</a>';

                            $replacedKeywords[$link['kl']] = true;
                            if ($link['id'] !== null) {
                                $manifest['cs'][] = $link['id'];
                            }

                            return $replacement;
                        },
                        $text,
                        1,
                    );
                }
            }

            $result[] = $text;
        }

        return implode('', $result);
    }

    /**
     * @param array<int, array<string, mixed>> $fixes
     */
    public static function applyBrokenLinkFixes(string $html, array $fixes): string
    {
        foreach ($fixes as $fix) {
            try {
                $tag = strtolower((string) ($fix['tag'] ?? ''));
                $attr = strtolower((string) ($fix['attr'] ?? ''));
                $brokenUrl = (string) ($fix['broken_url'] ?? ($fix['old_url'] ?? ''));
                $newUrl = ((string) ($fix['new_url'] ?? '')) !== ''
                    ? (string) $fix['new_url']
                    : (string) ($fix['replacement_url'] ?? '');
                $action = ($fix['action'] ?? '') === 'unlink' ? 'unlink' : 'replace';

                if ($tag === '' || $attr === '' || $brokenUrl === '') {
                    continue;
                }
                if ($action === 'replace' && $newUrl === '') {
                    continue;
                }
                if ($tag !== 'a' && $tag !== 'img') {
                    continue;
                }
                if ($attr !== 'href' && $attr !== 'src') {
                    continue;
                }

                $escapedOldUrl = preg_quote($brokenUrl, '/');

                if ($action === 'replace') {
                    $re = '/(<' . $tag . '\b[^>]*\s' . $attr . '=)(["\'])(' . $escapedOldUrl . ')\2([^>]*>)/i';
                    $newUrlEscaped = self::escapeHtml($newUrl);
                    $html = (string) preg_replace_callback(
                        $re,
                        static function (array $matches) use ($newUrlEscaped): string {
                            return $matches[1] . $matches[2] . $newUrlEscaped . $matches[2] . $matches[4];
                        },
                        $html,
                    );
                } elseif ($tag === 'img') {
                    $re = '/<img\b[^>]*\s' . $attr . '=["\']' . $escapedOldUrl . '["\'][^>]*>/i';
                    $html = (string) preg_replace($re, '', $html);
                } else {
                    $re = '/<a\b[^>]*\s' . $attr . '=["\']' . $escapedOldUrl . '["\'][^>]*>[\s\S]*?<\/a>/i';
                    $html = (string) preg_replace($re, '', $html);
                }
            } catch (\Throwable) {
                // one bad fix never aborts the page
            }
        }

        return $html;
    }

    /**
     * @param array<int, array<string, mixed>> $diffs
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function applyContentDiffs(string $html, array $diffs, array &$manifest): string
    {
        foreach ($diffs as $diff) {
            try {
                $original = (string) ($diff['original_text'] ?? '');
                $replacement = (string) ($diff['replacement_html'] ?? '');

                if ($original === '' || $replacement === '') {
                    continue;
                }

                if (str_contains($html, $replacement) && !str_contains($html, $original)) {
                    continue; // already applied
                }

                $idx = strpos($html, $original);
                if ($idx === false) {
                    continue; // DOM drift → skip
                }

                if (strpos($html, $original, $idx + 1) !== false) {
                    continue; // ambiguous → skip
                }

                $id = $diff['id'] ?? null;
                if ($id !== null) {
                    if (preg_match(self::SINGLE_ROOT_RE, $replacement, $rootMatch)) {
                        $markerStr = 'data-seojuice-cs="' . $id . '"';
                        if (!str_contains($replacement, $markerStr)) {
                            $openTag = $rootMatch[0];
                            $markedOpenTag = substr($openTag, 0, -1) . ' ' . $markerStr . '>';
                            $replacement = $markedOpenTag . substr($replacement, strlen($openTag));
                        }

                        if (!str_contains($html, 'data-seojuice-cs="' . $id . '"')) {
                            $manifest['cs'][] = $id;
                        }
                    }
                }

                $html = substr($html, 0, $idx) . $replacement . substr($html, $idx + strlen($original));
            } catch (\Throwable) {
                // one bad diff never aborts the page
            }
        }

        return $html;
    }

    /**
     * @param array<string, mixed> $data
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function replaceH1(string $html, array $data, array &$manifest): string
    {
        $h1 = (string) ($data['h1'] ?? '');

        if ($h1 === '') {
            return $html;
        }

        return (string) preg_replace_callback(
            '/(<h1[^>]*>)([\s\S]*?)(<\/h1>)/i',
            static function (array $matches) use ($h1, &$manifest): string {
                $openTag = $matches[1];

                if (!str_contains($openTag, 'data-seojuice=')) {
                    $openTag = (string) preg_replace('/>$/', ' data-seojuice="h1">', $openTag);
                }

                $manifest['h1'] = 1;

                return $openTag . self::escapeHtml($h1) . $matches[3];
            },
            $html,
            1,
        );
    }
}
