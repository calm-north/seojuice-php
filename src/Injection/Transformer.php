<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class Transformer
{
    public const SKIP_TAG_RE = '/^<(a|script|style|title|h[1-6])[\s\/>]/i';
    public const CLOSE_TAG_RE = '/^<\/(a|script|style|title|h[1-6])>/i';
    private const SINGLE_ROOT_RE = '/^<(\w+)(\s[^>]*)?>/';
    private const SCRIPT_STYLE_RE = '/(<(script|style)\b[^>]*>)(.*?)(<\/\2>)/is';
    private const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
    private const CONTENT_AREA_TAGS = ['p', 'li', 'span', 'div', 'td', 'blockquote', 'dd', 'figcaption'];

    public static function escapeHtml(string $text): string
    {
        if ($text === '') {
            return '';
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    // Next.js optimizer: the image is the `url` param, not the endpoint.
    private static function unwrapNextImageUrl(string $url): string
    {
        $parts = parse_url(str_starts_with($url, '//') ? 'http:' . $url : $url);
        if ($parts === false || ($parts['path'] ?? '') !== '/_next/image') {
            return $url;
        }

        parse_str($parts['query'] ?? '', $query);
        $original = $query['url'] ?? null;
        if (!is_string($original) || $original === '') {
            return $url;
        }

        if (!isset($parts['host']) || preg_match('#^[a-z][a-z0-9+.-]*:|^//#i', $original) === 1) {
            return $original;
        }

        $origin = (str_starts_with($url, '//') ? '' : ($parts['scheme'] . ':')) . '//' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');

        return str_starts_with($original, '/') ? $origin . $original : $url;
    }

    public static function normalizeImageUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $url = explode('?', self::unwrapNextImageUrl($url))[0];

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
     * Replace the first match of $pattern with $replacement, treating
     * $replacement as a LITERAL string. Unlike preg_replace, this never
     * interprets `$n`/`${n}`/`\n`/`\0` in the replacement as backreferences —
     * essential because $replacement is built from escapeHtml() output, and
     * htmlspecialchars does not neutralize `$` or `\` (e.g. API text "Save $5"
     * or "Deal \0 backref" would otherwise corrupt the output).
     */
    private static function replaceFirstLiteral(string $pattern, string $replacement, string $subject): string
    {
        return (string) preg_replace_callback(
            $pattern,
            static fn (): string => $replacement,
            $subject,
            1,
        );
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
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<title data-seojuice="title">' . self::escapeHtml($title) . "</title>\n</head>",
                $html,
            );
            $manifest['meta'][] = 'title';
        }

        if ($metaDescription !== '' && !preg_match('/<meta\s+name=["\']description["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta name="description" content="' . self::escapeHtml($metaDescription) . "\" data-seojuice=\"meta-description\">\n</head>",
                $html,
            );
            $manifest['meta'][] = 'meta-description';
        }

        if ($metaKeywords !== '' && !preg_match('/<meta\s+name=["\']keywords["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta name="keywords" content="' . self::escapeHtml($metaKeywords) . "\" data-seojuice=\"meta-keywords\">\n</head>",
                $html,
            );
            $manifest['meta'][] = 'meta-keywords';
        }

        if ($ogTitle !== '' && !preg_match('/<meta\s+property=["\']og:title["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta property="og:title" content="' . self::escapeHtml($ogTitle) . "\" data-seojuice=\"og-title\">\n</head>",
                $html,
            );
            $manifest['meta'][] = 'og-title';
        }

        if ($ogDescription !== '' && !preg_match('/<meta\s+property=["\']og:description["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta property="og:description" content="' . self::escapeHtml($ogDescription) . "\" data-seojuice=\"og-description\">\n</head>",
                $html,
            );
            $manifest['meta'][] = 'og-description';
        }

        if ($ogUrl !== '' && !preg_match('/<meta\s+property=["\']og:url["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta property="og:url" content="' . self::escapeHtml($ogUrl) . "\">\n</head>",
                $html,
            );
        }

        if ($ogImage !== '' && !preg_match('/<meta\s+property=["\']og:image["\']/i', $html)) {
            $html = self::replaceFirstLiteral(
                '/<\/head>/i',
                '<meta property="og:image" content="' . self::escapeHtml($ogImage) . "\">\n</head>",
                $html,
            );
        }

        if ($structuredData !== '' && $structuredData !== 'null') {
            // Mirrors seojuice-ssr.js replaceMetaTags (structured_data block).
            // Decode once; if the result is still a string (legacy double
            // encoding) decode again. Objects stay stdClass so an empty `{}`
            // round-trips as `{}`. An unparsable payload injects nothing.
            $obj = json_decode($structuredData);
            if (is_string($obj)) {
                $obj = json_decode($obj);
            }

            if (json_last_error() === JSON_ERROR_NONE) {
                $nodes = self::normalizeSchemaNodes($obj);
                // Tagged scripts are ours: replace, never append.
                $html = self::stripOwnLdJson($html);
                $present = self::collectPresentLdJsonTypes($html);
                $missing = array_values(array_filter(
                    $nodes,
                    fn ($node) => !self::ldJsonNodeAlreadyPresent($node, $present),
                ));

                if ($missing !== []) {
                    $json = str_replace(
                        '<',
                        '\\u003c',
                        (string) json_encode($missing, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    );
                    $html = self::replaceFirstLiteral(
                        '/<\/head>/i',
                        '<script type="application/ld+json" data-seojuice="schema">' . $json . "</script>\n</head>",
                        $html,
                    );
                    $manifest['schema'] = 1;
                }
            }
        }

        return $html;
    }

    /**
     * Gap-fill dedup type coverage (seojuice-ssr.js SCHEMA_COVERS): a proposed
     * type is redundant when any covering type is already on the page.
     *
     * @var array<string, array<int, string>>
     */
    private const SCHEMA_COVERS = [
        'Article' => ['Article', 'BlogPosting', 'NewsArticle', 'TechArticle', 'ScholarlyArticle', 'Report'],
        'WebPage' => [
            'WebPage', 'CollectionPage', 'AboutPage', 'ContactPage', 'ItemPage', 'ProfilePage', 'FAQPage', 'QAPage',
            'SearchResultsPage', 'CheckoutPage', 'MedicalWebPage', 'RealEstateListing',
        ],
        'Organization' => [
            'Organization', 'Corporation', 'NGO', 'EducationalOrganization', 'School', 'CollegeOrUniversity',
            'ElementarySchool', 'HighSchool', 'MiddleSchool', 'Preschool', 'GovernmentOrganization',
            'NewsMediaOrganization', 'MedicalOrganization', 'ResearchOrganization', 'SportsOrganization',
            'SportsTeam', 'PerformingGroup', 'OnlineBusiness', 'OnlineStore', 'Airline', 'Consortium',
            'LibrarySystem', 'WorkersUnion', 'LocalBusiness', 'AccountingService', 'AutomotiveBusiness',
            'AutoDealer', 'AutoRepair', 'Dentist', 'EmergencyService', 'EntertainmentBusiness', 'FinancialService',
            'FoodEstablishment', 'Bakery', 'BarOrPub', 'CafeOrCoffeeShop', 'Restaurant', 'GovernmentOffice',
            'HealthAndBeautyBusiness', 'BeautySalon', 'DaySpa', 'HairSalon', 'HomeAndConstructionBusiness',
            'Electrician', 'GeneralContractor', 'Plumber', 'InternetCafe', 'LegalService', 'Attorney', 'Notary',
            'LodgingBusiness', 'Hotel', 'Hostel', 'Motel', 'MedicalBusiness', 'Pharmacy', 'Physician',
            'ProfessionalService', 'RealEstateAgent', 'RecyclingCenter', 'SelfStorage', 'ShoppingCenter',
            'SportsActivityLocation', 'Store', 'TouristInformationCenter', 'TravelAgency', 'Person',
        ],
    ];

    /**
     * A bare list is used as-is, `{"@graph": [...]}` is unwrapped, anything
     * else becomes a one-element list.
     *
     * @return array<int, mixed>
     */
    private static function normalizeSchemaNodes(mixed $obj): array
    {
        if (is_array($obj)) {
            return array_values($obj);
        }
        if ($obj instanceof \stdClass && isset($obj->{'@graph'}) && is_array($obj->{'@graph'})) {
            return array_values($obj->{'@graph'});
        }

        return [$obj];
    }

    /** Removes the script blocks tagged data-seojuice (ours), keeps the site's. */
    private static function stripOwnLdJson(string $html): string
    {
        return (string) preg_replace_callback(
            '/<script([^>]*type=["\']application\/ld\+json["\'][^>]*)>[\s\S]*?<\/script>\n?/i',
            fn (array $m) => preg_match('/\sdata-seojuice(?=[\s=>\/]|\z)/i', $m[1]) === 1 ? '' : $m[0],
            $html,
        );
    }

    /** @return array<string, true> @type values declared by the page's own ld+json blocks. */
    private static function collectPresentLdJsonTypes(string $html): array
    {
        $types = [];
        preg_match_all('/<script([^>]*type=["\']application\/ld\+json["\'][^>]*)>([\s\S]*?)<\/script>/i', $html, $blocks, PREG_SET_ORDER);
        foreach ($blocks as $block) {
            if (preg_match('/\sdata-seojuice(?=[\s=>\/]|\z)/i', $block[1]) === 1) {
                continue;
            }
            $parsed = json_decode($block[2]);
            if (json_last_error() === JSON_ERROR_NONE) {
                self::collectLdJsonTypes($parsed, $types);
            }
        }

        return $types;
    }

    /** @param array<string, true> $types */
    private static function collectLdJsonTypes(mixed $node, array &$types): void
    {
        if (is_array($node)) {
            foreach ($node as $item) {
                self::collectLdJsonTypes($item, $types);
            }

            return;
        }
        if (!$node instanceof \stdClass) {
            return;
        }
        if (isset($node->{'@graph'}) && is_array($node->{'@graph'})) {
            foreach ($node->{'@graph'} as $item) {
                self::collectLdJsonTypes($item, $types);
            }
        }
        $t = $node->{'@type'} ?? null;
        if (is_string($t)) {
            $types[$t] = true;
        } elseif (is_array($t)) {
            foreach ($t as $tt) {
                if (is_string($tt)) {
                    $types[$tt] = true;
                }
            }
        }
    }

    /**
     * A node is "already present" only when every one of its @types is covered.
     * A node without @type is always kept (fail open).
     *
     * @param array<string, true> $present
     */
    private static function ldJsonNodeAlreadyPresent(mixed $node, array $present): bool
    {
        if (!$node instanceof \stdClass) {
            return false;
        }
        $t = $node->{'@type'} ?? null;
        if (is_string($t)) {
            return self::isTypeCovered($t, $present);
        }
        if (is_array($t) && $t !== []) {
            foreach ($t as $tt) {
                if (!is_string($tt) || !self::isTypeCovered($tt, $present)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /** @param array<string, true> $present */
    private static function isTypeCovered(string $proposed, array $present): bool
    {
        foreach (self::SCHEMA_COVERS[$proposed] ?? [$proposed] as $covering) {
            if (isset($present[$covering])) {
                return true;
            }
        }

        return false;
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

        $base = isset($data['base']) && is_string($data['base']) && $data['base'] !== '' ? $data['base'] : null;

        $imageMap = [];
        foreach ($images as $image) {
            $url = (string) ($image['url'] ?? '');
            $altText = (string) ($image['alt_text'] ?? '');

            if ($url !== '' && $altText !== '') {
                $key = self::imageMatchKey($url, $base);
                if ($key !== null) {
                    $imageMap[$key] = $altText;
                }
            }
        }

        if ($imageMap === []) {
            return $html;
        }

        return (string) preg_replace_callback(
            '/<img([^>]+)>/i',
            static function (array $matches) use ($imageMap, $base, &$manifest): string {
                $match = $matches[0];
                $matchedAlt = null;
                foreach (self::imageCandidates($matches[1]) as $candidate) {
                    $key = self::imageMatchKey($candidate, $base);
                    if ($key !== null && isset($imageMap[$key])) {
                        $matchedAlt = $imageMap[$key];
                        break;
                    }
                }
                if ($matchedAlt === null) {
                    return $match;
                }

                $altMatch = [];
                $hasAlt = (bool) preg_match('/alt=["\']([^"\']*)["\']/', $match, $altMatch);
                $existingAlt = $hasAlt ? $altMatch[1] : '';

                if ($existingAlt !== '' && mb_strlen($existingAlt) >= 5) {
                    return $match;
                }

                $altText = self::escapeHtml($matchedAlt);
                $manifest['img']++;

                // $altText comes from escapeHtml() and may contain `$` or `\`; use
                // literal replacement so those are never read as backreferences.
                if ($hasAlt) {
                    $replaced = self::replaceFirstLiteral('/alt=["\'][^"\']*["\']/', 'alt="' . $altText . '"', $match);
                    if (!str_contains($replaced, 'data-seojuice=')) {
                        $replaced = self::replaceFirstLiteral('/<img/', '<img data-seojuice="alt"', $replaced);
                    }

                    return $replaced;
                }

                return self::replaceFirstLiteral('/<img/', '<img alt="' . $altText . '" data-seojuice="alt"', $match);
            },
            $html,
        );
    }

    private const IMAGE_ATTRS = ['src', 'data-src', 'data-lazy-src', 'data-lazy', 'data-original'];
    private const IMAGE_SRCSET_ATTRS = ['srcset', 'data-srcset'];

    private static function readAttr(string $attributes, string $name): ?string
    {
        if (!preg_match('/(?:^|\s)' . preg_quote($name, '/') . '\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $attributes, $m)) {
            return null;
        }

        return ($m[1] ?? '') !== '' ? $m[1] : ($m[2] ?? ($m[1] ?? ''));
    }

    /** @return array<int, string> every URL an <img> could be addressed by, in Worker order */
    private static function imageCandidates(string $attributes): array
    {
        $out = [];
        foreach (self::IMAGE_ATTRS as $name) {
            $v = self::readAttr($attributes, $name);
            if ($v !== null && $v !== '') {
                $out[] = $v;
            }
        }
        foreach (self::IMAGE_SRCSET_ATTRS as $name) {
            $v = self::readAttr($attributes, $name);
            if ($v !== null && $v !== '') {
                foreach (explode(',', $v) as $part) {
                    $u = preg_split('/\s+/', trim($part))[0] ?? '';
                    if ($u !== '') {
                        $out[] = $u;
                    }
                }
            }
        }

        return $out;
    }

    /**
     * Port of seojuice-ssr.js matchKey: resolve against the payload base, skip
     * data:/blob:, unwrap one /_next/image level, percent-decode the path
     * (decodeURI), then strip scheme and query.
     */
    private static function imageMatchKey(string $candidate, ?string $base, bool $unwrapped = false): ?string
    {
        if ($candidate === '') {
            return null;
        }
        $resolved = self::resolveUrl($candidate, $base);
        if ($resolved === null) {
            return preg_match('/^(data|blob):/i', $candidate) === 1 ? null : self::normalizeImageUrl($candidate);
        }
        if ($resolved['scheme'] === 'data' || $resolved['scheme'] === 'blob') {
            return null;
        }
        if (!$unwrapped && $resolved['path'] === '/_next/image') {
            parse_str($resolved['query'], $params);
            $original = $params['url'] ?? null;
            if (is_string($original) && $original !== '') {
                $href = $resolved['scheme'] . '://' . $resolved['host'] . $resolved['path'] . ($resolved['query'] !== '' ? '?' . $resolved['query'] : '');

                return self::imageMatchKey($original, $href, true);
            }
        }

        return self::normalizeImageUrl($resolved['scheme'] . '://' . $resolved['host'] . self::decodeUriPath($resolved['path']));
    }

    /**
     * RFC 3986 resolution, enough for http(s) image URLs. Null when the URL
     * cannot be resolved (relative with no base), like `new URL()` throwing.
     *
     * @return array{scheme: string, host: string, path: string, query: string}|null
     */
    private static function resolveUrl(string $candidate, ?string $base): ?array
    {
        $candidate = trim($candidate);
        if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $candidate, $sm) === 1) {
            $scheme = strtolower($sm[1]);
            if (!in_array($scheme, ['http', 'https'], true)) {
                return ['scheme' => $scheme, 'host' => '', 'path' => '', 'query' => ''];
            }
            $parts = parse_url($candidate);
            if ($parts === false || empty($parts['host'])) {
                return null;
            }

            return self::buildResolved($scheme, $parts['host'], $parts['port'] ?? null, $parts['path'] ?? '/', $parts['query'] ?? '');
        }
        if ($base === null) {
            return null;
        }
        $b = parse_url($base);
        if ($b === false || empty($b['scheme']) || empty($b['host'])) {
            return null;
        }
        $scheme = strtolower($b['scheme']);
        $candidate = explode('#', $candidate)[0];

        if (str_starts_with($candidate, '//')) {
            $parts = parse_url($scheme . ':' . $candidate);
            if ($parts === false || empty($parts['host'])) {
                return null;
            }

            return self::buildResolved($scheme, $parts['host'], $parts['port'] ?? null, $parts['path'] ?? '/', $parts['query'] ?? '');
        }

        $query = '';
        if (str_contains($candidate, '?')) {
            [$candidate, $query] = explode('?', $candidate, 2);
        }
        if ($candidate === '') {
            $path = $b['path'] ?? '/';
            $query = $query !== '' ? $query : ($b['query'] ?? '');
        } elseif ($candidate[0] === '/') {
            $path = $candidate;
        } else {
            $basePath = $b['path'] ?? '/';
            $path = substr($basePath, 0, (int) strrpos($basePath, '/') + 1) . $candidate;
        }

        return self::buildResolved($scheme, $b['host'], $b['port'] ?? null, $path, $query);
    }

    /** @return array{scheme: string, host: string, path: string, query: string} */
    private static function buildResolved(string $scheme, string $host, ?int $port, string $path, string $query): array
    {
        $host = strtolower($host);
        $defaultPort = $scheme === 'https' ? 443 : 80;
        if ($port !== null && $port !== $defaultPort) {
            $host .= ':' . $port;
        }
        $out = [];
        foreach (explode('/', $path === '' ? '/' : $path) as $i => $seg) {
            if ($seg === '..') {
                if (count($out) > 1) {
                    array_pop($out);
                }
            } elseif ($seg !== '.') {
                $out[] = $seg;
            }
        }
        $resolved = implode('/', $out);
        if (str_ends_with($path, '/.') || str_ends_with($path, '/..')) {
            $resolved .= '/';
        }

        return ['scheme' => $scheme, 'host' => $host, 'path' => $resolved === '' ? '/' : $resolved, 'query' => $query];
    }

    /** JS decodeURI: percent-decode UTF-8 except reserved characters; malformed input stays as is. */
    private static function decodeUriPath(string $path): string
    {
        if (preg_match('/%(?![0-9a-fA-F]{2})/', $path) === 1) {
            return $path;
        }
        $failed = false;
        $decoded = (string) preg_replace_callback('/(?:%[0-9a-fA-F]{2})+/', static function (array $m) use (&$failed): string {
            $tokens = str_split($m[0], 3);
            $out = '';
            for ($i = 0, $n = count($tokens); $i < $n; $i++) {
                $byte = hexdec(substr($tokens[$i], 1));
                if ($byte < 0x80) {
                    $out .= str_contains(';/?:@&=+$,#', chr($byte)) ? $tokens[$i] : chr($byte);
                    continue;
                }
                $len = $byte >= 0xF0 ? 4 : ($byte >= 0xE0 ? 3 : ($byte >= 0xC0 ? 2 : 0));
                if ($len === 0 || $i + $len > $n) {
                    $failed = true;

                    return $m[0];
                }
                $bytes = '';
                for ($k = 0; $k < $len; $k++) {
                    $bytes .= chr((int) hexdec(substr($tokens[$i + $k], 1)));
                }
                if (!mb_check_encoding($bytes, 'UTF-8')) {
                    $failed = true;

                    return $m[0];
                }
                $out .= $bytes;
                $i += $len - 1;
            }

            return $out;
        }, $path);

        return $failed ? $path : $decoded;
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
        $contentAreaOnly = (bool) ($data['insert_into_content_only'] ?? false);

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
                ? '/(?<=[\p{Han}\p{Hiragana}\p{Katakana}]|^)(' . $escapedKeyword . ')(?=[\p{Han}\p{Hiragana}\p{Katakana}.!?)\]\/。、！？）」』]|$)/u'
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
        $tagStack = [];
        $result = [];

        foreach ($segments as $segment) {
            if ($segment['type'] === 'tag') {
                if (preg_match(self::SKIP_TAG_RE, $segment['value'])) {
                    $skipDepth++;
                } elseif ($skipDepth > 0 && preg_match(self::CLOSE_TAG_RE, $segment['value'])) {
                    $skipDepth--;
                }

                if ($contentAreaOnly) {
                    self::updateTagStack($segment['value'], $tagStack);
                }

                $result[] = $segment['value'];
                continue;
            }

            $text = $segment['value'];
            $inContentArea = !$contentAreaOnly || ($tagStack !== [] && in_array(end($tagStack), self::CONTENT_AREA_TAGS, true));

            if ($skipDepth === 0 && $inContentArea) {
                foreach ($links as $link) {
                    if (isset($replacedKeywords[$link['kl']])) {
                        continue;
                    }

                    $text = (string) preg_replace_callback(
                        $link['pattern'],
                        static function (array $matches) use ($link, $customLinkClass, &$replacedKeywords, &$manifest): string {
                            // The Worker anchors the matched page text, keeping its casing.
                            if (isset($replacedKeywords[$link['kl']])) {
                                return $matches[0];
                            }

                            $classAttr = $customLinkClass !== '' ? ' class="seojuice-link ' . $customLinkClass . '"' : '';
                            $csAttr = $link['id'] !== null ? ' data-seojuice-cs="' . $link['id'] . '"' : '';
                            $replacement = '<a href="' . self::escapeHtml($link['url']) . '"' . $classAttr . $csAttr . '>' . self::escapeHtml($matches[0]) . '</a>';

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
     * Blanks the *contents* of every <script>/<style> element (tags kept, body
     * replaced with equal-length \x00 runs) so the occurrence/ambiguity checks
     * in applyContentDiffs() only ever "see" the visible document. Same byte
     * length as the input, so an index found in the masked copy is valid
     * against the real html too. Frameworks like Next.js App Router serialize
     * the pre-transform page text into a `__next_f` hydration <script>;
     * without masking, that duplicate trips the ambiguity guard and the diff
     * never applies to the visible body.
     */
    private static function maskScriptStyle(string $html): string
    {
        // preg_replace_callback returns null on a PCRE error (e.g. backtrack
        // limit exhausted on pathological input). Casting that null to string
        // silently blanks the entire document, which would make every
        // applyContentDiffs() lookup fail and skip ALL diffs. Fall back to the
        // unmasked $html instead — this degrades to the pre-mask behavior
        // (script/style duplicates can trip the ambiguity guard) rather than
        // dropping every diff outright.
        $masked = preg_replace_callback(
            self::SCRIPT_STYLE_RE,
            static function (array $matches): string {
                return $matches[1] . str_repeat("\x00", strlen($matches[3])) . $matches[4];
            },
            $html,
        );

        return $masked ?? $html;
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

                $masked = self::maskScriptStyle($html);

                if (str_contains($masked, $replacement) && !str_contains($masked, $original)) {
                    continue; // already applied
                }

                $idx = strpos($masked, $original);
                if ($idx === false) {
                    continue; // not in the visible region → skip (DOM drift, or only in script/style)
                }

                if (strpos($masked, $original, $idx + strlen($original)) !== false) {
                    continue; // ambiguous in the visible region → skip
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

    /**
     * @param array<string, mixed> $data
     */
    public static function validateApiResponse(array $data): bool
    {
        if (!empty($data['errors'])) {
            return false;
        }

        // The Worker (seojuice-ssr.js) applies every transform it is given, so an
        // h1-only payload is actionable (vector h1_replacement_escapes_html).
        $hasContent = !empty($data['title'])
            || !empty($data['h1'])
            || !empty($data['meta_description'])
            || !empty($data['suggestions'])
            || !empty($data['images'])
            || !empty($data['structured_data'])
            || !empty($data['og_title'])
            || !empty($data['broken_link_fixes'])
            || (isset($data['diffs']) && is_array($data['diffs']) && $data['diffs'] !== []);

        if (!$hasContent) {
            return false;
        }

        if (isset($data['suggestions']) && !is_array($data['suggestions'])) {
            return false;
        }

        if (isset($data['images']) && !is_array($data['images'])) {
            return false;
        }

        if (isset($data['diffs']) && !is_array($data['diffs'])) {
            return false;
        }

        if (isset($data['broken_link_fixes']) && !is_array($data['broken_link_fixes'])) {
            return false;
        }

        return true;
    }

    /**
     * @param array{cs: array<int, int|string>, meta: array<int, string>, img: int, schema: int, h1: int} $manifest
     */
    public static function addManifestComment(string $html, array $manifest): string
    {
        if (str_contains($html, '<!-- seojuice:')) {
            return $html;
        }

        $parts = [];

        $csIds = $manifest['cs'] ?? [];
        if ($csIds !== []) {
            $parts[] = 'cs=[' . implode(',', $csIds) . ']';
        }

        $metaKeys = $manifest['meta'] ?? [];
        if ($metaKeys !== []) {
            $parts[] = 'meta=[' . implode(',', $metaKeys) . ']';
        }

        if (($manifest['img'] ?? 0) > 0) {
            $parts[] = 'img=' . $manifest['img'];
        }

        if (!empty($manifest['schema'])) {
            $parts[] = 'schema=1';
        }

        if (!empty($manifest['h1'])) {
            $parts[] = 'h1=1';
        }

        if ($parts === []) {
            return $html;
        }

        $comment = '<!-- seojuice: ' . implode(' ', $parts) . ' -->';

        return (string) preg_replace('/<\/body>/i', $comment . "\n</body>", $html, 1);
    }

    public static function addSsrFlag(string $html): string
    {
        if (str_contains($html, 'window.seojuiceSSR')) {
            return $html;
        }

        return (string) preg_replace(
            '/<\/body>/i',
            "<script>window.seojuiceSSR = true;</script>\n</body>",
            $html,
            1,
        );
    }

    /**
     * @param array<int, string> $stack
     */
    private static function updateTagStack(string $tagValue, array &$stack): void
    {
        if (!preg_match('/^<\/?([a-zA-Z][a-zA-Z0-9]*)/', $tagValue, $matches)) {
            return;
        }

        $name = strtolower($matches[1]);

        if (str_starts_with($tagValue, '</')) {
            if ($stack !== [] && end($stack) === $name) {
                array_pop($stack);
            }

            return;
        }

        $isSelfClosing = str_ends_with(rtrim($tagValue, '>'), '/') || in_array($name, self::VOID_TAGS, true);

        if (!$isSelfClosing) {
            $stack[] = $name;
        }
    }
}
