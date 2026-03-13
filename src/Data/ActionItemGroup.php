<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ActionItemGroup
{
    public readonly string $category;
    public readonly int $count;
    /** @var array<string, int> */
    public readonly array $priorityDistribution;

    /**
     * @param array<string, int> $priorityDistribution
     */
    public function __construct(
        string $category,
        int $count,
        array $priorityDistribution,
    ) {
        $this->category = $category;
        $this->count = $count;
        $this->priorityDistribution = $priorityDistribution;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: (string) ($data['category'] ?? ''),
            count: (int) ($data['count'] ?? 0),
            priorityDistribution: $data['priority_distribution'] ?? [],
        );
    }
}
