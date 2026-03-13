<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ChangeRecord
{
    public readonly int $id;
    public readonly string $changeType;
    public readonly string $status;
    public readonly ?string $pageUrl;
    public readonly ?string $proposedValue;
    public readonly ?string $previousValue;
    public readonly ?string $reason;
    public readonly ?float $confidenceScore;
    public readonly ?string $anchorText;
    /** @var array<int, mixed> */
    public readonly array $alternatives;
    /** @var array<int, mixed> */
    public readonly array $originalIssues;
    /** @var array<int, mixed> */
    public readonly array $optimizationTechniques;
    /** @var array<int, mixed> */
    public readonly array $seoSignalsImproved;
    /** @var array<int, mixed> */
    public readonly array $potentialRisks;
    /** @var array<int, mixed> */
    public readonly array $relatedChanges;
    /** @var array<string, mixed> */
    public readonly array $llmMetadata;
    public readonly ?string $createdAt;
    public readonly ?string $reviewedAt;
    public readonly ?string $appliedAt;
    public readonly ?string $pulledAt;
    public readonly ?string $pulledByIntegration;
    public readonly ?string $verifiedAt;
    public readonly ?string $revertedAt;
    public readonly ?string $revertReason;

    /**
     * @param array<int, mixed> $alternatives
     * @param array<int, mixed> $originalIssues
     * @param array<int, mixed> $optimizationTechniques
     * @param array<int, mixed> $seoSignalsImproved
     * @param array<int, mixed> $potentialRisks
     * @param array<int, mixed> $relatedChanges
     * @param array<string, mixed> $llmMetadata
     */
    public function __construct(
        int $id,
        string $changeType,
        string $status,
        ?string $pageUrl,
        ?string $proposedValue,
        ?string $previousValue,
        ?string $reason,
        ?float $confidenceScore,
        ?string $anchorText,
        array $alternatives,
        array $originalIssues,
        array $optimizationTechniques,
        array $seoSignalsImproved,
        array $potentialRisks,
        array $relatedChanges,
        array $llmMetadata,
        ?string $createdAt,
        ?string $reviewedAt,
        ?string $appliedAt,
        ?string $pulledAt,
        ?string $pulledByIntegration,
        ?string $verifiedAt,
        ?string $revertedAt,
        ?string $revertReason,
    ) {
        $this->id = $id;
        $this->changeType = $changeType;
        $this->status = $status;
        $this->pageUrl = $pageUrl;
        $this->proposedValue = $proposedValue;
        $this->previousValue = $previousValue;
        $this->reason = $reason;
        $this->confidenceScore = $confidenceScore;
        $this->anchorText = $anchorText;
        $this->alternatives = $alternatives;
        $this->originalIssues = $originalIssues;
        $this->optimizationTechniques = $optimizationTechniques;
        $this->seoSignalsImproved = $seoSignalsImproved;
        $this->potentialRisks = $potentialRisks;
        $this->relatedChanges = $relatedChanges;
        $this->llmMetadata = $llmMetadata;
        $this->createdAt = $createdAt;
        $this->reviewedAt = $reviewedAt;
        $this->appliedAt = $appliedAt;
        $this->pulledAt = $pulledAt;
        $this->pulledByIntegration = $pulledByIntegration;
        $this->verifiedAt = $verifiedAt;
        $this->revertedAt = $revertedAt;
        $this->revertReason = $revertReason;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            changeType: (string) ($data['change_type'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            pageUrl: $data['page_url'] ?? null,
            proposedValue: $data['proposed_value'] ?? null,
            previousValue: $data['previous_value'] ?? null,
            reason: $data['reason'] ?? null,
            confidenceScore: isset($data['confidence_score']) ? (float) $data['confidence_score'] : null,
            anchorText: $data['anchor_text'] ?? null,
            alternatives: $data['alternatives'] ?? [],
            originalIssues: $data['original_issues'] ?? [],
            optimizationTechniques: $data['optimization_techniques'] ?? [],
            seoSignalsImproved: $data['seo_signals_improved'] ?? [],
            potentialRisks: $data['potential_risks'] ?? [],
            relatedChanges: $data['related_changes'] ?? [],
            llmMetadata: $data['llm_metadata'] ?? [],
            createdAt: $data['created_at'] ?? null,
            reviewedAt: $data['reviewed_at'] ?? null,
            appliedAt: $data['applied_at'] ?? null,
            pulledAt: $data['pulled_at'] ?? null,
            pulledByIntegration: $data['pulled_by_integration'] ?? null,
            verifiedAt: $data['verified_at'] ?? null,
            revertedAt: $data['reverted_at'] ?? null,
            revertReason: $data['revert_reason'] ?? null,
        );
    }
}
