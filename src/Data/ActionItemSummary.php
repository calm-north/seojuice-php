<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class ActionItemSummary
{
    public readonly int $total;
    public readonly int $open;
    public readonly int $done;
    public readonly int $snoozed;
    public readonly int $dismissed;
    public readonly int $autoFixed;
    public readonly int $doneThisMonth;
    public readonly float $completionRate;
    /** @var array<string, int> */
    public readonly array $byCategory;
    /** @var array<string, int> */
    public readonly array $byPriority;

    /**
     * @param array<string, int> $byCategory
     * @param array<string, int> $byPriority
     */
    public function __construct(
        int $total,
        int $open,
        int $done,
        int $snoozed,
        int $dismissed,
        int $autoFixed,
        int $doneThisMonth,
        float $completionRate,
        array $byCategory,
        array $byPriority,
    ) {
        $this->total = $total;
        $this->open = $open;
        $this->done = $done;
        $this->snoozed = $snoozed;
        $this->dismissed = $dismissed;
        $this->autoFixed = $autoFixed;
        $this->doneThisMonth = $doneThisMonth;
        $this->completionRate = $completionRate;
        $this->byCategory = $byCategory;
        $this->byPriority = $byPriority;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            total: (int) ($data['total'] ?? 0),
            open: (int) ($data['open'] ?? 0),
            done: (int) ($data['done'] ?? 0),
            snoozed: (int) ($data['snoozed'] ?? 0),
            dismissed: (int) ($data['dismissed'] ?? 0),
            autoFixed: (int) ($data['auto_fixed'] ?? 0),
            doneThisMonth: (int) ($data['done_this_month'] ?? 0),
            completionRate: (float) ($data['completion_rate'] ?? 0.0),
            byCategory: $data['by_category'] ?? [],
            byPriority: $data['by_priority'] ?? [],
        );
    }
}
