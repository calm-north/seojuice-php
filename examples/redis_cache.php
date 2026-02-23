<?php

/**
 * Redis caching layer for SEOJuice suggestion data.
 *
 * Wraps Predis to provide cache-aside helpers with key expiry
 * and pattern-based invalidation.
 *
 * Requirements:
 *     composer require seojuice/seojuice predis/predis
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Predis\Client as Redis;
use SEOJuice\Injection\SeoInjector;
use SEOJuice\Injection\Suggestions;
use SEOJuice\SEOJuice;

class SeoRedisCache
{
    private const PREFIX = 'seojuice:';
    private const TTL = 3600;

    public function __construct(private readonly Redis $redis) {}

    /** @return array<string, mixed>|null */
    public function getCachedSuggestions(string $url): ?array
    {
        try {
            $raw = $this->redis->get(self::PREFIX . $url);
        } catch (\Exception) {
            return null; // fail-open
        }

        if ($raw === null) {
            return null;
        }

        return json_decode($raw, true);
    }

    /** @param array<string, mixed> $data */
    public function cacheSuggestions(string $url, array $data, int $ttl = self::TTL): void
    {
        try {
            $this->redis->setex(self::PREFIX . $url, $ttl, json_encode($data));
        } catch (\Exception) {
            // fail-open
        }
    }

    public function invalidateUrl(string $url): void
    {
        $this->redis->del(self::PREFIX . $url);
    }

    public function invalidatePattern(string $domain): void
    {
        $cursor = '0';
        do {
            [$cursor, $keys] = $this->redis->scan($cursor, [
                'MATCH' => self::PREFIX . "*{$domain}*",
                'COUNT' => 200,
            ]);

            if (!empty($keys)) {
                $this->redis->del($keys);
            }
        } while ($cursor !== '0');
    }
}

function main(): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));
    $cache = new SeoRedisCache(new Redis(getenv('REDIS_URL') ?: 'tcp://127.0.0.1:6379'));
    $url = 'https://example.com/blog/seo-guide';

    // Cache-aside pattern
    $data = $cache->getCachedSuggestions($url);

    if ($data === null) {
        $suggestions = $client->smart()->suggestions($url);
        // Store the raw arrays for caching
        $data = [
            'links' => $suggestions->links,
            'images' => $suggestions->images,
            'meta_tags' => $suggestions->metaTags,
            'structured_data' => $suggestions->structuredData,
            'accessibility_fixes' => $suggestions->accessibilityFixes,
            'og_tags' => $suggestions->ogTags,
        ];
        $cache->cacheSuggestions($url, $data);
    }

    // Reconstruct typed Suggestions from cached data
    $suggestions = Suggestions::fromArray($data);

    if (!$suggestions->isEmpty()) {
        $html = '<html><head><title>My Page</title></head><body>Hello</body></html>';
        $injector = new SeoInjector();
        echo $injector->inject($html, $suggestions);
    }
}

main();
