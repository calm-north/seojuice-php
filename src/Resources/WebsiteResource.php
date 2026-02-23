<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\Data\Website;
use SEOJuice\HttpClient;

final class WebsiteResource
{
    public function __construct(
        private readonly HttpClient $http,
    ) {
    }

    /**
     * @return array<int, Website>
     */
    public function list(): array
    {
        $data = $this->http->get('websites/');

        $websites = $data['results'] ?? $data;

        return array_map(
            fn (array $item) => Website::fromArray($item),
            $websites,
        );
    }

    public function get(string $domain): Website
    {
        $data = $this->http->get("websites/{$domain}/");

        return Website::fromArray($data);
    }
}
