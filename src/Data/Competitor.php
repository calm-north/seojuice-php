<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Competitor
{
    public readonly int $id;
    public readonly string $domain;
    public readonly float $score;
    public readonly ?int $intersections;
    public readonly ?int $estimatedTraffic;
    public readonly int $contentGapsCount;
    public readonly ?float $avgPosition;
    /** @var array<int, mixed> */
    public readonly array $topKeywords;
    /** @var array<string, mixed>|null */
    public readonly ?array $trends;

    /**
     * @param array<int, mixed> $topKeywords
     * @param array<string, mixed>|null $trends
     */
    public function __construct(
        int $id,
        string $domain,
        float $score,
        ?int $intersections,
        ?int $estimatedTraffic,
        int $contentGapsCount,
        ?float $avgPosition,
        array $topKeywords,
        ?array $trends,
    ) {
        $this->id = $id;
        $this->domain = $domain;
        $this->score = $score;
        $this->intersections = $intersections;
        $this->estimatedTraffic = $estimatedTraffic;
        $this->contentGapsCount = $contentGapsCount;
        $this->avgPosition = $avgPosition;
        $this->topKeywords = $topKeywords;
        $this->trends = $trends;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            domain: (string) $data['domain'],
            score: (float) ($data['score'] ?? 0),
            intersections: isset($data['intersections']) ? (int) $data['intersections'] : null,
            estimatedTraffic: isset($data['estimated_traffic']) ? (int) $data['estimated_traffic'] : null,
            contentGapsCount: (int) ($data['content_gaps_count'] ?? 0),
            avgPosition: isset($data['avg_position']) ? (float) $data['avg_position'] : null,
            topKeywords: $data['top_keywords'] ?? [],
            trends: $data['trends'] ?? null,
        );
    }
}
