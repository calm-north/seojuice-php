<?php

declare(strict_types=1);

namespace SEOJuice\Data;

class ClusterSummary
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $slug;
    public readonly int $pageCount;
    public readonly int $totalClicks;
    public readonly float $avgPosition;

    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $pageCount,
        int $totalClicks,
        float $avgPosition,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->pageCount = $pageCount;
        $this->totalClicks = $totalClicks;
        $this->avgPosition = $avgPosition;
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
            totalClicks: (int) ($data['total_clicks'] ?? 0),
            avgPosition: (float) ($data['avg_position'] ?? 0),
        );
    }
}
