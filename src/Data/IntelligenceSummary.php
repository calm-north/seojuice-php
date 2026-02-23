<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class IntelligenceSummary
{
    public readonly string $domain;
    public readonly float $seoScore;
    public readonly float $aisoScore;
    public readonly int $totalPages;
    public readonly int $totalClusters;
    public readonly int $totalInternalLinks;
    public readonly int $orphanPages;
    public readonly int $contentGaps;
    public readonly ?string $lastCrawledAt;
    /** @var array<string, mixed>|null */
    public readonly ?array $history;
    /** @var array<string, mixed>|null */
    public readonly ?array $trends;

    /**
     * @param array<string, mixed>|null $history
     * @param array<string, mixed>|null $trends
     */
    public function __construct(
        string $domain,
        float $seoScore,
        float $aisoScore,
        int $totalPages,
        int $totalClusters,
        int $totalInternalLinks,
        int $orphanPages,
        int $contentGaps,
        ?string $lastCrawledAt,
        ?array $history,
        ?array $trends,
    ) {
        $this->domain = $domain;
        $this->seoScore = $seoScore;
        $this->aisoScore = $aisoScore;
        $this->totalPages = $totalPages;
        $this->totalClusters = $totalClusters;
        $this->totalInternalLinks = $totalInternalLinks;
        $this->orphanPages = $orphanPages;
        $this->contentGaps = $contentGaps;
        $this->lastCrawledAt = $lastCrawledAt;
        $this->history = $history;
        $this->trends = $trends;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: (string) $data['domain'],
            seoScore: (float) ($data['seo_score'] ?? 0),
            aisoScore: (float) ($data['aiso_score'] ?? 0),
            totalPages: (int) ($data['total_pages'] ?? 0),
            totalClusters: (int) ($data['total_clusters'] ?? 0),
            totalInternalLinks: (int) ($data['total_internal_links'] ?? 0),
            orphanPages: (int) ($data['orphan_pages'] ?? 0),
            contentGaps: (int) ($data['content_gaps'] ?? 0),
            lastCrawledAt: $data['last_crawled_at'] ?? null,
            history: $data['history'] ?? null,
            trends: $data['trends'] ?? null,
        );
    }
}
