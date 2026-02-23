<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class Keyword
{
    public readonly int $id;
    public readonly string $name;
    public readonly ?string $pageUrl;
    public readonly ?string $category;
    public readonly ?int $searchVolume;
    public readonly ?float $keywordDifficulty;
    public readonly ?float $cpc;
    public readonly ?float $competition;
    public readonly ?int $aiSearchVolume;
    public readonly ?string $lastUpdated;

    public function __construct(
        int $id,
        string $name,
        ?string $pageUrl,
        ?string $category,
        ?int $searchVolume,
        ?float $keywordDifficulty,
        ?float $cpc,
        ?float $competition,
        ?int $aiSearchVolume,
        ?string $lastUpdated,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->pageUrl = $pageUrl;
        $this->category = $category;
        $this->searchVolume = $searchVolume;
        $this->keywordDifficulty = $keywordDifficulty;
        $this->cpc = $cpc;
        $this->competition = $competition;
        $this->aiSearchVolume = $aiSearchVolume;
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            pageUrl: $data['page_url'] ?? null,
            category: $data['category'] ?? null,
            searchVolume: isset($data['search_volume']) ? (int) $data['search_volume'] : null,
            keywordDifficulty: isset($data['keyword_difficulty']) ? (float) $data['keyword_difficulty'] : null,
            cpc: isset($data['cpc']) ? (float) $data['cpc'] : null,
            competition: isset($data['competition']) ? (float) $data['competition'] : null,
            aiSearchVolume: isset($data['ai_search_volume']) ? (int) $data['ai_search_volume'] : null,
            lastUpdated: $data['last_updated'] ?? null,
        );
    }
}
