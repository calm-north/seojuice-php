<?php

declare(strict_types=1);

namespace SEOJuice;

use SEOJuice\Exceptions\ValidationException;

final class Config
{
    public readonly string $baseUrl;
    public readonly string $smartUrl;
    public readonly int $timeout;
    public readonly string $userAgent;
    public readonly int $connectTimeout;

    public function __construct(
        string $baseUrl = 'https://seojuice.com/api/v2',
        string $smartUrl = 'https://smart.seojuice.io',
        int $timeout = 30,
        string $userAgent = 'seojuice-php/1.0',
        int $connectTimeout = 10,
    ) {
        if ($timeout <= 0) {
            throw new ValidationException('timeout must be greater than 0', 'invalid_config');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->smartUrl = rtrim($smartUrl, '/');
        $this->timeout = $timeout;
        $this->userAgent = $userAgent;
        $this->connectTimeout = $connectTimeout;
    }
}
