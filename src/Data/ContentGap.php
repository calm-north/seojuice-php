<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ContentGap
{
    public readonly int $id;
    public readonly string $pageName;
    public readonly string $category;
    public readonly string $intent;
    public readonly float $seoPotential;
    public readonly int $totalSearchVolume;
    /** @var array<int, mixed> */
    public readonly array $keywords;
    public readonly bool $aisoDriven;
    public readonly bool $isGenerated;
    public readonly bool $hasPotentialCandidate;

    /**
     * @param array<int, mixed> $keywords
     */
    public function __construct(
        int $id,
        string $pageName,
        string $category,
        string $intent,
        float $seoPotential,
        int $totalSearchVolume,
        array $keywords,
        bool $aisoDriven,
        bool $isGenerated,
        bool $hasPotentialCandidate,
    ) {
        $this->id = $id;
        $this->pageName = $pageName;
        $this->category = $category;
        $this->intent = $intent;
        $this->seoPotential = $seoPotential;
        $this->totalSearchVolume = $totalSearchVolume;
        $this->keywords = $keywords;
        $this->aisoDriven = $aisoDriven;
        $this->isGenerated = $isGenerated;
        $this->hasPotentialCandidate = $hasPotentialCandidate;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            pageName: (string) ($data['page_name'] ?? ''),
            category: (string) ($data['category'] ?? ''),
            intent: (string) ($data['intent'] ?? ''),
            seoPotential: (float) ($data['seo_potential'] ?? 0),
            totalSearchVolume: (int) ($data['total_search_volume'] ?? 0),
            keywords: $data['keywords'] ?? [],
            aisoDriven: (bool) ($data['aiso_driven'] ?? false),
            isGenerated: (bool) ($data['is_generated'] ?? false),
            hasPotentialCandidate: (bool) ($data['has_potential_candidate'] ?? false),
        );
    }
}
