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
    $apiKey = getenv('SEOJUICE_API_KEY') ?: '';
    if ($apiKey === '') {
        fwrite(STDERR, "API key not configured — set SEOJUICE_API_KEY\n");
        return;
    }
    $client = new SEOJuice($apiKey);
    $cache = new SeoRedisCache(new Redis(getenv('REDIS_URL') ?: 'tcp://127.0.0.1:6379'));
    $url = 'https://example.com/blog/seo-guide';

    // Cache-aside pattern — the /suggestions payload is already a plain array,
    // so it round-trips through Redis (json_encode/json_decode) unchanged.
    $data = $cache->getCachedSuggestions($url);

    if ($data === null) {
        $data = $client->smart()->suggestions($url); // returns [] on any failure
        $cache->cacheSuggestions($url, $data);
    }

    $html = '<html><head><title>My Page</title></head><body>Hello</body></html>';
    $injector = new SeoInjector();
    echo $injector->inject($html, $data); // no-op on empty/invalid $data
}

main();
