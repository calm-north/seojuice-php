<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class BulkActionResult
{
    public readonly string $action;
    /** @var array<int, int> */
    public readonly array $succeeded;
    /** @var array<int, array{id: int, error: string}> */
    public readonly array $failed;
    public readonly int $totalSucceeded;
    public readonly int $totalFailed;

    /**
     * @param array<int, int> $succeeded
     * @param array<int, array{id: int, error: string}> $failed
     */
    public function __construct(
        string $action,
        array $succeeded,
        array $failed,
        int $totalSucceeded,
        int $totalFailed,
    ) {
        $this->action = $action;
        $this->succeeded = $succeeded;
        $this->failed = $failed;
        $this->totalSucceeded = $totalSucceeded;
        $this->totalFailed = $totalFailed;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            action: (string) ($data['action'] ?? ''),
            succeeded: $data['succeeded'] ?? [],
            failed: $data['failed'] ?? [],
            totalSucceeded: (int) ($data['total_succeeded'] ?? 0),
            totalFailed: (int) ($data['total_failed'] ?? 0),
        );
    }
}
