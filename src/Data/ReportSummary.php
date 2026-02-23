<?php

declare(strict_types=1);

namespace SEOJuice\Data;

class ReportSummary
{
    public readonly int $id;
    public readonly string $type;
    public readonly string $typeDisplay;
    public readonly string $status;
    public readonly ?string $date;
    public readonly ?string $endDate;
    public readonly ?string $createdAt;
    public readonly bool $hasPdf;

    public function __construct(
        int $id,
        string $type,
        string $typeDisplay,
        string $status,
        ?string $date,
        ?string $endDate,
        ?string $createdAt,
        bool $hasPdf,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->typeDisplay = $typeDisplay;
        $this->status = $status;
        $this->date = $date;
        $this->endDate = $endDate;
        $this->createdAt = $createdAt;
        $this->hasPdf = $hasPdf;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            type: (string) ($data['type'] ?? ''),
            typeDisplay: (string) ($data['type_display'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            date: $data['date'] ?? null,
            endDate: $data['end_date'] ?? null,
            createdAt: $data['created_at'] ?? null,
            hasPdf: (bool) ($data['has_pdf'] ?? false),
        );
    }
}
