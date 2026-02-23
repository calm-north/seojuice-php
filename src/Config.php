<?php

declare(strict_types=1);

namespace SEOJuice;

final class Config
{
    public readonly string $baseUrl;
    public readonly string $smartUrl;
    public readonly int $timeout;
    public readonly string $userAgent;

    public function __construct(
        string $baseUrl = 'https://seojuice.com/api/v2',
        string $smartUrl = 'https://smart.seojuice.io',
        int $timeout = 30,
        string $userAgent = 'seojuice-php/1.0',
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->smartUrl = rtrim($smartUrl, '/');
        $this->timeout = $timeout;
        $this->userAgent = $userAgent;
    }
}
