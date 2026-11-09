<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ActionItem
{
    public readonly int $id;
    public readonly string $title;
    public readonly ?string $description;
    public readonly ?string $guidance;
    public readonly ?string $category;
    public readonly ?string $priority;
    public readonly string $status;
    public readonly ?string $estimatedEffort;
    public readonly bool $autoFixed;
    public readonly ?string $createdAt;
    public readonly ?string $completedAt;
    public readonly ?string $itemType;
    public readonly ?string $sourceType;
    public readonly ?int $sourceId;
    public readonly bool $autoFixable;
    public readonly ?string $autoFixedAt;
    public readonly ?string $pageUrl;
    public readonly ?int $pageId;
    public readonly ?int $groupId;
    public readonly ?string $updatedAt;

    public function __construct(
        int $id,
        string $title,
        ?string $description,
        ?string $guidance,
        ?string $category,
        ?string $priority,
        string $status,
        ?string $estimatedEffort,
        bool $autoFixed,
        ?string $createdAt,
        ?string $completedAt,
        ?string $itemType = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        bool $autoFixable = false,
        ?string $autoFixedAt = null,
        ?string $pageUrl = null,
        ?int $pageId = null,
        ?int $groupId = null,
        ?string $updatedAt = null,
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->guidance = $guidance;
        $this->category = $category;
        $this->priority = $priority;
        $this->status = $status;
        $this->estimatedEffort = $estimatedEffort;
        $this->autoFixed = $autoFixed;
        $this->createdAt = $createdAt;
        $this->completedAt = $completedAt;
        $this->itemType = $itemType;
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->autoFixable = $autoFixable;
        $this->autoFixedAt = $autoFixedAt;
        $this->pageUrl = $pageUrl;
        $this->pageId = $pageId;
        $this->groupId = $groupId;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: (string) ($data['title'] ?? ''),
            description: $data['description'] ?? null,
            guidance: $data['guidance'] ?? null,
            category: $data['category'] ?? null,
            priority: $data['priority'] ?? null,
            status: (string) ($data['status'] ?? 'open'),
            estimatedEffort: $data['estimated_effort'] ?? null,
            autoFixed: (bool) ($data['auto_fixed'] ?? false),
            createdAt: $data['created_at'] ?? null,
            completedAt: $data['completed_at'] ?? null,
            itemType: $data['item_type'] ?? null,
            sourceType: $data['source_type'] ?? null,
            sourceId: isset($data['source_id']) ? (int) $data['source_id'] : null,
            autoFixable: (bool) ($data['auto_fixable'] ?? false),
            autoFixedAt: $data['auto_fixed_at'] ?? null,
            pageUrl: $data['page_url'] ?? null,
            pageId: isset($data['page_id']) ? (int) $data['page_id'] : null,
            groupId: isset($data['group_id']) ? (int) $data['group_id'] : null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
