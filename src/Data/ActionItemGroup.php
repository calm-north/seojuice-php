<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ActionItemGroup
{
    public readonly string $category;
    public readonly int $id;
    public readonly string $title;
    public readonly ?string $itemType;
    public readonly ?string $priority;
    public readonly ?string $sourceType;
    public readonly int $totalItems;
    public readonly int $openItems;
    public readonly int $doneItems;
    public readonly string $status;

    public function __construct(
        string $category,
        int $id = 0,
        string $title = '',
        ?string $itemType = null,
        ?string $priority = null,
        ?string $sourceType = null,
        int $totalItems = 0,
        int $openItems = 0,
        int $doneItems = 0,
        string $status = '',
    ) {
        $this->category = $category;
        $this->id = $id;
        $this->title = $title;
        $this->itemType = $itemType;
        $this->priority = $priority;
        $this->sourceType = $sourceType;
        $this->totalItems = $totalItems;
        $this->openItems = $openItems;
        $this->doneItems = $doneItems;
        $this->status = $status;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: (string) ($data['category'] ?? ''),
            id: (int) ($data['id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            itemType: $data['item_type'] ?? null,
            priority: $data['priority'] ?? null,
            sourceType: $data['source_type'] ?? null,
            totalItems: (int) ($data['total_items'] ?? 0),
            openItems: (int) ($data['open_items'] ?? 0),
            doneItems: (int) ($data['done_items'] ?? 0),
            status: (string) ($data['status'] ?? ''),
        );
    }
}
