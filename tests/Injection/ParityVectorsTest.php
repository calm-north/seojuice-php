<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Injection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SEOJuice\Injection\SeoInjector;

final class ParityVectorsTest extends TestCase
{
    /**
     * The one vector that cannot byte-match the raw-Worker expected_html once
     * this SDK's documented delta is applied — pinned intentionally by its own
     * "notes" field, not a regression:
     *
     * - brokenlink_legacy_replacement_url_worker_noop: the vector pins the raw
     *   Worker's GAP behavior (new_url-only, no replacement_url fallback). Task 7
     *   deliberately implements the GENERAL-plan delta (new_url ?: replacement_url),
     *   so this SDK correctly APPLIES the fix where the vector expects a no-op.
     *   Covered instead by TransformerTest::testApplyBrokenLinkFixesReplacesViaLegacyReplacementUrlWhenNewUrlEmpty.
     *
     * (failopen_empty_payload_ssr_flag_only now passes by strict equality: C1
     * gates only the content transforms, and the SSR flag is emitted
     * unconditionally — matching the Worker + node/python.)
     *
     * @var array<int, string>
     */
    private const KNOWN_C1_DELTA_MISMATCHES = [
        'brokenlink_legacy_replacement_url_worker_noop',
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
