<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class SeoInjector
{
    /**
     * @param array<string, mixed> $data
     */
    public function inject(string $html, array $data): string
    {
        if (!Transformer::validateApiResponse($data)) {
            return $html;
        }

        $original = $html;
        $manifest = ['cs' => [], 'meta' => [], 'img' => 0, 'schema' => 0, 'h1' => 0];

        try {
            $html = Transformer::replaceMetaTags($html, $data, $manifest);
            $html = Transformer::replaceImages($html, $data, $manifest);
            $html = Transformer::injectInternalLinks($html, $data, $manifest);
            $html = Transformer::applyContentDiffs($html, is_array($data['diffs'] ?? null) ? $data['diffs'] : [], $manifest);
            $html = Transformer::replaceH1($html, $data, $manifest);
            $html = Transformer::applyBrokenLinkFixes($html, is_array($data['broken_link_fixes'] ?? null) ? $data['broken_link_fixes'] : []);
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
