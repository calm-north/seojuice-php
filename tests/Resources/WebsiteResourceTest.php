<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Resources;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Website;
use SEOJuice\HttpClient;
use SEOJuice\Resources\WebsiteResource;

final class WebsiteResourceTest extends TestCase
{
    public function testListCallsHttpGetAndReturnsWebsiteArray(): void
    {
        $responseData = [
            'results' => [
                [
                    'domain' => 'example.com',
                    'platform' => 'wordpress',
                    'industry' => 'technology',
                    'seo_score' => 85.5,
                    'scores' => ['content' => 90],
                    'kpis' => ['pages' => 100],
                    'created_at' => '2025-01-01',
                    'last_processed_at' => '2025-06-01',
                ],
                [
                    'domain' => 'other.com',
                    'platform' => 'shopify',
                    'industry' => null,
                    'seo_score' => 72.0,
                    'scores' => [],
                    'kpis' => [],
                    'created_at' => '2025-02-01',
                    'last_processed_at' => null,
                ],
            ],
        ];

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/')
            ->willReturn($responseData);

        $resource = new WebsiteResource($http);
        $result = $resource->list();

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Website::class, $result[0]);
        $this->assertInstanceOf(Website::class, $result[1]);
        $this->assertSame('example.com', $result[0]->domain);
        $this->assertSame('other.com', $result[1]->domain);
    }

    public function testListHandlesResponseWithoutResultsWrapper(): void
    {
        $responseData = [
            [
                'domain' => 'flat.com',
                'platform' => null,
                'seo_score' => 60.0,
            ],
        ];

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/')
            ->willReturn($responseData);

        $resource = new WebsiteResource($http);
        $result = $resource->list();

        $this->assertCount(1, $result);
        $this->assertSame('flat.com', $result[0]->domain);
    }

    public function testListReturnsEmptyArrayWhenNoWebsites(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/')
            ->willReturn(['results' => []]);

        $resource = new WebsiteResource($http);
        $result = $resource->list();

        $this->assertCount(0, $result);
        $this->assertSame([], $result);
    }

    public function testGetCallsHttpGetWithDomainAndReturnsWebsite(): void
    {
        $responseData = [
            'domain' => 'example.com',
            'platform' => 'wordpress',
            'industry' => 'technology',
            'seo_score' => 92.3,
            'scores' => ['technical' => 88],
            'kpis' => ['indexed_pages' => 500],
            'created_at' => '2025-01-15',
            'last_processed_at' => '2025-06-10',
        ];

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/')
            ->willReturn($responseData);

        $resource = new WebsiteResource($http);
        $result = $resource->get('example.com');

        $this->assertInstanceOf(Website::class, $result);
        $this->assertSame('example.com', $result->domain);
        $this->assertSame('wordpress', $result->platform);
        $this->assertSame('technology', $result->industry);
        $this->assertSame(92.3, $result->seoScore);
        $this->assertSame(['technical' => 88], $result->scores);
        $this->assertSame(['indexed_pages' => 500], $result->kpis);
        $this->assertSame('2025-01-15', $result->createdAt);
        $this->assertSame('2025-06-10', $result->lastProcessedAt);
    }

    public function testGetConstructsCorrectUrlPath(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/my-site.org/')
            ->willReturn(['domain' => 'my-site.org']);

        $resource = new WebsiteResource($http);
        $resource->get('my-site.org');
    }
}
