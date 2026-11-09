<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\AnalysisStatus;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\Enums\ReportType;
use SEOJuice\Exceptions\AuthException;
use SEOJuice\Exceptions\ForbiddenException;
use SEOJuice\Exceptions\NotFoundException;
use SEOJuice\Exceptions\PaymentRequiredException;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Exceptions\ValidationException;
use SEOJuice\HttpClient;
use SEOJuice\Resources\AccessibilityResource;
use SEOJuice\Resources\ActionItemResource;
use SEOJuice\Resources\AisoResource;
use SEOJuice\Resources\AnalysisResource;
use SEOJuice\Resources\BacklinkResource;
use SEOJuice\Resources\BenchmarkResource;
use SEOJuice\Resources\ChangeResource;
use SEOJuice\Resources\ClusterResource;
use SEOJuice\Resources\CompetitorResource;
use SEOJuice\Resources\ContentResource;
use SEOJuice\Resources\DomainHealthResource;
use SEOJuice\Resources\GbpResource;
use SEOJuice\Resources\IntelligenceResource;
use SEOJuice\Resources\KeywordResource;
use SEOJuice\Resources\LinkResource;
use SEOJuice\Resources\PageResource;
use SEOJuice\Resources\ReportResource;
use SEOJuice\Resources\SerpLandscapeResource;
use SEOJuice\Resources\SimilarResource;
use SEOJuice\Resources\UrlResource;
use SEOJuice\Resources\WebsiteResource;
use SEOJuice\Tests\Support\FakeServer;

/**
 * Replays recorded server responses (tests/fixtures/api-fixtures) through the
 * SDK. The request must equal the recorded one, and the returned object must
 * expose every field of the recorded body with the recorded value.
 */
final class FixtureContractTest extends TestCase
{
    private const D = FakeServer::DOMAIN;

    /** Fixtures whose endpoint has no SDK method yet (out of scope this round). */
    private const NO_SDK_METHOD = [
        'create_website', 'create_website_already_exists', 'create_website_missing_domain', 'create_website_plan_limit',
        'find_page', 'get_link_analytics', 'get_link_analytics_stats', 'get_report_summary',
        'get_search_performance', 'get_search_performance_compare', 'get_traffic_analytics',
        'list_ai_blind_spots', 'list_ai_citations', 'list_ai_prompts', 'list_keyword_movers',
        'list_content_pieces', 'get_content_piece', 'error_404_no_ai_monitoring', 'error_404_no_search_console',
    ];

    /** The SDK cannot send this request (invalid JSON body). */
    private const UNSENDABLE = ['error_400_invalid_json'];

    /** Body key holding the list when the SDK returns a bare list/array. */
    private const BODY_SELECTOR = ['list_gbp_locations' => 'results', 'list_websites' => 'results'];

    /** @return array<string, array{0: callable(HttpClient): mixed}> */
    public static function successCases(): array
    {
        $c = [
            'analyze_page' => fn ($h) => (new AnalysisResource($h, self::D))->submit('https://fixtures.example/new-page/'),
            'approve_change' => fn ($h) => (new ChangeResource($h, self::D))->approve(1),
            'approve_heading_confirmed' => fn ($h) => (new ChangeResource($h, self::D))->approve(1, confirmHeading: true),
            'bulk_approve_changes' => fn ($h) => (new ChangeResource($h, self::D))->bulk('approve', [1, 2, 3]),
            'create_action_item' => fn ($h) => (new ActionItemResource($h, self::D))->create(['title' => 'Fix the broken footer links', 'category' => 'seo', 'priority' => 'high']),
            'create_report' => fn ($h) => (new ReportResource($h, self::D))->create(ReportType::LastWeek),
            'get_action_item' => fn ($h) => (new ActionItemResource($h, self::D))->get(1),
            'get_action_item_groups' => fn ($h) => (new ActionItemResource($h, self::D))->groups(),
            'get_action_item_summary' => fn ($h) => (new ActionItemResource($h, self::D))->summary(),
            'get_aiso' => fn ($h) => (new AisoResource($h, self::D))->get(),
            'get_analysis_status' => fn ($h) => (new AnalysisResource($h, self::D))->status('analysis-done'),
            'get_benchmarks' => fn ($h) => (new BenchmarkResource($h, self::D))->get(),
            'get_change' => fn ($h) => (new ChangeResource($h, self::D))->get(1),
            'get_change_live' => fn ($h) => (new ChangeResource($h, self::D))->get(1),
            'get_change_settings' => fn ($h) => (new ChangeResource($h, self::D))->settings(),
            'get_change_stats' => fn ($h) => (new ChangeResource($h, self::D))->stats(),
            'get_cluster' => fn ($h) => (new ClusterResource($h, self::D))->get(1),
            'get_domain_health' => fn ($h) => (new DomainHealthResource($h, self::D))->get(),
            'get_intelligence' => fn ($h) => (new IntelligenceResource($h, self::D))->summary(),
            'get_intelligence_with_history' => fn ($h) => (new IntelligenceResource($h, self::D))->summary(includeHistory: true, includeTrends: true),
            'get_page' => fn ($h) => (new PageResource($h, self::D))->get('1'),
            'get_page_content' => fn ($h) => (new PageResource($h, self::D))->content('1'),
            'get_page_content_quality' => fn ($h) => (new PageResource($h, self::D))->contentQuality('1'),
            'get_page_geo_readiness' => fn ($h) => (new PageResource($h, self::D))->geoReadiness('1'),
            'get_page_metrics_history' => fn ($h) => (new PageResource($h, self::D))->metricsHistory('1', pageSize: 30),
            'get_page_search_stats' => fn ($h) => (new PageResource($h, self::D))->searchStats('1', pageSize: 30),
            'get_pagespeed' => fn ($h) => (new IntelligenceResource($h, self::D))->pageSpeed('https://fixtures.example/guide/'),
            'get_report' => fn ($h) => (new ReportResource($h, self::D))->get(1),
            'get_report_pdf' => fn ($h) => (new ReportResource($h, self::D))->downloadPdf(1),
            'get_serp_landscape' => fn ($h) => (new SerpLandscapeResource($h, self::D))->get(),
            'get_topology' => fn ($h) => (new IntelligenceResource($h, self::D))->topology(),
            'get_url_status' => fn ($h) => (new UrlResource($h, self::D))->status('https://fixtures.example/pushed/'),
            'get_website' => fn ($h) => (new WebsiteResource($h))->get(self::D),
            'get_website_unlocked_on_single_plan' => fn ($h) => (new WebsiteResource($h))->get(self::D),
            'list_accessibility_issues' => fn ($h) => (new AccessibilityResource($h, self::D))->list(pageSize: 20),
            'list_action_items' => fn ($h) => (new ActionItemResource($h, self::D))->list(pageSize: 20),
            'list_backlink_domains' => fn ($h) => (new BacklinkResource($h, self::D))->listDomains(pageSize: 20),
            'list_backlinks' => fn ($h) => (new BacklinkResource($h, self::D))->list(pageSize: 20),
            'list_changes' => fn ($h) => (new ChangeResource($h, self::D))->list(pageSize: 20),
            'list_changes_filtered' => fn ($h) => (new ChangeResource($h, self::D))->list(status: 'pending', changeType: 'meta_description', pageSize: 20),
            'list_clusters' => fn ($h) => (new ClusterResource($h, self::D))->list(pageSize: 20),
            'list_competitors' => fn ($h) => (new CompetitorResource($h, self::D))->list(pageSize: 20),
            'list_competitors_with_trends' => fn ($h) => (new CompetitorResource($h, self::D))->list(includeTrends: true, pageSize: 20),
            'list_content_decay' => fn ($h) => (new ContentResource($h, self::D))->listDecayAlerts(pageSize: 20),
            'list_content_gaps' => fn ($h) => (new ContentResource($h, self::D))->listGaps(pageSize: 20),
            'list_gbp_locations' => fn ($h) => (new GbpResource($h, self::D))->locations(),
            'list_gbp_reviews' => fn ($h) => (new GbpResource($h, self::D))->reviews(pageSize: 20),
            'list_gbp_reviews_needs_attention' => fn ($h) => (new GbpResource($h, self::D))->reviews(needsAttention: true, pageSize: 20),
            'list_keywords' => fn ($h) => (new KeywordResource($h, self::D))->list(pageSize: 20),
            'list_keywords_with_serp' => fn ($h) => (new KeywordResource($h, self::D))->list(includeSerp: true, pageSize: 20),
            'list_links' => fn ($h) => (new LinkResource($h, self::D))->list(),
            'list_page_keywords' => fn ($h) => (new PageResource($h, self::D))->keywords('1', pageSize: 20),
            'list_pages' => fn ($h) => (new PageResource($h, self::D))->list(),
            'list_pages_paginated' => fn ($h) => (new PageResource($h, self::D))->list(page: 2, pageSize: 1),
            'list_reports' => fn ($h) => (new ReportResource($h, self::D))->list(pageSize: 12),
            'list_websites' => fn ($h) => (new WebsiteResource($h))->list(),
            'pull_change' => fn ($h) => (new ChangeResource($h, self::D))->pull(1, 'wordpress'),
            'reject_change' => fn ($h) => (new ChangeResource($h, self::D))->reject(1, 'Does not match our tone'),
            'reply_to_gbp_review' => fn ($h) => (new GbpResource($h, self::D))->replyToReview(1, 'Sorry about that. Please call us.'),
            'revert_change' => fn ($h) => (new ChangeResource($h, self::D))->revert(1, 'Wrong title'),
            'submit_urls' => fn ($h) => (new UrlResource($h, self::D))->submit(['https://fixtures.example/queued/', ['url' => 'https://fixtures.example/pushed/', 'content' => 'Pushed body text', 'title' => 'Pushed page']]),
            'update_action_item_done' => fn ($h) => (new ActionItemResource($h, self::D))->update(1, 'done'),
            'update_change_settings' => fn ($h) => (new ChangeResource($h, self::D))->updateSettings(['meta_tags_mode' => 'auto_deploy', 'max_changes_per_day' => 25, 'exclude_paths' => ['/tag/', '/cart/']]),
            'verify_change' => fn ($h) => (new ChangeResource($h, self::D))->verify(1, 'wordpress'),
        ];

        return array_map(fn ($f) => [$f], $c);
    }

    /**
     * Error fixtures. Where the SDK refuses to build the invalid request, the
     * raw HttpClient sends it so the status-to-exception mapping is still covered.
     *
     * @return array<string, array{0: callable(HttpClient): mixed, 1: class-string<SEOJuiceException>}>
     */
    public static function errorCases(): array
    {
        $raw = fn (string $m, string $path, array $body = []) => fn (HttpClient $h) => $m === 'GET' ? $h->get($path, $body) : $h->{strtolower($m)}($path, $body);
        $d = self::D;

        return [
            'analyze_page_missing_url' => [$raw('POST', "websites/{$d}/analyze/"), ValidationException::class],
            'approve_change_not_proposed' => [fn ($h) => (new ChangeResource($h, $d))->approve(1), ValidationException::class],
            'approve_heading_without_confirmation' => [fn ($h) => (new ChangeResource($h, $d))->approve(1), ValidationException::class],
            'bulk_changes_invalid_action' => [fn ($h) => (new ChangeResource($h, $d))->bulk('explode', [1]), ValidationException::class],
            'create_action_item_missing_title' => [fn ($h) => (new ActionItemResource($h, $d))->create(['category' => 'seo']), ValidationException::class],
            'create_report_invalid_type' => [$raw('POST', "websites/{$d}/reports/", ['type' => 'yearly']), ValidationException::class],
            'error_400_invalid_pagination' => [fn ($h) => (new PageResource($h, $d))->list(page: 0), ValidationException::class],
            'error_400_missing_parameter' => [$raw('GET', "websites/{$d}/pagespeed/"), ValidationException::class],
            'error_401_empty_token' => [fn ($h) => (new WebsiteResource($h))->list(), AuthException::class],
            'error_401_invalid_token' => [fn ($h) => (new WebsiteResource($h))->list(), AuthException::class],
            'error_401_missing_token' => [fn ($h) => (new WebsiteResource($h))->list(), AuthException::class],
            'error_402_trial_required' => [fn ($h) => (new WebsiteResource($h))->get($d), PaymentRequiredException::class],
            'error_402_website_locked' => [fn ($h) => (new WebsiteResource($h))->get($d), PaymentRequiredException::class],
            'error_403_reports_not_in_plan' => [fn ($h) => (new ReportResource($h, $d))->list(pageSize: 12), ForbiddenException::class],
            'error_404_change_not_found' => [fn ($h) => (new ChangeResource($h, $d))->get(1), NotFoundException::class],
            'error_404_cluster_not_found' => [fn ($h) => (new ClusterResource($h, $d))->get(1), NotFoundException::class],
            'error_404_page_not_found' => [fn ($h) => (new PageResource($h, $d))->get('1'), NotFoundException::class],
            'error_404_website_not_found' => [fn ($h) => (new WebsiteResource($h))->get($d), NotFoundException::class],
            'error_404_website_not_found_alt_code' => [fn ($h) => (new IntelligenceResource($h, $d))->summary(), NotFoundException::class],
            'get_analysis_status_not_found' => [fn ($h) => (new AnalysisResource($h, $d))->status('nope'), NotFoundException::class],
            'get_similar_pages' => [fn ($h) => (new SimilarResource($h, $d))->find('https://fixtures.example/guide/'), ValidationException::class],
            'pull_change_without_integration' => [$raw('POST', "websites/{$d}/changes/1/pull/"), ValidationException::class],
            'reject_change_without_reason' => [$raw('POST', "websites/{$d}/changes/1/reject/"), ValidationException::class],
            'reply_to_gbp_review_missing_text' => [$raw('POST', "websites/{$d}/gbp/reviews/1/reply/"), ValidationException::class],
            'submit_urls_empty' => [fn ($h) => (new UrlResource($h, $d))->submit([]), ValidationException::class],
            'update_change_settings_invalid_mode' => [fn ($h) => (new ChangeResource($h, $d))->updateSettings(['meta_tags_mode' => 'sometimes']), ValidationException::class],
        ];
    }

    /** @return array<string, array{0: callable, 1: string}> */
    public static function errorCasesProvider(): array
    {
        $out = [];
        foreach (self::errorCases() as $name => [$call, $class]) {
            $out[$name] = [$call, $class];
        }

        return $out;
    }

    public function testEveryFixtureIsCoveredOrExplicitlyListed(): void
    {
        $index = json_decode((string) file_get_contents(FakeServer::FIXTURE_DIR . '/_index.json'), true);
        $covered = array_merge(
            array_keys(self::successCases()),
            array_keys(self::errorCases()),
            self::NO_SDK_METHOD,
            self::UNSENDABLE,
        );
        $all = array_keys($index['fixtures']);
        sort($all);
        sort($covered);

        $this->assertSame($all, $covered, 'fixture set and covered set differ');
    }

    /**
     * @dataProvider successProvider
     */
    public function testSdkExposesEveryFieldOfTheRecordedResponse(string $fixture): void
    {
        $server = FakeServer::forFixture($fixture);
        $actual = (self::successCases()[$fixture][0])($server->http());

        $this->assertCount(1, $server->requests, 'exactly one request');
        $body = $server->fixture['response']['body'] ?? null;
        if (isset($server->fixture['response']['body_base64'])) {
            $this->assertSame(base64_decode($server->fixture['response']['body_base64']), $actual);

            return;
        }
        if (isset(self::BODY_SELECTOR[$fixture])) {
            $body = $body[self::BODY_SELECTOR[$fixture]];
        }
        $this->assertExposes($body, $actual, $fixture);
    }

    /** @return array<string, array{0: string}> */
    public static function successProvider(): array
    {
        $out = [];
        foreach (array_keys(self::successCases()) as $name) {
            $out[$name] = [$name];
        }

        return $out;
    }

    /**
     * @dataProvider errorProvider
     * @param class-string<SEOJuiceException> $class
     */
    public function testErrorFixtureMapsToTheRightException(string $fixture, string $class): void
    {
        $server = FakeServer::forFixture($fixture);
        $body = $server->fixture['response']['body'];

        try {
            (self::errorCases()[$fixture][0])($server->http());
            $this->fail('no exception thrown');
        } catch (SEOJuiceException $e) {
            $this->assertSame($class, $e::class);
            $this->assertSame($body['error'], $e->errorCode);
            $this->assertSame($body['message'], $e->getMessage());
            if ($e instanceof PaymentRequiredException) {
                $this->assertSame($body['checkout_url'], $e->checkoutUrl);
                $this->assertSame($body['lock_state'], $e->lockState);
            }
        }
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function errorProvider(): array
    {
        $out = [];
        foreach (self::errorCases() as $name => [, $class]) {
            $out[$name] = [$name, $class];
        }

        return $out;
    }

    public function testConflictStatusKeepsTheServerErrorCode(): void
    {
        $server = FakeServer::forFixture('create_website_already_exists');
        try {
            $server->http()->post('websites/', ['domain' => 'taken-client.com']);
            $this->fail('no exception');
        } catch (SEOJuiceException $e) {
            $this->assertSame('already_exists', $e->errorCode);
            $this->assertSame('Website already exists', $e->getMessage());
        }
    }

    /** Fixtures whose lists are empty only prove the pagination envelope parses. */
    public function testEmptyFixturesAreKnown(): void
    {
        $empty = [];
        foreach (array_keys(self::successCases()) as $name) {
            $body = json_decode((string) file_get_contents(FakeServer::FIXTURE_DIR . "/{$name}.json"), true)['response']['body'] ?? null;
            if (is_array($body) && array_key_exists('results', $body) && $body['results'] === []) {
                $empty[] = $name;
            }
        }
        $this->assertSame(['list_backlink_domains'], $empty);
    }

    // ---- exposure assertion -------------------------------------------------

    private function assertExposes(mixed $expected, mixed $actual, string $at): void
    {
        if ($actual instanceof PaginatedResult) {
            $p = $expected['pagination'];
            $this->assertSame($p['page'], $actual->pagination->page, "{$at}.pagination.page");
            $this->assertSame($p['page_size'], $actual->pagination->pageSize, "{$at}.pagination.page_size");
            $this->assertSame($p['total_count'], $actual->pagination->totalCount, "{$at}.pagination.total_count");
            $this->assertSame($p['total_pages'], $actual->pagination->totalPages, "{$at}.pagination.total_pages");
            $this->assertExposes($expected['results'], $actual->results, "{$at}.results");

            return;
        }
        if ($actual instanceof AnalysisStatus) {
            $declared = ['analysis_id', 'status', 'url', 'status_url', 'estimated_time_seconds', 'completed_at', 'error_message'];
            foreach ($expected as $k => $v) {
                if (in_array($k, $declared, true)) {
                    $this->assertScalar($v, $actual->{self::camel($k)}, "{$at}.{$k}");
                } else {
                    $this->assertArrayHasKey($k, $actual->result, "{$at}.result.{$k}");
                    $this->assertEquals($v, $actual->result[$k], "{$at}.result.{$k}");
                }
            }

            return;
        }
        if (is_object($actual)) {
            $this->assertIsArray($expected, $at);
            foreach ($expected as $k => $v) {
                $prop = self::camel((string) $k);
                $this->assertTrue(property_exists($actual, $prop), "{$at}: " . $actual::class . " has no property \${$prop} for field '{$k}'");
                $this->assertExposes($v, $actual->$prop, "{$at}.{$k}");
            }

            return;
        }
        if (is_array($actual) && is_array($expected) && array_is_list($expected) && $expected !== [] && is_object($actual[0] ?? null)) {
            $this->assertCount(count($expected), $actual, $at);
            foreach ($expected as $i => $item) {
                $this->assertExposes($item, $actual[$i], "{$at}[{$i}]");
            }

            return;
        }
        if (is_array($actual) || is_array($expected)) {
            $this->assertEquals($expected, $actual, $at);

            return;
        }
        $this->assertScalar($expected, $actual, $at);
    }

    private function assertScalar(mixed $expected, mixed $actual, string $at): void
    {
        if (is_int($expected) || is_float($expected)) {
            $this->assertTrue(is_int($actual) || is_float($actual), "{$at}: expected number " . json_encode($expected) . ', got ' . json_encode($actual));
            $this->assertEquals($expected, $actual, $at);

            return;
        }
        $this->assertSame($expected, $actual, $at);
    }

    private static function camel(string $key): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
    }
}
