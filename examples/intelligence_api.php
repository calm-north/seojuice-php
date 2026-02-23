<?php

/**
 * Intelligence API — full workflow examples.
 *
 * Demonstrates the resource-oriented client for SEO analytics:
 * overview, content gaps, decay alerts, topology, PageSpeed, and accessibility.
 *
 * Requirements:
 *     composer require seojuice/seojuice
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use SEOJuice\Enums\Period;
use SEOJuice\SEOJuice;

function getSeoOverview(string $domain): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $summary = $client->intelligence($domain)->summary(
        period: Period::ThirtyDays,
        includeTrends: true,
    );

    echo "Domain: {$domain}\n";
    echo "  SEO Score : {$summary->seoScore}\n";
    echo "  AISO Score: {$summary->aisoScore}\n";
    echo "  Pages     : {$summary->totalPages}\n";

    if ($summary->trends !== null) {
        echo "  Trends    : " . json_encode($summary->trends) . "\n";
    }

    $aiso = $client->aiso($domain)->get(Period::ThirtyDays);
    echo "  AISO Total Mentions: {$aiso->totalMentions}\n";
}

function findContentGaps(string $domain): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $result = $client->content($domain)->listGaps(intent: 'informational');

    echo "\nContent gaps for {$domain}:\n";
    foreach ($result->results as $gap) {
        echo "  {$gap->pageName} — potential: {$gap->seoPotential}\n";
    }
}

function findDecayingContent(string $domain): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $result = $client->content($domain)->listDecayAlerts(
        isActive: true,
        severity: 'high',
    );

    echo "\nHigh-severity decay alerts for {$domain}:\n";
    foreach ($result->results as $alert) {
        echo "  {$alert->pageUrl} — type: {$alert->decayType}\n";
    }
}

function checkTopology(string $domain): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $topology = $client->intelligence($domain)->topology();

    echo "\nTopology for {$domain}:\n";
    echo "  Total pages     : {$topology->totalPages}\n";
    echo "  Avg links/page  : {$topology->avgLinksPerPage}\n";
    echo "  Orphan pages    : {$topology->orphanPagesCount}\n";
}

function checkPagespeed(string $domain, string $url): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $speed = $client->intelligence($domain)->pageSpeed($url);

    echo "\nPageSpeed for {$url}:\n";
    echo "  Loading time: {$speed->loadingTime}ms\n";
    echo "  Scores      : " . json_encode($speed->scores) . "\n";
}

function checkAccessibility(string $domain): void
{
    $client = new SEOJuice(getenv('SEOJUICE_API_KEY'));

    $result = $client->accessibility($domain)->list(severity: 'critical');

    echo "\nCritical accessibility issues for {$domain}:\n";
    foreach ($result->results as $issue) {
        echo "  [{$issue->category}] {$issue->description} (WCAG {$issue->wcagCriterion})\n";
    }
}

function main(): void
{
    $domain = 'example.com';
    $url = "https://{$domain}/blog/seo-guide";

    getSeoOverview($domain);
    findContentGaps($domain);
    findDecayingContent($domain);
    checkTopology($domain);
    checkPagespeed($domain, $url);
    checkAccessibility($domain);
}

main();
