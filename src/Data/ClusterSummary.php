<?php

declare(strict_types=1);

namespace SEOJuice\Data;

class ClusterSummary
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $slug;
    public readonly int $pageCount;
    public readonly ?float $avgPosition;
    public readonly int $clicks;
    public readonly int $impressions;
    public readonly ?float $ctr;
    public readonly ?float $clicksChangePct;
    public readonly string $trend;
    /** @var array<string, mixed>|null */
    public readonly ?array $pillar;
    public readonly ?float $cohesion;
    /** @var array<string, mixed> */
    public readonly array $issues;
    public readonly bool $isActive;

    /**
     * @param array<string, mixed>|null $pillar
     * @param array<string, mixed> $issues
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $pageCount,
        ?float $avgPosition,
        int $clicks = 0,
        int $impressions = 0,
        ?float $ctr = null,
        ?float $clicksChangePct = null,
        string $trend = 'steady',
        ?array $pillar = null,
        ?float $cohesion = null,
        array $issues = [],
        bool $isActive = true,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->pageCount = $pageCount;
        $this->avgPosition = $avgPosition;
        $this->clicks = $clicks;
        $this->impressions = $impressions;
        $this->ctr = $ctr;
        $this->clicksChangePct = $clicksChangePct;
        $this->trend = $trend;
        $this->pillar = $pillar;
        $this->cohesion = $cohesion;
        $this->issues = $issues;
        $this->isActive = $isActive;
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
            clicks: (int) ($data['clicks'] ?? 0),
            impressions: (int) ($data['impressions'] ?? 0),
            ctr: isset($data['ctr']) ? (float) $data['ctr'] : null,
            clicksChangePct: isset($data['clicks_change_pct']) ? (float) $data['clicks_change_pct'] : null,
            trend: (string) ($data['trend'] ?? 'steady'),
            pillar: $data['pillar'] ?? null,
            cohesion: isset($data['cohesion']) ? (float) $data['cohesion'] : null,
            issues: $data['issues'] ?? [],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
