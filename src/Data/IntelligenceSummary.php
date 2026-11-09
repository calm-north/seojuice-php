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
    public readonly ?float $domainHealthScore;
    public readonly ?float $avgContentQualityScore;
    public readonly ?float $avgGeoReadinessScore;
    public readonly int $totalSeojuiceLinks;

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
        ?float $domainHealthScore = null,
        ?float $avgContentQualityScore = null,
        ?float $avgGeoReadinessScore = null,
        int $totalSeojuiceLinks = 0,
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
        $this->domainHealthScore = $domainHealthScore;
        $this->avgContentQualityScore = $avgContentQualityScore;
        $this->avgGeoReadinessScore = $avgGeoReadinessScore;
        $this->totalSeojuiceLinks = $totalSeojuiceLinks;
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
            domainHealthScore: isset($data['domain_health_score']) ? (float) $data['domain_health_score'] : null,
            avgContentQualityScore: isset($data['avg_content_quality_score']) ? (float) $data['avg_content_quality_score'] : null,
            avgGeoReadinessScore: isset($data['avg_geo_readiness_score']) ? (float) $data['avg_geo_readiness_score'] : null,
            totalSeojuiceLinks: (int) ($data['total_seojuice_links'] ?? 0),
        );
    }
}
