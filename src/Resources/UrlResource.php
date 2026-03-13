<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\HttpClient;

final class UrlResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    /**
     * @param array<int, string> $urls
     * @return array<string, mixed>
     */
    public function submit(array $urls): array
    {
        return $this->http->post("websites/{$this->domain}/urls/", [
            'urls' => $urls,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function status(string $url): array
    {
        return $this->http->get("websites/{$this->domain}/urls/status/", [
            'url' => $url,
        ]);
    }
}
