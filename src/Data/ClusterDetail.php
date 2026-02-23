<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ClusterDetail extends ClusterSummary
{
    /** @var array<int, mixed> */
    public readonly array $topKeywords;
    /** @var array<int, mixed> */
    public readonly array $timeSeries;

    /**
     * @param array<int, mixed> $topKeywords
     * @param array<int, mixed> $timeSeries
     */
    public function __construct(
        int $id,
        string $name,
        string $slug,
        int $pageCount,
        int $totalClicks,
        float $avgPosition,
        array $topKeywords,
        array $timeSeries,
    ) {
        parent::__construct($id, $name, $slug, $pageCount, $totalClicks, $avgPosition);
        $this->topKeywords = $topKeywords;
        $this->timeSeries = $timeSeries;
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
            topKeywords: $data['top_keywords'] ?? [],
            timeSeries: $data['time_series'] ?? [],
        );
    }
}
