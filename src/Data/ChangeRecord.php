<?php

declare(strict_types=1);

namespace SEOJuice\Data;

use SEOJuice\Enums\ChangeStatus;
use SEOJuice\Enums\ChangeType;

final class ChangeRecord
{
    public readonly int $id;
    public readonly string $changeType;
    public readonly string $status;
    public readonly ?string $riskLevel;
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
    public readonly ?string $batchId;
    public readonly ?string $batchLabel;
    public readonly bool $editedManually;
    public readonly ?string $mutationType;
    public readonly ?string $verificationState;
    public readonly ?string $outcomeState;
    /** @var array<string, mixed>|null */
    public readonly ?array $apply;

    /**
     * @param array<int, mixed> $alternatives
     * @param array<int, mixed> $originalIssues
     * @param array<int, mixed> $optimizationTechniques
     * @param array<int, mixed> $seoSignalsImproved
     * @param array<int, mixed> $potentialRisks
     * @param array<int, mixed> $relatedChanges
     * @param array<string, mixed> $llmMetadata
     * @param array<string, mixed>|null $apply
     */
    public function __construct(
        int $id,
        string $changeType,
        string $status,
        ?string $riskLevel,
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
        ?string $batchId,
        ?string $batchLabel,
        bool $editedManually,
        ?string $mutationType = null,
        ?string $verificationState = null,
        ?string $outcomeState = null,
        ?array $apply = null,
    ) {
        $this->id = $id;
        $this->changeType = $changeType;
        $this->status = $status;
        $this->riskLevel = $riskLevel;
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
        $this->batchId = $batchId;
        $this->batchLabel = $batchLabel;
        $this->editedManually = $editedManually;
        $this->mutationType = $mutationType;
        $this->verificationState = $verificationState;
        $this->outcomeState = $outcomeState;
        $this->apply = $apply;
    }

    /** Null for a status this SDK version does not know. */
    public function statusEnum(): ?ChangeStatus
    {
        return ChangeStatus::tryFrom($this->status);
    }

    /** Null for a change type this SDK version does not know. */
    public function changeTypeEnum(): ?ChangeType
    {
        return ChangeType::tryFrom($this->changeType);
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
            riskLevel: $data['risk_level'] ?? null,
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
            batchId: $data['batch_id'] ?? null,
            batchLabel: $data['batch_label'] ?? null,
            editedManually: (bool) ($data['edited_manually'] ?? false),
            mutationType: $data['mutation_type'] ?? null,
            verificationState: $data['verification_state'] ?? null,
            outcomeState: $data['outcome_state'] ?? null,
            apply: $data['apply'] ?? null,
        );
    }
}
