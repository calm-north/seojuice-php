<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class AnalysisStatus
{
    public readonly string $analysisId;
    public readonly string $status;
    public readonly string $url;
    public readonly ?string $statusUrl;
    public readonly ?int $estimatedTimeSeconds;
    public readonly ?string $completedAt;
    public readonly ?string $errorMessage;
    /** @var array<string, mixed> */
    public readonly array $result;

    /**
     * @param array<string, mixed> $result
     */
    public function __construct(
        string $analysisId,
        string $status,
        string $url,
        ?string $statusUrl,
        ?int $estimatedTimeSeconds,
        ?string $completedAt,
        ?string $errorMessage,
        array $result,
    ) {
        $this->analysisId = $analysisId;
        $this->status = $status;
        $this->url = $url;
        $this->statusUrl = $statusUrl;
        $this->estimatedTimeSeconds = $estimatedTimeSeconds;
        $this->completedAt = $completedAt;
        $this->errorMessage = $errorMessage;
        $this->result = $result;
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'processing';
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            analysisId: (string) ($data['analysis_id'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            statusUrl: $data['status_url'] ?? null,
            estimatedTimeSeconds: isset($data['estimated_time_seconds']) ? (int) $data['estimated_time_seconds'] : null,
            completedAt: $data['completed_at'] ?? null,
            errorMessage: $data['error_message'] ?? null,
            result: $data['result'] ?? [],
        );
    }
}
