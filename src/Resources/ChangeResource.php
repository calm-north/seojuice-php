<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\BulkActionResult;
use SEOJuice\Data\ChangeRecord;
use SEOJuice\Data\ChangeSettings;
use SEOJuice\Data\ChangeStats;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\Exceptions\ValidationException;
use SEOJuice\HttpClient;

final class ChangeResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    /**
     * The list filter only knows the legacy names (partners/views.py
     * legacy_to_lifecycle); a lifecycle value would return an empty page.
     */
    private const LIFECYCLE_TO_FILTER = ['proposed' => 'pending', 'live' => 'applied', 'rolled_back' => 'reverted'];

    public function list(
        ?string $status = null,
        ?string $changeType = null,
        ?string $url = null,
        int $page = 1,
        int $pageSize = 10,
        ?string $batchId = null,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/changes/", [
            'status' => $status !== null ? (self::LIFECYCLE_TO_FILTER[$status] ?? $status) : null,
            'change_type' => $changeType,
            'url' => $url,
            'page' => $page,
            'page_size' => $pageSize,
            'batch_id' => $batchId,
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

    /**
     * Heading (h1) changes need $confirmHeading = true; the server answers 400
     * confirm_heading_required otherwise.
     */
    public function approve(int $changeId, bool $confirmHeading = false): ChangeRecord
    {
        $body = $confirmHeading ? ['confirm_heading' => true] : [];

        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/approve/", $body);

        return ChangeRecord::fromArray($data);
    }

    public function reject(int $changeId, string $reason): ChangeRecord
    {
        if (trim($reason) === '') {
            throw new ValidationException('reason is required when rejecting a change', 'missing_field');
        }

        $data = $this->http->post("websites/{$this->domain}/changes/{$changeId}/reject/", [
            'reason' => $reason,
        ]);

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
     * Pass $ids, $batchId, or both; the server requires at least one.
     *
     * @param array<int, int> $ids
     */
    public function bulk(
        string $action,
        array $ids = [],
        ?string $reason = null,
        ?string $integration = null,
        ?string $batchId = null,
    ): BulkActionResult {
        if ($ids === [] && $batchId === null) {
            throw new ValidationException('ids or batchId is required', 'missing_field');
        }

        $body = ['action' => $action];
        if ($ids !== []) {
            $body['ids'] = $ids;
        }
        if ($batchId !== null) {
            $body['batch_id'] = $batchId;
        }
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
