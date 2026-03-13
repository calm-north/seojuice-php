<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ChangeStats
{
    public readonly int $total;
    /** @var array<string, int> */
    public readonly array $byStatus;
    /** @var array<string, int> */
    public readonly array $byType;

    /**
     * @param array<string, int> $byStatus
     * @param array<string, int> $byType
     */
    public function __construct(int $total, array $byStatus, array $byType)
    {
        $this->total = $total;
        $this->byStatus = $byStatus;
        $this->byType = $byType;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: (int) ($data['total'] ?? 0),
            byStatus: $data['by_status'] ?? [],
            byType: $data['by_type'] ?? [],
        );
    }
}
