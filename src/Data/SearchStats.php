<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class SearchStats
{
    public readonly ?string $date;
    public readonly ?int $clicks;
    public readonly ?int $impressions;
    public readonly ?float $ctr;
    public readonly ?float $rank;

    public function __construct(
        ?string $date,
        ?int $clicks,
        ?int $impressions,
        ?float $ctr,
        ?float $rank,
    ) {
        $this->date = $date;
        $this->clicks = $clicks;
        $this->impressions = $impressions;
        $this->ctr = $ctr;
        $this->rank = $rank;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            date: $data['date'] ?? null,
            clicks: isset($data['clicks']) ? (int) $data['clicks'] : null,
            impressions: isset($data['impressions']) ? (int) $data['impressions'] : null,
            ctr: isset($data['ctr']) ? (float) $data['ctr'] : null,
            rank: isset($data['rank']) ? (float) $data['rank'] : null,
        );
    }
}
