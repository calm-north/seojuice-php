<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\PaginatedResult;
use SEOJuice\Data\ReportDetail;
use SEOJuice\Data\ReportSummary;
use SEOJuice\Enums\ReportType;
use SEOJuice\HttpClient;

final class ReportResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(int $page = 1, int $pageSize = 10): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/reports/", [
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ReportSummary::fromArray($item));
    }

    public function get(int $reportId): ReportDetail
    {
        $data = $this->http->get("websites/{$this->domain}/reports/{$reportId}/");

        return ReportDetail::fromArray($data);
    }

    public function downloadPdf(int $reportId): string
    {
        return $this->http->getRaw("websites/{$this->domain}/reports/{$reportId}/pdf/");
    }

    /**
     * @return array<string, mixed>
     */
    public function create(ReportType $type = ReportType::ThisMonth): array
    {
        return $this->http->post("websites/{$this->domain}/reports/", [
            'type' => $type->value,
        ]);
    }
}
