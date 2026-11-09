<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use SEOJuice\Config;
use SEOJuice\Data\AnalysisStatus;
use SEOJuice\Data\ChangeRecord;
use SEOJuice\Data\ReportDetail;
use SEOJuice\Enums\ChangeStatus;
use SEOJuice\Enums\ChangeType;
use SEOJuice\Exceptions\ValidationException;
use SEOJuice\Resources\ActionItemResource;
use SEOJuice\Resources\ChangeResource;
use SEOJuice\Tests\Support\FakeServer;

/**
 * Expected values come from the server code, cited per test.
 */
final class ServerContractCallsTest extends TestCase
{
    private function change(string $status = 'proposed'): array
    {
        return ['id' => 7, 'change_type' => 'title_tag', 'status' => $status];
    }

    /** seojuice/partners/views.py ChangeRejectView: 422 missing_field without a non-empty reason. */
    public function testRejectSendsTheReasonTheServerRequires(): void
    {
        $server = new FakeServer([
            'POST /websites/example.com/changes/7/reject/' => function ($body) {
                $reason = trim((string) ($body->reason ?? ''));
                if ($reason === '') {
                    return [422, ['error' => ['code' => 'missing_field', 'message' => 'reason is required when rejecting a change']]];
                }

                return [200, $this->change('rejected')];
            },
        ]);
        $changes = new ChangeResource($server->http(), 'example.com');

        $record = $changes->reject(7, 'Off brand');

        $this->assertSame('Off brand', $server->requests[0]['body']->reason);
        $this->assertSame('rejected', $record->status);
    }

    public function testRejectWithoutReasonFailsBeforeAnyRequest(): void
    {
        $server = new FakeServer([]);
        $changes = new ChangeResource($server->http(), 'example.com');

        $this->expectException(ValidationException::class);
        try {
            $changes->reject(7, '  ');
        } finally {
            $this->assertSame([], $server->requests);
        }
    }

    /** ChangeApproveView: heading changes need body.confirm_heading === true (JSON boolean). */
    public function testApproveSendsConfirmHeadingAsBooleanTrue(): void
    {
        $server = new FakeServer([
            'POST /websites/example.com/changes/7/approve/' => fn ($body) => [200, $this->change('approved')],
        ]);
        $changes = new ChangeResource($server->http(), 'example.com');

        $changes->approve(7, confirmHeading: true);
        $changes->approve(7);

        $this->assertTrue($server->requests[0]['body']->confirm_heading);
        $this->assertNull($server->requests[1]['body']?->confirm_heading ?? null);
    }

    /** ChangesBulkView: "ids or batch_id is required"; batch_id alone is valid. */
    public function testBulkAcceptsBatchIdWithoutIds(): void
    {
        $batch = '6f1c2f0e-2f4e-4c8e-9a53-0b3f2f6f7a11';
        $server = new FakeServer([
            'POST /websites/example.com/changes/bulk/' => function ($body) {
                if (empty($body->batch_id) && empty($body->ids)) {
                    return [422, ['error' => ['code' => 'missing_field', 'message' => 'ids or batch_id is required']]];
                }

                return [200, ['total_requested' => 1, 'total_succeeded' => 1, 'total_failed' => 0, 'succeeded' => [7], 'failed' => []]];
            },
        ]);
        $changes = new ChangeResource($server->http(), 'example.com');

        $changes->bulk('approve', batchId: $batch);

        $this->assertSame($batch, $server->requests[0]['body']->batch_id);
        $this->assertObjectNotHasProperty('ids', $server->requests[0]['body']);
    }

    public function testBulkWithNeitherIdsNorBatchFailsBeforeAnyRequest(): void
    {
        $server = new FakeServer([]);
        $this->expectException(ValidationException::class);
        (new ChangeResource($server->http(), 'example.com'))->bulk('approve');
    }

    /** ChangesListView reads request.GET["batch_id"]. */
    public function testListSendsBatchIdFilter(): void
    {
        $batch = '6f1c2f0e-2f4e-4c8e-9a53-0b3f2f6f7a11';
        $server = new FakeServer([
            'GET /websites/example.com/changes/' => fn ($b, $q) => [200, [
                'pagination' => ['page' => 1, 'page_size' => 10, 'total_count' => 0, 'total_pages' => 0],
                'results' => [],
            ]],
        ]);
        (new ChangeResource($server->http(), 'example.com'))->list(batchId: $batch);

        $this->assertSame($batch, $server->requests[0]['query']['batch_id']);
    }

    /** seojuice/delivery/models.py LifecycleState (171-181). */
    public function testEveryServerLifecycleStateIsAKnownChangeStatus(): void
    {
        foreach (['proposed', 'approved', 'live', 'rolled_back', 'rejected', 'superseded', 'stale', 'expired'] as $state) {
            $this->assertNotNull(ChangeStatus::tryFrom($state), $state);
            $this->assertSame($state, ChangeRecord::fromArray($this->change($state))->statusEnum()?->value);
        }
    }

    /** seojuice/actionitems/constants.py MUTATION_FIELD_TO_LEGACY_CHANGE_TYPE values. */
    public function testEveryServerChangeTypeIsAKnownChangeType(): void
    {
        $serverTypes = [
            'title_tag', 'h1_tag', 'meta_description', 'meta_keywords', 'og_title', 'og_description',
            'og_image', 'internal_link', 'local_schema', 'nap_fix', 'structured_data', 'image_alt',
            'accessibility', 'broken_link_fix', 'content_patch',
        ];
        foreach ($serverTypes as $type) {
            $this->assertNotNull(ChangeType::tryFrom($type), $type);
        }
    }

    public function testUnknownFutureStatusAndTypeDoNotThrow(): void
    {
        $record = ChangeRecord::fromArray(['id' => 1, 'change_type' => 'future_type', 'status' => 'future_state']);

        $this->assertNull($record->statusEnum());
        $this->assertNull($record->changeTypeEnum());
        $this->assertSame('future_state', $record->status);
    }

    /** actionitems/api_views.py ActionItemDetailView.patch: done, in_progress, dismiss, reopen only. */
    public function testActionItemUpdateAcceptsOnlyServerActions(): void
    {
        $server = new FakeServer([
            'PATCH /websites/example.com/action-items/3/' => function ($body) {
                if (!in_array($body->action ?? null, ['done', 'in_progress', 'dismiss', 'reopen'], true)) {
                    return [400, ['error' => ['code' => 'validation_error', 'message' => 'Invalid action']]];
                }

                return [200, ['id' => 3, 'title' => 't', 'status' => 'done']];
            },
        ]);
        $items = new ActionItemResource($server->http(), 'example.com');

        foreach (['done', 'in_progress', 'dismiss', 'reopen'] as $action) {
            $items->update(3, $action);
        }
        $this->assertCount(4, $server->requests);

        $this->expectException(ValidationException::class);
        try {
            $items->update(3, 'snooze');
        } finally {
            $this->assertCount(4, $server->requests);
        }
    }

    /** websites/models.py PageAnalysisRequest.Status: queued, in_progress, completed, failed. */
    public function testAnalysisIsPendingForQueuedAndInProgress(): void
    {
        foreach (['queued', 'in_progress'] as $status) {
            $this->assertTrue(AnalysisStatus::fromArray(['analysis_id' => 'a', 'status' => $status, 'url' => 'u'])->isPending(), $status);
        }
        foreach (['completed', 'failed'] as $status) {
            $this->assertFalse(AnalysisStatus::fromArray(['analysis_id' => 'a', 'status' => $status, 'url' => 'u'])->isPending(), $status);
        }
    }

    /** partners/views.py AnalyzeStatusView:1535 merges the stored result into the top level. */
    public function testAnalysisResultIsReadFromTopLevelFields(): void
    {
        $status = AnalysisStatus::fromArray([
            'analysis_id' => 'abc', 'status' => 'completed', 'url' => 'https://e.com/a',
            'page_id' => 3, 'seo_score' => 71.0, 'is_orphan' => false,
            'completed_at' => '2026-11-01T00:00:00+00:00',
        ]);

        $this->assertSame(3, $status->result['page_id']);
        $this->assertSame(71.0, $status->result['seo_score']);
        $this->assertArrayNotHasKey('status', $status->result);
        $this->assertSame('2026-11-01T00:00:00+00:00', $status->completedAt);
    }

    /** websites/models.py:5730 summary = TextField. */
    public function testReportDetailAcceptsStringSummary(): void
    {
        $detail = ReportDetail::fromArray([
            'id' => 1, 'type' => 'weekly', 'type_display' => 'Weekly', 'status' => 'completed',
            'summary' => 'Traffic grew 12%.', 'data' => null,
        ]);

        $this->assertSame('Traffic grew 12%.', $detail->summary);
    }

    public function testDefaultUserAgentFollowsTheReleasedVersion(): void
    {
        preg_match('/^## (\d+\.\d+\.\d+)/m', (string) file_get_contents(__DIR__ . '/../CHANGELOG.md'), $m);

        $this->assertSame($m[1], Config::VERSION);
        $this->assertSame('seojuice-php/' . $m[1], (new Config())->userAgent);
        $this->assertSame('mine/2', (new Config(userAgent: 'mine/2'))->userAgent);
    }

    /** partners/views.py ChangesListView legacy_to_lifecycle: only legacy names filter; others give none(). */
    public function testListMapsLifecycleStatusToTheLegacyFilterName(): void
    {
        $page = ['pagination' => ['page' => 1, 'page_size' => 10, 'total_count' => 0, 'total_pages' => 0], 'results' => []];
        $server = new FakeServer(['GET /websites/example.com/changes/' => fn ($b, $q) => [200, $page]]);
        $changes = new ChangeResource($server->http(), 'example.com');

        $expected = ['proposed' => 'pending', 'live' => 'applied', 'rolled_back' => 'reverted', 'approved' => 'approved', 'rejected' => 'rejected', 'expired' => 'expired', 'pending' => 'pending'];
        foreach ($expected as $given => $sent) {
            $changes->list(status: $given);
        }
        $this->assertSame(array_values($expected), array_map(fn ($r) => $r['query']['status'], $server->requests));
    }
}
