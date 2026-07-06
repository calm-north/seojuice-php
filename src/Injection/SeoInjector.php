<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class SeoInjector
{
    /**
     * Full server-side parity injection, matching the SEOJuice edge Worker's
     * `transformHTML` pipeline and the node/python SDKs.
     *
     * C1 (`validateApiResponse`) gates only the content-mutating transforms — an
     * invalid/actionless payload skips straight to the manifest comment (a no-op
     * when nothing changed) and the SSR flag, which are ALWAYS applied. This
     * matches the Worker's own unconditional SSR-flag behavior; short-circuiting
     * before the flag would diverge from node/python.
     *
     * Fails open: any thrown error, an empty result, a result under half the
     * original length, or a result missing a `<body>` tag returns the original
     * HTML unchanged.
     *
     * @param array<string, mixed> $data
     */
    public function inject(string $html, array $data): string
    {
        $original = $html;
        $manifest = ['cs' => [], 'meta' => [], 'img' => 0, 'schema' => 0, 'h1' => 0];

        try {
            if (Transformer::validateApiResponse($data)) {
                $html = Transformer::replaceMetaTags($html, $data, $manifest);
                $html = Transformer::replaceImages($html, $data, $manifest);
                $html = Transformer::injectInternalLinks($html, $data, $manifest);
                $html = Transformer::applyContentDiffs($html, is_array($data['diffs'] ?? null) ? $data['diffs'] : [], $manifest);
                $html = Transformer::replaceH1($html, $data, $manifest);
                $html = Transformer::applyBrokenLinkFixes($html, is_array($data['broken_link_fixes'] ?? null) ? $data['broken_link_fixes'] : []);
            }

            $html = Transformer::addManifestComment($html, $manifest);
            $html = Transformer::addSsrFlag($html);

            if ($html === '' || strlen($html) < strlen($original) * 0.5 || !preg_match('/<body[\s>]/i', $html)) {
                $html = $original;
            }
        } catch (\Throwable) {
            $html = $original;
        }

        return $html;
    }
}
