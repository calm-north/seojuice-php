<?php

declare(strict_types=1);

namespace SEOJuice\Tests;

use PHPUnit\Framework\TestCase;
use SEOJuice\Config;

final class ConfigTest extends TestCase
{
    public function testDefaultValuesAreSetCorrectly(): void
    {
        $config = new Config();

        $this->assertSame('https://seojuice.com/api/v2', $config->baseUrl);
        $this->assertSame('https://smart.seojuice.io', $config->smartUrl);
        $this->assertSame(30, $config->timeout);
        $this->assertSame('seojuice-php/1.0', $config->userAgent);
    }

    public function testCustomValuesAreAccepted(): void
    {
        $config = new Config(
            baseUrl: 'https://custom-api.example.com/v1',
            smartUrl: 'https://custom-smart.example.com',
            timeout: 60,
            userAgent: 'my-app/2.0',
        );

        $this->assertSame('https://custom-api.example.com/v1', $config->baseUrl);
        $this->assertSame('https://custom-smart.example.com', $config->smartUrl);
        $this->assertSame(60, $config->timeout);
        $this->assertSame('my-app/2.0', $config->userAgent);
    }

    public function testBaseUrlTrailingSlashIsTrimmed(): void
    {
        $config = new Config(baseUrl: 'https://api.example.com/v2/');

        $this->assertSame('https://api.example.com/v2', $config->baseUrl);
    }

    public function testBaseUrlMultipleTrailingSlashesAreTrimmed(): void
    {
        $config = new Config(baseUrl: 'https://api.example.com/v2///');

        $this->assertSame('https://api.example.com/v2', $config->baseUrl);
    }

    public function testSmartUrlTrailingSlashIsTrimmed(): void
    {
        $config = new Config(smartUrl: 'https://smart.example.com/');

        $this->assertSame('https://smart.example.com', $config->smartUrl);
    }

    public function testSmartUrlMultipleTrailingSlashesAreTrimmed(): void
    {
        $config = new Config(smartUrl: 'https://smart.example.com///');

        $this->assertSame('https://smart.example.com', $config->smartUrl);
    }

    public function testBaseUrlWithoutTrailingSlashIsUnchanged(): void
    {
        $config = new Config(baseUrl: 'https://api.example.com/v2');

        $this->assertSame('https://api.example.com/v2', $config->baseUrl);
    }
}
