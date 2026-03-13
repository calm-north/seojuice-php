<?php

/**
 * Action Items — Manage SEO action items programmatically.
 *
 * Shows how to list, filter, create, and update action items,
 * plus view summary statistics and group breakdowns.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SEOJuice\AutoPaginator;
use SEOJuice\SEOJuice;

$client = new SEOJuice(getenv('SEOJUICE_API_KEY'));
$domain = 'example.com';

function showSummary(SEOJuice $client, string $domain): void
{
    $summary = $client->actionItems($domain)->summary();

    echo "=== Action Items Summary ===\n";
    echo "  Total: {$summary->total}\n";
    echo "  Open: {$summary->open}\n";
    echo "  Done: {$summary->done}\n";
    echo "  Snoozed: {$summary->snoozed}\n";
    echo "  Dismissed: {$summary->dismissed}\n";
    echo "  Auto-fixed: {$summary->autoFixed}\n";
    echo "  Done this month: {$summary->doneThisMonth}\n";
    echo "  Completion rate: " . number_format($summary->completionRate, 1) . "%\n";
    echo "  By category: " . json_encode($summary->byCategory) . "\n";
    echo "  By priority: " . json_encode($summary->byPriority) . "\n";
}

function listOpenItems(SEOJuice $client, string $domain): void
{
    echo "\n=== Open Action Items ===\n";

    foreach (AutoPaginator::paginate(
        fn (int $page, int $pageSize) => $client->actionItems($domain)->list(
            status: 'open',
            page: $page,
            pageSize: $pageSize,
        ),
    ) as $item) {
        $priority = $item->priority ?? 'unknown';
        echo "  [{$priority}] #{$item->id}: {$item->title}\n";
        if ($item->guidance !== null) {
            echo "         Guidance: " . substr($item->guidance, 0, 100) . "\n";
        }
        if ($item->estimatedEffort !== null) {
            echo "         Effort: {$item->estimatedEffort}\n";
        }
    }
}

function showGroups(SEOJuice $client, string $domain): void
{
    echo "\n=== Action Items by Category ===\n";

    $result = $client->actionItems($domain)->groups();
    foreach ($result->results as $group) {
        echo "  {$group->category}: {$group->count} items\n";
        echo "    Priority distribution: " . json_encode($group->priorityDistribution) . "\n";
    }
}

function createCustomItem(SEOJuice $client, string $domain): void
{
    $item = $client->actionItems($domain)->create([
        'title' => 'Review and update cornerstone content',
        'description' => "Our top 5 cornerstone pages haven't been refreshed in 6 months",
        'category' => 'content',
        'priority' => 'high',
        'estimated_effort' => '4h',
    ]);
    echo "\nCreated action item #{$item->id}: {$item->title}\n";
}

function completeItem(SEOJuice $client, string $domain, int $itemId): void
{
    $updated = $client->actionItems($domain)->update($itemId, 'done');
    echo "Completed #{$updated->id}: {$updated->title}\n";
}

function snoozeItem(SEOJuice $client, string $domain, int $itemId, int $days = 7): void
{
    $updated = $client->actionItems($domain)->update($itemId, 'snooze', $days);
    echo "Snoozed #{$updated->id} for {$days} days\n";
}

// --- Full workflow ---

showSummary($client, $domain);
listOpenItems($client, $domain);
showGroups($client, $domain);
createCustomItem($client, $domain);
// completeItem($client, $domain, $itemId);
// snoozeItem($client, $domain, $itemId, 14);
