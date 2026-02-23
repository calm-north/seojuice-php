<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ReportDetail extends ReportSummary
{
    /** @var array<string, mixed>|null */
    public readonly ?array $summary;
    /** @var array<string, mixed>|null */
    public readonly ?array $data;
    public readonly ?string $updatedAt;
    public readonly ?string $pdfUrl;

    /**
     * @param array<string, mixed>|null $summary
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        int $id,
        string $type,
        string $typeDisplay,
        string $status,
        ?string $date,
        ?string $endDate,
        ?string $createdAt,
        bool $hasPdf,
        ?array $summary,
        ?array $data,
        ?string $updatedAt,
        ?string $pdfUrl,
    ) {
        parent::__construct($id, $type, $typeDisplay, $status, $date, $endDate, $createdAt, $hasPdf);
        $this->summary = $summary;
        $this->data = $data;
        $this->updatedAt = $updatedAt;
        $this->pdfUrl = $pdfUrl;
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
            summary: $data['summary'] ?? null,
            data: $data['data'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            pdfUrl: $data['pdf_url'] ?? null,
        );
    }
}
