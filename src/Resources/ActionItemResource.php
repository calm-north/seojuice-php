<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\ActionItem;
use SEOJuice\Data\ActionItemGroup;
use SEOJuice\Data\ActionItemSummary;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\Exceptions\ValidationException;
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
        ?string $sourceType = null,
    ): PaginatedResult {
        $data = $this->http->get("websites/{$this->domain}/action-items/", [
            'status' => $status,
            'category' => $category,
            'priority' => $priority,
            'source_type' => $sourceType,
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

    public const ACTIONS = ['done', 'in_progress', 'dismiss', 'reopen'];

    /**
     * @param string $action one of self::ACTIONS
     */
    public function update(int $itemId, string $action): ActionItem
    {
        if (!in_array($action, self::ACTIONS, true)) {
            throw new ValidationException(
                'Invalid action. Must be one of: ' . implode(', ', self::ACTIONS),
                'validation_error',
            );
        }

        $data = $this->http->patch("websites/{$this->domain}/action-items/{$itemId}/", ['action' => $action]);

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
