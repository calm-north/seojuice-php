<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Website
{
    public readonly string $domain;
    public readonly ?string $platform;
    public readonly ?string $industry;
    public readonly ?float $seoScore;
    /** @var array<string, mixed> */
    public readonly array $scores;
    /** @var array<string, mixed> */
    public readonly array $kpis;
    public readonly ?string $createdAt;
    public readonly ?string $lastProcessedAt;
    public readonly ?float $domainHealthScore;
    /** @var array<string, mixed>|null */
    public readonly ?array $domainHealthBreakdown;
    /** @var array<string, mixed>|null */
    public readonly ?array $report;
    public readonly ?float $avgContentQualityScore;
    public readonly ?float $avgGeoReadinessScore;

    /**
     * @param array<string, mixed> $scores
     * @param array<string, mixed> $kpis
     * @param array<string, mixed>|null $domainHealthBreakdown
     * @param array<string, mixed>|null $report
     */
    public function __construct(
        string $domain,
        ?string $platform,
        ?string $industry,
        ?float $seoScore,
        array $scores,
        array $kpis,
        ?string $createdAt,
        ?string $lastProcessedAt,
        ?float $domainHealthScore = null,
        ?array $domainHealthBreakdown = null,
        ?array $report = null,
        ?float $avgContentQualityScore = null,
        ?float $avgGeoReadinessScore = null,
    ) {
        $this->domain = $domain;
        $this->platform = $platform;
        $this->industry = $industry;
        $this->seoScore = $seoScore;
        $this->scores = $scores;
        $this->kpis = $kpis;
        $this->createdAt = $createdAt;
        $this->lastProcessedAt = $lastProcessedAt;
        $this->domainHealthScore = $domainHealthScore;
        $this->domainHealthBreakdown = $domainHealthBreakdown;
        $this->report = $report;
        $this->avgContentQualityScore = $avgContentQualityScore;
        $this->avgGeoReadinessScore = $avgGeoReadinessScore;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            domain: (string) $data['domain'],
            platform: $data['platform'] ?? null,
            industry: $data['industry'] ?? null,
            seoScore: isset($data['seo_score']) ? (float) $data['seo_score'] : null,
            scores: $data['scores'] ?? [],
            kpis: $data['kpis'] ?? [],
            createdAt: $data['created_at'] ?? null,
            lastProcessedAt: $data['last_processed_at'] ?? null,
            domainHealthScore: isset($data['domain_health_score']) ? (float) $data['domain_health_score'] : null,
            domainHealthBreakdown: $data['domain_health_breakdown'] ?? null,
            report: $data['report'] ?? null,
            avgContentQualityScore: isset($data['avg_content_quality_score']) ? (float) $data['avg_content_quality_score'] : null,
            avgGeoReadinessScore: isset($data['avg_geo_readiness_score']) ? (float) $data['avg_geo_readiness_score'] : null,
        );
    }
}
