<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\ActionItem;
use SEOJuice\Data\ActionItemGroup;
use SEOJuice\Data\ActionItemSummary;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;

final class ActionItemResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    public function list(
        ?string $status = null,
        ?string $category = null,
        ?string $priority = null,
        int $page = 1,
        int $pageSize = 10,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/action-items/", [
            'status' => $status,
            'category' => $category,
            'priority' => $priority,
            'page' => $page,
            'page_size' => $pageSize,
        ]);

        return PaginatedResult::fromArray($data, fn (array $item) => ActionItem::fromArray($item));
    }

    public function get(int $itemId): ActionItem
    {
        $data = $this->http->get("websites/{$this->domain}/action-items/{$itemId}/");

        return ActionItem::fromArray($data);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function create(array $params): ActionItem
    {
        $data = $this->http->post("websites/{$this->domain}/action-items/", $params);

        return ActionItem::fromArray($data);
    }

    public function update(int $itemId, string $action, ?int $snoozeDays = null): ActionItem
    {
        $body = ['action' => $action];
        if ($snoozeDays !== null) {
            $body['snooze_days'] = $snoozeDays;
        }

        $data = $this->http->patch("websites/{$this->domain}/action-items/{$itemId}/", $body);

        return ActionItem::fromArray($data);
    }

    public function groups(): PaginatedResult
    {
        $data = $this->http->get("websites/{$this->domain}/action-items/groups/");

        return PaginatedResult::fromArray($data, fn (array $item) => ActionItemGroup::fromArray($item));
    }

    public function summary(): ActionItemSummary
    {
        $data = $this->http->get("websites/{$this->domain}/action-items/summary/");

        return ActionItemSummary::fromArray($data);
    }
}
