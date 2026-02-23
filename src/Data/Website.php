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

    /**
     * @param array<string, mixed> $scores
     * @param array<string, mixed> $kpis
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
    ) {
        $this->domain = $domain;
        $this->platform = $platform;
        $this->industry = $industry;
        $this->seoScore = $seoScore;
        $this->scores = $scores;
        $this->kpis = $kpis;
        $this->createdAt = $createdAt;
        $this->lastProcessedAt = $lastProcessedAt;
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
        );
    }
}
