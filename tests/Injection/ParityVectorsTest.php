<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\SeoInjector;

final class ParityVectorsTest extends TestCase
{
    /**
     * Vectors that cannot byte-match the raw-Worker expected_html once this SDK's
     * documented deltas are applied. Both are pinned intentionally by their own
     * "notes" field — this is not a regression:
     *
     * - brokenlink_legacy_replacement_url_worker_noop: the vector pins the raw
     *   Worker's GAP behavior (new_url-only, no replacement_url fallback). Task 7
     *   deliberately implements the GENERAL-plan delta (new_url ?: replacement_url),
     *   so this SDK correctly APPLIES the fix where the vector expects a no-op.
     *   Covered instead by TransformerTest::testApplyBrokenLinkFixesReplacesViaLegacyReplacementUrlWhenNewUrlEmpty.
     * - failopen_empty_payload_ssr_flag_only: an all-empty-but-error-free payload.
     *   The raw Worker has no C1 concept and always appends the SSR flag; this SDK's
     *   C1 gate (required by the GENERAL plan, ported from WP validate_api_response)
     *   correctly treats "nothing actionable" as reject-and-return-original before
     *   the SSR flag step ever runs. C1 and this pre-C1 vector are structurally
     *   incompatible for this one payload shape.
     *
     * @var array<int, string>
     */
    private const KNOWN_C1_DELTA_MISMATCHES = [
        'brokenlink_legacy_replacement_url_worker_noop',
        'failopen_empty_payload_ssr_flag_only',
    ];

    /**
     * @return array<string, array{0: array<string, mixed>}>
     */
    public static function vectors(): array
    {
        $dir = __DIR__ . '/../fixtures/ssr-parity-vectors';
        $out = [];

        foreach (glob("{$dir}/*.json") as $file) {
            $vector = json_decode((string) file_get_contents($file), true);
            $out[$vector['name']] = [$vector];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $vector
     */
    #[DataProvider('vectors')]
    public function testMatchesWorkerVector(array $vector): void
    {
        if (in_array($vector['name'], self::KNOWN_C1_DELTA_MISMATCHES, true)) {
            $this->markTestSkipped('Documented SDK delta vs. raw-Worker vector — see KNOWN_C1_DELTA_MISMATCHES.');
        }

        $normalize = static fn (string $html): string => trim((string) preg_replace('/\s+/', ' ', $html));

        $actual = (new SeoInjector())->inject($vector['input_html'], $vector['payload']);

        $this->assertSame($normalize($vector['expected_html']), $normalize($actual), $vector['name']);
    }
}
