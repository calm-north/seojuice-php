<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class PageSpeed
{
    public readonly string $url;
    public readonly ?float $loadingTime;
    /** @var array<string, mixed> */
    public readonly array $coreWebVitals;
    /** @var array<string, mixed> */
    public readonly array $scores;
    /** @var array<string, mixed> */
    public readonly array $resourceSizes;
    public readonly ?string $measuredAt;

    /**
     * @param array<string, mixed> $coreWebVitals
     * @param array<string, mixed> $scores
     * @param array<string, mixed> $resourceSizes
     */
    public function __construct(
        string $url,
        ?float $loadingTime,
        array $coreWebVitals,
        array $scores,
        array $resourceSizes,
        ?string $measuredAt,
    ) {
        $this->url = $url;
        $this->loadingTime = $loadingTime;
        $this->coreWebVitals = $coreWebVitals;
        $this->scores = $scores;
        $this->resourceSizes = $resourceSizes;
        $this->measuredAt = $measuredAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            url: (string) $data['url'],
            loadingTime: isset($data['loading_time']) ? (float) $data['loading_time'] : null,
            coreWebVitals: $data['core_web_vitals'] ?? [],
            scores: $data['scores'] ?? [],
            resourceSizes: $data['resource_sizes'] ?? [],
            measuredAt: $data['measured_at'] ?? null,
        );
    }
}
