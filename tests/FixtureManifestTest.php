<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;

final class FixtureManifestTest extends TestCase
{
    public function testEveryFixtureFileMatchesTheSyncedManifest(): void
    {
        $root = __DIR__ . '/fixtures';
        $listed = [];
        foreach (file($root . '/MANIFEST.sha256', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            [$hash, $path] = preg_split('/\s+/', $line, 2);
            $listed[$path] = $hash;
            $this->assertFileExists("{$root}/{$path}", "listed but missing: {$path}");
            $this->assertSame($hash, hash_file('sha256', "{$root}/{$path}"), "hash mismatch: {$path}");
        }
        $this->assertNotEmpty($listed);

        $onDisk = [];
        foreach (['api-fixtures', 'ssr-parity-vectors'] as $dir) {
            foreach (glob("{$root}/{$dir}/*") ?: [] as $file) {
                $onDisk[] = "{$dir}/" . basename($file);
            }
        }
        $this->assertSame([], array_values(array_diff($onDisk, array_keys($listed))), 'fixture files not in MANIFEST.sha256');
    }
}
