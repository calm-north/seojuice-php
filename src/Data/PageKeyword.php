<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class PageKeyword
{
    public readonly int $id;
    public readonly string $keyword;
    public readonly ?string $processedAt;
    /** @var array<string, mixed>|null */
    public readonly ?array $stats;

    /**
     * @param array<string, mixed>|null $stats
     */
    public function __construct(
        int $id,
        string $keyword,
        ?string $processedAt,
        ?array $stats,
    ) {
        $this->id = $id;
        $this->keyword = $keyword;
        $this->processedAt = $processedAt;
        $this->stats = $stats;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            keyword: (string) $data['keyword'],
            processedAt: $data['processed_at'] ?? null,
            stats: $data['stats'] ?? null,
        );
    }
}
