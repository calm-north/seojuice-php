<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ClusterDetail extends ClusterSummary
{
    /** @var array<int, mixed> */
    public readonly array $topQueries;
    /** @var array<int, mixed> */
    public readonly array $timeSeries;
    /** @var array<string, mixed> */
    public readonly array $qualityHistory;

    /**
     * An inactive cluster comes back with only id, name, slug and is_active=false.
     *
     * @param array<string, mixed>|null $pillar
     * @param array<string, mixed> $issues
     * @param array<int, mixed> $topQueries
     * @param array<int, mixed> $timeSeries
     * @param array<string, mixed> $qualityHistory
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $pageCount,
        ?float $avgPosition,
        array $topQueries,
        array $timeSeries,
        int $clicks = 0,
        int $impressions = 0,
        ?float $ctr = null,
        ?float $clicksChangePct = null,
        string $trend = 'steady',
        ?array $pillar = null,
        ?float $cohesion = null,
        array $issues = [],
        bool $isActive = true,
        array $qualityHistory = [],
    ) {
        parent::__construct(
            $id, $name, $slug, $pageCount, $avgPosition, $clicks, $impressions, $ctr,
            $clicksChangePct, $trend, $pillar, $cohesion, $issues, $isActive,
        );
        $this->topQueries = $topQueries;
        $this->timeSeries = $timeSeries;
        $this->qualityHistory = $qualityHistory;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            slug: (string) ($data['slug'] ?? ''),
            pageCount: (int) ($data['page_count'] ?? 0),
            avgPosition: isset($data['avg_position']) ? (float) $data['avg_position'] : null,
            topQueries: $data['top_queries'] ?? [],
            timeSeries: $data['time_series'] ?? [],
            clicks: (int) ($data['clicks'] ?? 0),
            impressions: (int) ($data['impressions'] ?? 0),
            ctr: isset($data['ctr']) ? (float) $data['ctr'] : null,
            clicksChangePct: isset($data['clicks_change_pct']) ? (float) $data['clicks_change_pct'] : null,
            trend: (string) ($data['trend'] ?? 'steady'),
            pillar: $data['pillar'] ?? null,
            cohesion: isset($data['cohesion']) ? (float) $data['cohesion'] : null,
            issues: $data['issues'] ?? [],
            isActive: (bool) ($data['is_active'] ?? true),
            qualityHistory: $data['quality_history'] ?? [],
        );
    }
}
