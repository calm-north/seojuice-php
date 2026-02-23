<?php

declare(strict_types=1);

namespace SEOJuice\Data;

final class MetricsHistory
{
    public readonly ?string $createdAt;
    public readonly ?float $seoScore;
    public readonly ?float $onpageScore;
    public readonly ?float $accessibilityScore;
    public readonly ?int $wordCount;
    public readonly ?int $gscClicks;
    public readonly ?int $gscImpressions;
    public readonly ?float $gscAvgPosition;
    public readonly ?float $gscCtr;
    public readonly bool $isOrphan;
    public readonly ?int $totalIncomingLinks;
    public readonly ?int $totalOutgoingLinks;
    public readonly ?float $cwvLcp;
    public readonly ?float $cwvCls;
    public readonly ?float $cwvFid;
    public readonly ?float $cwvPerformanceScore;

    public function __construct(
        ?string $createdAt,
        ?float $seoScore,
        ?float $onpageScore,
        ?float $accessibilityScore,
        ?int $wordCount,
        ?int $gscClicks,
        ?int $gscImpressions,
        ?float $gscAvgPosition,
        ?float $gscCtr,
        bool $isOrphan,
        ?int $totalIncomingLinks,
        ?int $totalOutgoingLinks,
        ?float $cwvLcp,
        ?float $cwvCls,
        ?float $cwvFid,
        ?float $cwvPerformanceScore,
    ) {
        $this->createdAt = $createdAt;
        $this->seoScore = $seoScore;
        $this->onpageScore = $onpageScore;
        $this->accessibilityScore = $accessibilityScore;
        $this->wordCount = $wordCount;
        $this->gscClicks = $gscClicks;
        $this->gscImpressions = $gscImpressions;
        $this->gscAvgPosition = $gscAvgPosition;
        $this->gscCtr = $gscCtr;
        $this->isOrphan = $isOrphan;
        $this->totalIncomingLinks = $totalIncomingLinks;
        $this->totalOutgoingLinks = $totalOutgoingLinks;
        $this->cwvLcp = $cwvLcp;
        $this->cwvCls = $cwvCls;
        $this->cwvFid = $cwvFid;
        $this->cwvPerformanceScore = $cwvPerformanceScore;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            createdAt: $data['created_at'] ?? null,
            seoScore: isset($data['seo_score']) ? (float) $data['seo_score'] : null,
            onpageScore: isset($data['onpage_score']) ? (float) $data['onpage_score'] : null,
            accessibilityScore: isset($data['accessibility_score']) ? (float) $data['accessibility_score'] : null,
            wordCount: isset($data['word_count']) ? (int) $data['word_count'] : null,
            gscClicks: isset($data['gsc_clicks']) ? (int) $data['gsc_clicks'] : null,
            gscImpressions: isset($data['gsc_impressions']) ? (int) $data['gsc_impressions'] : null,
            gscAvgPosition: isset($data['gsc_avg_position']) ? (float) $data['gsc_avg_position'] : null,
            gscCtr: isset($data['gsc_ctr']) ? (float) $data['gsc_ctr'] : null,
            isOrphan: (bool) ($data['is_orphan'] ?? false),
            totalIncomingLinks: isset($data['total_incoming_links']) ? (int) $data['total_incoming_links'] : null,
            totalOutgoingLinks: isset($data['total_outgoing_links']) ? (int) $data['total_outgoing_links'] : null,
            cwvLcp: isset($data['cwv_lcp']) ? (float) $data['cwv_lcp'] : null,
            cwvCls: isset($data['cwv_cls']) ? (float) $data['cwv_cls'] : null,
            cwvFid: isset($data['cwv_fid']) ? (float) $data['cwv_fid'] : null,
            cwvPerformanceScore: isset($data['cwv_performance_score']) ? (float) $data['cwv_performance_score'] : null,
        );
    }
}
