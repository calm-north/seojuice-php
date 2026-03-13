<?php

/**
 * Change Management — Programmatic review and automation workflows.
 *
 * Shows how to triage changes by type, bulk-approve changes,
 * review and reject specific ones, configure automation settings,
 * and revert changes that caused problems.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SEOJuice\AutoPaginator;
use SEOJuice\Data\ChangeRecord;
use SEOJuice\Data\ChangeSettings;
use SEOJuice\Enums\ChangeStatus;
use SEOJuice\SEOJuice;

$client = new SEOJuice(getenv('SEOJUICE_API_KEY'));
$domain = 'example.com';

// Change types that are generally safe to auto-approve
const AUTO_APPROVE_TYPES = [
    'meta_description',
    'og_title',
    'og_description',
    'og_image',
    'image_alt',
    'structured_data',
];

function printStats(SEOJuice $client, string $domain): void
{
    $stats = $client->changes($domain)->stats();

    echo "=== Change Stats ===\n";
    echo "By status: " . json_encode($stats->byStatus) . "\n";
    echo "By type: " . json_encode($stats->byType) . "\n";

    $pending = $stats->byStatus[ChangeStatus::Pending->value] ?? 0;
    $applied = $stats->byStatus[ChangeStatus::Applied->value] ?? 0;
    echo "Pending review: {$pending}, Live on site: {$applied}\n";
}

/**
 * @return array{autoApprovable: ChangeRecord[], needsReview: ChangeRecord[]}
 */
function triagePendingChanges(SEOJuice $client, string $domain): array
{
    $triage = ['autoApprovable' => [], 'needsReview' => []];

    foreach (AutoPaginator::paginate(
        fn (int $page, int $pageSize) => $client->changes($domain)->list(
            status: ChangeStatus::Pending->value,
            page: $page,
            pageSize: $pageSize,
        ),
    ) as $change) {
        if (in_array($change->changeType, AUTO_APPROVE_TYPES, true)) {
            $triage['autoApprovable'][] = $change;
        } else {
            $triage['needsReview'][] = $change;
        }
    }

    $autoCount = count($triage['autoApprovable']);
    $reviewCount = count($triage['needsReview']);
    echo "\nTriaged: {$autoCount} auto-approvable, {$reviewCount} need review\n";

    return $triage;
}

/**
 * @param ChangeRecord[] $changes
 */
function bulkApprove(SEOJuice $client, string $domain, array $changes, string $label): void
{
    if ($changes === []) {
        return;
    }

    $ids = array_map(fn (ChangeRecord $c) => $c->id, $changes);
    $result = $client->changes($domain)->bulk('approve', $ids);

    $total = count($ids);
    $msg = "[{$label}] Approved {$result->totalSucceeded}/{$total}";
    if ($result->totalFailed > 0) {
        $msg .= " ({$result->totalFailed} failed)";
    }
    echo $msg . "\n";

    foreach ($result->failed as $failure) {
        echo "  Change #{$failure['id']}: {$failure['error']}\n";
    }
}

/**
 * @param ChangeRecord[] $changes
 */
function reviewChanges(SEOJuice $client, string $domain, array $changes): void
{
    foreach ($changes as $change) {
        echo "\n--- Change #{$change->id} ---\n";
        echo "  Type: {$change->changeType}\n";
        echo "  Page: {$change->pageUrl}\n";
        echo "  Confidence: {$change->confidenceScore}\n";
        echo "  Reason: {$change->reason}\n";
        echo "  Current: " . truncate($change->previousValue, 80) . "\n";
        echo "  Proposed: " . truncate($change->proposedValue, 80) . "\n";

        if ($change->potentialRisks !== []) {
            echo "  Risks: " . json_encode($change->potentialRisks) . "\n";
        }

        // Reject title tag changes with low confidence
        if ($change->changeType === 'title_tag' && ($change->confidenceScore ?? 0) < 0.7) {
            $client->changes($domain)->reject($change->id, 'Low confidence title change — needs manual review');
            echo "  -> Rejected (low confidence title)\n";
        }
    }
}

function configureAutomation(SEOJuice $client, string $domain): void
{
    $current = $client->changes($domain)->settings();
    echo "\n=== Current Automation Settings ===\n";
    printSettings($current);

    $updated = $client->changes($domain)->updateSettings([
        'meta_tags_mode' => 'suggest',
        'title_tags_mode' => 'suggest',
        'max_changes_per_day' => 50,
        'max_changes_per_page_per_day' => 5,
    ]);

    echo "\n=== Updated Settings ===\n";
    printSettings($updated);
}

function printSettings(ChangeSettings $settings): void
{
    echo "  Internal links: {$settings->internalLinksMode}\n";
    echo "  Meta tags: {$settings->metaTagsMode}\n";
    echo "  OG tags: {$settings->ogTagsMode}\n";
    echo "  Title tags: {$settings->titleTagsMode}\n";
    echo "  Structured data: {$settings->structuredDataMode}\n";
    echo "  Image alt: {$settings->imageAltMode}\n";
    echo "  Accessibility: {$settings->accessibilityMode}\n";
    echo "  Local SEO: {$settings->localSeoMode}\n";
    echo "  Daily limit: {$settings->maxChangesPerDay}\n";
    echo "  Per-page limit: {$settings->maxChangesPerPagePerDay}\n";
    if ($settings->excludePaths !== '') {
        echo "  Excluded paths: {$settings->excludePaths}\n";
    }
}

function monitorVelocity(SEOJuice $client, string $domain): void
{
    $stats = $client->changes($domain)->stats();

    $pending = $stats->byStatus[ChangeStatus::Pending->value] ?? 0;
    $applied = $stats->byStatus[ChangeStatus::Applied->value] ?? 0;
    $rejected = $stats->byStatus[ChangeStatus::Rejected->value] ?? 0;

    if ($pending > 100) {
        echo "[alert] {$pending} pending changes — review queue is growing\n";
    }

    $total = $applied + $rejected;
    if ($total > 0) {
        $approvalRate = number_format(($applied / $total) * 100, 1);
        echo "[velocity] Approval rate: {$approvalRate}%\n";
    }
}

function truncate(?string $value, int $max): string
{
    if ($value === null) {
        return '(empty)';
    }

    return strlen($value) > $max ? substr($value, 0, $max) . '...' : $value;
}

// --- Full workflow ---

printStats($client, $domain);
$triage = triagePendingChanges($client, $domain);
bulkApprove($client, $domain, $triage['autoApprovable'], 'auto-approvable');
reviewChanges($client, $domain, $triage['needsReview']);
configureAutomation($client, $domain);
// revertChange: $client->changes($domain)->revert(12345, 'Caused 404 on /pricing');
monitorVelocity($client, $domain);
