<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ContentDecayAlert
{
    public readonly int $id;
    public readonly ?string $pageUrl;
    public readonly string $severity;
    public readonly string $decayType;
    public readonly ?int $clicksBaseline;
    public readonly ?int $clicksCurrent;
    public readonly ?float $clicksChangePct;
    public readonly ?int $impressionsBaseline;
    public readonly ?int $impressionsCurrent;
    public readonly ?float $impressionsChangePct;
    public readonly ?float $positionBaseline;
    public readonly ?float $positionCurrent;
    public readonly ?float $positionChangePct;
    public readonly bool $isActive;
    public readonly string $detectedAt;
    public readonly ?string $resolvedAt;
    /** @var array<int, mixed> */
    public readonly array $suggestions;

    /**
     * @param array<int, mixed> $suggestions
     */
    public function __construct(
        int $id,
        ?string $pageUrl,
        string $severity,
        string $decayType,
        ?int $clicksBaseline,
        ?int $clicksCurrent,
        ?float $clicksChangePct,
        ?int $impressionsBaseline,
        ?int $impressionsCurrent,
        ?float $impressionsChangePct,
        ?float $positionBaseline,
        ?float $positionCurrent,
        ?float $positionChangePct,
        bool $isActive,
        string $detectedAt,
        ?string $resolvedAt,
        array $suggestions,
    ) {
        $this->id = $id;
        $this->pageUrl = $pageUrl;
        $this->severity = $severity;
        $this->decayType = $decayType;
        $this->clicksBaseline = $clicksBaseline;
        $this->clicksCurrent = $clicksCurrent;
        $this->clicksChangePct = $clicksChangePct;
        $this->impressionsBaseline = $impressionsBaseline;
        $this->impressionsCurrent = $impressionsCurrent;
        $this->impressionsChangePct = $impressionsChangePct;
        $this->positionBaseline = $positionBaseline;
        $this->positionCurrent = $positionCurrent;
        $this->positionChangePct = $positionChangePct;
        $this->isActive = $isActive;
        $this->detectedAt = $detectedAt;
        $this->resolvedAt = $resolvedAt;
        $this->suggestions = $suggestions;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            pageUrl: $data['page_url'] ?? null,
            severity: (string) ($data['severity'] ?? ''),
            decayType: (string) ($data['decay_type'] ?? ''),
            clicksBaseline: isset($data['clicks_baseline']) ? (int) $data['clicks_baseline'] : null,
            clicksCurrent: isset($data['clicks_current']) ? (int) $data['clicks_current'] : null,
            clicksChangePct: isset($data['clicks_change_pct']) ? (float) $data['clicks_change_pct'] : null,
            impressionsBaseline: isset($data['impressions_baseline']) ? (int) $data['impressions_baseline'] : null,
            impressionsCurrent: isset($data['impressions_current']) ? (int) $data['impressions_current'] : null,
            impressionsChangePct: isset($data['impressions_change_pct']) ? (float) $data['impressions_change_pct'] : null,
            positionBaseline: isset($data['position_baseline']) ? (float) $data['position_baseline'] : null,
            positionCurrent: isset($data['position_current']) ? (float) $data['position_current'] : null,
            positionChangePct: isset($data['position_change_pct']) ? (float) $data['position_change_pct'] : null,
            isActive: (bool) ($data['is_active'] ?? false),
            detectedAt: (string) ($data['detected_at'] ?? ''),
            resolvedAt: $data['resolved_at'] ?? null,
            suggestions: $data['suggestions'] ?? [],
        );
    }
}
