<?php

declare(strict_types=1);

namespace SEOJuice\Resources;

use SEOJuice\HttpClient;

final class BenchmarkResource
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $domain,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->http->get("websites/{$this->domain}/benchmarks/");
    }
}
