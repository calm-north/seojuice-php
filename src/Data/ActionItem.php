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
    /** @var array<int, mixed> */
    public readonly array $affectedPages;
    /** @var array<string, mixed>|null */
    public readonly ?array $page;

    /**
     * @param array<int, mixed> $affectedPages
     * @param array<string, mixed>|null $page
     */
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
        array $affectedPages,
        ?array $page,
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
        $this->affectedPages = $affectedPages;
        $this->page = $page;
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
            affectedPages: $data['affected_pages'] ?? [],
            page: $data['page'] ?? null,
        );
    }
}
