<?php

declare(strict_types=1);

namespace SEOJuice\Injection;

final class SeoInjector
{
    public function inject(string $html, Suggestions $suggestions): string
    {
        if ($suggestions->isEmpty()) {
            return $html;
        }

        $html = $this->injectMetaTags($html, $suggestions->metaTags);
        $html = $this->injectOgTags($html, $suggestions->ogTags);
        $html = $this->injectStructuredData($html, $suggestions->structuredData);
        $html = $this->applyImageAlts($html, $suggestions->images);

        return $html;
    }

    /**
     * @param array<string, mixed> $metaTags
     */
    private function injectMetaTags(string $html, array $metaTags): string
    {
        if ($metaTags === []) {
            return $html;
        }

        $tags = '';

        if (isset($metaTags['title']) && $metaTags['title'] !== '') {
            $escaped = htmlspecialchars((string) $metaTags['title'], ENT_QUOTES, 'UTF-8');
            $tags .= "<title>{$escaped}</title>\n";
        }

        if (isset($metaTags['description']) && $metaTags['description'] !== '') {
            $escaped = htmlspecialchars((string) $metaTags['description'], ENT_QUOTES, 'UTF-8');
            $tags .= "<meta name=\"description\" content=\"{$escaped}\">\n";
        }

        if (isset($metaTags['canonical']) && $metaTags['canonical'] !== '') {
            $escaped = htmlspecialchars((string) $metaTags['canonical'], ENT_QUOTES, 'UTF-8');
            $tags .= "<link rel=\"canonical\" href=\"{$escaped}\">\n";
        }

        if (isset($metaTags['robots']) && $metaTags['robots'] !== '') {
            $escaped = htmlspecialchars((string) $metaTags['robots'], ENT_QUOTES, 'UTF-8');
            $tags .= "<meta name=\"robots\" content=\"{$escaped}\">\n";
        }

        if ($tags === '') {
            return $html;
        }

        return (string) preg_replace('/<\/head>/i', $tags . '</head>', $html, 1);
    }

    /**
     * @param array<string, mixed> $ogTags
     */
    private function injectOgTags(string $html, array $ogTags): string
    {
        if ($ogTags === []) {
            return $html;
        }

        $tags = '';

        foreach ($ogTags as $property => $content) {
            if ($content === null || $content === '') {
                continue;
            }
            $escapedProperty = htmlspecialchars((string) $property, ENT_QUOTES, 'UTF-8');
            $escapedContent = htmlspecialchars((string) $content, ENT_QUOTES, 'UTF-8');
            $tags .= "<meta property=\"og:{$escapedProperty}\" content=\"{$escapedContent}\">\n";
        }

        if ($tags === '') {
            return $html;
        }

        return (string) preg_replace('/<\/head>/i', $tags . '</head>', $html, 1);
    }

    /**
     * @param array<int, mixed> $structuredData
     */
    private function injectStructuredData(string $html, array $structuredData): string
    {
        if ($structuredData === []) {
            return $html;
        }

        $tags = '';

        foreach ($structuredData as $schema) {
            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                continue;
            }
            $tags .= "<script type=\"application/ld+json\">{$json}</script>\n";
        }

        if ($tags === '') {
            return $html;
        }

        return (string) preg_replace('/<\/head>/i', $tags . '</head>', $html, 1);
    }

    /**
     * @param array<int, mixed> $images
     */
    private function applyImageAlts(string $html, array $images): string
    {
        if ($images === []) {
            return $html;
        }

        foreach ($images as $image) {
            $src = $image['src'] ?? null;
            $alt = $image['alt'] ?? null;

            if ($src === null || $alt === null || $alt === '') {
                continue;
            }

            $escapedSrc = preg_quote((string) $src, '/');
            $escapedAlt = htmlspecialchars((string) $alt, ENT_QUOTES, 'UTF-8');

            // Replace empty or missing alt attributes on matching images
            $html = (string) preg_replace(
                '/(<img\b[^>]*src=["\']' . $escapedSrc . '["\'][^>]*?)alt=["\']["\']([^>]*>)/i',
                '$1alt="' . $escapedAlt . '"$2',
                $html,
            );

            // Add alt attribute to images that don't have one
            $html = (string) preg_replace(
                '/(<img\b(?![^>]*alt=)[^>]*src=["\']' . $escapedSrc . '["\'][^>]*?)(\s*\/?>)/i',
                '$1 alt="' . $escapedAlt . '"$2',
                $html,
            );
        }

        return $html;
    }
}
