<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class AccessibilityIssue
{
    public readonly int $id;
    public readonly ?string $pageUrl;
    public readonly string $category;
    public readonly string $severity;
    public readonly ?string $wcagCriterion;
    public readonly ?string $description;
    public readonly ?string $fixGuidance;
    public readonly ?string $elementSnippet;
    public readonly bool $autoFixable;
    public readonly bool $autoFixed;
    public readonly ?string $createdAt;

    public function __construct(
        int $id,
        ?string $pageUrl,
        string $category,
        string $severity,
        ?string $wcagCriterion,
        ?string $description,
        ?string $fixGuidance,
        ?string $elementSnippet,
        bool $autoFixable,
        bool $autoFixed,
        ?string $createdAt,
    ) {
        $this->id = $id;
        $this->pageUrl = $pageUrl;
        $this->category = $category;
        $this->severity = $severity;
        $this->wcagCriterion = $wcagCriterion;
        $this->description = $description;
        $this->fixGuidance = $fixGuidance;
        $this->elementSnippet = $elementSnippet;
        $this->autoFixable = $autoFixable;
        $this->autoFixed = $autoFixed;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            pageUrl: $data['page_url'] ?? null,
            category: (string) ($data['category'] ?? ''),
            severity: (string) ($data['severity'] ?? ''),
            wcagCriterion: $data['wcag_criterion'] ?? null,
            description: $data['description'] ?? null,
            fixGuidance: $data['fix_guidance'] ?? null,
            elementSnippet: $data['element_snippet'] ?? null,
            autoFixable: (bool) ($data['auto_fixable'] ?? false),
            autoFixed: (bool) ($data['auto_fixed'] ?? false),
            createdAt: $data['created_at'] ?? null,
        );
    }
}
