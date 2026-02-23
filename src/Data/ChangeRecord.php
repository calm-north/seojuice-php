<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ChangeRecord
{
    public readonly int $id;
    public readonly string $changeType;
    public readonly string $status;
    public readonly string $riskLevel;
    public readonly ?string $pageUrl;
    public readonly ?string $proposedValue;
    public readonly ?string $previousValue;
    public readonly ?string $reason;
    public readonly ?float $confidenceScore;
    public readonly ?string $createdAt;

    public function __construct(
        int $id,
        string $changeType,
        string $status,
        string $riskLevel,
        ?string $pageUrl,
        ?string $proposedValue,
        ?string $previousValue,
        ?string $reason,
        ?float $confidenceScore,
        ?string $createdAt,
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
        $this->createdAt = $createdAt;
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
            riskLevel: (string) ($data['risk_level'] ?? ''),
            pageUrl: $data['page_url'] ?? null,
            proposedValue: $data['proposed_value'] ?? null,
            previousValue: $data['previous_value'] ?? null,
            reason: $data['reason'] ?? null,
            confidenceScore: isset($data['confidence_score']) ? (float) $data['confidence_score'] : null,
            createdAt: $data['created_at'] ?? null,
        );
    }
}
