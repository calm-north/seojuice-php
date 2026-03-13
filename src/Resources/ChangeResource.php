<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\BulkActionResult;
use SEOJuice\Data\ChangeRecord;
use SEOJuice\Data\ChangeSettings;
use SEOJuice\Data\ChangeStats;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class ChangeResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $status = null,
        ?string $changeType = null,
        ?string $url = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/changes/", [
            'status' => $status,
            'change_type' => $changeType,
            'url' => $url,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ChangeRecord::fromArray($item));
    }

    public function get(int $changeId): ChangeRecord
    {
        $data = $this->http->get("websites/{$this->domain}/changes/{$changeId}/");

        return ChangeRecord::fromArray($data);
    }

    public function stats(): ChangeStats
    {
        $data = $this->http->get("websites/{$this->domain}/changes/stats/");

        return ChangeStats::fromArray($data);
    }

    public function settings(): ChangeSettings
    {
        $data = $this->http->get("websites/{$this->domain}/changes/settings/");

        return ChangeSettings::fromArray($data);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function updateSettings(array $settings): ChangeSettings
    {
        $data = $this->http->patch("websites/{$this->domain}/changes/settings/", $settings);

        return ChangeSettings::fromArray($data);
    }

    public function approve(int $changeId): ChangeRecord
    {
        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/approve/");

        return ChangeRecord::fromArray($data);
    }

    public function reject(int $changeId, ?string $reason = null): ChangeRecord
    {
        $body = [];
        if ($reason !== null) {
            $body['reason'] = $reason;
        }

        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/reject/", $body);

        return ChangeRecord::fromArray($data);
    }

    public function revert(int $changeId, ?string $reason = null): ChangeRecord
    {
        $body = [];
        if ($reason !== null) {
            $body['reason'] = $reason;
        }

        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/revert/", $body);

        return ChangeRecord::fromArray($data);
    }

    public function pull(int $changeId, string $integration): ChangeRecord
    {
        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/pull/", [
            'integration' => $integration,
        ]);

        return ChangeRecord::fromArray($data);
    }

    public function verify(int $changeId, string $integration): ChangeRecord
    {
        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/verify/", [
            'integration' => $integration,
        ]);

        return ChangeRecord::fromArray($data);
    }

    /**
     * @param array<int, int> $ids
     */
    public function bulk(
        string $action,
        array $ids,
        ?string $reason = null,
        ?string $integration = null,
    ): BulkActionResult {
        $body = [
            'action' => $action,
            'ids' => $ids,
        ];
        if ($reason !== null) {
            $body['reason'] = $reason;
        }
        if ($integration !== null) {
            $body['integration'] = $integration;
        }

        $data = $this->http->post("websites/{$this->domain}/changes/bulk/", $body);

        return BulkActionResult::fromArray($data);
    }
}
