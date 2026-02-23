<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ContentDecayAlert
{
    public readonly int $id;
    public readonly ?string $pageUrl;
    public readonly string $severity;
    public readonly string $decayType;
    public readonly ?int $clicksPrevious;
    public readonly ?int $clicksCurrent;
    public readonly ?int $impressionsPrevious;
    public readonly ?int $impressionsCurrent;
    public readonly ?float $positionPrevious;
    public readonly ?float $positionCurrent;
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
        ?int $clicksPrevious,
        ?int $clicksCurrent,
        ?int $impressionsPrevious,
        ?int $impressionsCurrent,
        ?float $positionPrevious,
        ?float $positionCurrent,
        bool $isActive,
        string $detectedAt,
        ?string $resolvedAt,
        array $suggestions,
    ) {
        $this->id = $id;
        $this->pageUrl = $pageUrl;
        $this->severity = $severity;
        $this->decayType = $decayType;
        $this->clicksPrevious = $clicksPrevious;
        $this->clicksCurrent = $clicksCurrent;
        $this->impressionsPrevious = $impressionsPrevious;
        $this->impressionsCurrent = $impressionsCurrent;
        $this->positionPrevious = $positionPrevious;
        $this->positionCurrent = $positionCurrent;
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
            clicksPrevious: isset($data['clicks_previous']) ? (int) $data['clicks_previous'] : null,
            clicksCurrent: isset($data['clicks_current']) ? (int) $data['clicks_current'] : null,
            impressionsPrevious: isset($data['impressions_previous']) ? (int) $data['impressions_previous'] : null,
            impressionsCurrent: isset($data['impressions_current']) ? (int) $data['impressions_current'] : null,
            positionPrevious: isset($data['position_previous']) ? (float) $data['position_previous'] : null,
            positionCurrent: isset($data['position_current']) ? (float) $data['position_current'] : null,
            isActive: (bool) ($data['is_active'] ?? false),
            detectedAt: (string) ($data['detected_at'] ?? ''),
            resolvedAt: $data['resolved_at'] ?? null,
            suggestions: $data['suggestions'] ?? [],
        );
    }
}
