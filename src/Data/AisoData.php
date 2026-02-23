<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class AisoData
{
    public readonly float $aisoScore;
    /** @var array<string, mixed> */
    public readonly array $subScores;
    public readonly int $totalMentions;
    public readonly int $yourMentions;
    public readonly float $avgPosition;
    public readonly float $positiveRate;
    /** @var array<int, mixed> */
    public readonly array $providers;
    /** @var array<int, mixed>|null */
    public readonly ?array $history;

    /**
     * @param array<string, mixed> $subScores
     * @param array<int, mixed> $providers
     * @param array<int, mixed>|null $history
     */
    public function __construct(
        float $aisoScore,
        array $subScores,
        int $totalMentions,
        int $yourMentions,
        float $avgPosition,
        float $positiveRate,
        array $providers,
        ?array $history,
    ) {
        $this->aisoScore = $aisoScore;
        $this->subScores = $subScores;
        $this->totalMentions = $totalMentions;
        $this->yourMentions = $yourMentions;
        $this->avgPosition = $avgPosition;
        $this->positiveRate = $positiveRate;
        $this->providers = $providers;
        $this->history = $history;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            aisoScore: (float) ($data['aiso_score'] ?? 0),
            subScores: $data['sub_scores'] ?? [],
            totalMentions: (int) ($data['total_mentions'] ?? 0),
            yourMentions: (int) ($data['your_mentions'] ?? 0),
            avgPosition: (float) ($data['avg_position'] ?? 0),
            positiveRate: (float) ($data['positive_rate'] ?? 0),
            providers: $data['providers'] ?? [],
            history: $data['history'] ?? null,
        );
    }
}
