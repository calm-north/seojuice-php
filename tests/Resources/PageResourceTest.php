<?php

declare(strict_types=1);

namespace SEOJuice\Tests\Resources;

use PHPUnit\Framework\TestCase;
use SEOJuice\Data\Page;
use SEOJuice\Data\PageKeyword;
use SEOJuice\Data\PaginatedResult;
use SEOJuice\HttpClient;
use SEOJuice\Resources\PageResource;

final class PageResourceTest extends TestCase
{
    private function makePageData(int $id = 1, string $url = '/page-1'): array
    {
        return [
            'id' => $id,
            'url' => $url,
            'title' => "Page {$id}",
            'page_type' => 'article',
            'seo_score' => 80.0,
            'accessibility_score' => 75.0,
            'onpage_score' => 85.0,
            'conversion_score' => 70.0,
            'meta_description' => 'A test page',
            'language_code' => 'en',
            'og_title' => 'OG Title',
            'og_description' => 'OG Desc',
            'og_image' => null,
            'readability' => [],
            'structured_data' => [],
            'links' => [],
            'created_at' => '2025-01-01',
            'last_processed_at' => '2025-06-01',
        ];
    }

    private function makePaginatedResponse(array $results, int $page = 1, int $totalCount = 1): array
    {
        return [
            'pagination' => [
                'page' => $page,
                'page_size' => 10,
                'total_count' => $totalCount,
                'total_pages' => (int) ceil($totalCount / 10),
            ],
            'results' => $results,
        ];
    }

    public function testListCallsCorrectUrlWithPaginationParams(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/', [
                'page' => 2,
                'page_size' => 25,
            ])
            ->willReturn($this->makePaginatedResponse(
                [$this->makePageData()],
                page: 2,
                totalCount: 50,
            ));

        $resource = new PageResource($http, 'example.com');
        $result = $resource->list(page: 2, pageSize: 25);

        $this->assertInstanceOf(PaginatedResult::class, $result);
    }

    public function testListUsesDefaultPaginationParams(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/', [
                'page' => 1,
                'page_size' => 10,
            ])
            ->willReturn($this->makePaginatedResponse([$this->makePageData()]));

        $resource = new PageResource($http, 'example.com');
        $resource->list();
    }

    public function testListReturnsPaginatedResultWithPages(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->method('get')
            ->willReturn($this->makePaginatedResponse([
                $this->makePageData(1, '/first'),
                $this->makePageData(2, '/second'),
            ], totalCount: 2));

        $resource = new PageResource($http, 'example.com');
        $result = $resource->list();

        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(Page::class, $result->results[0]);
        $this->assertInstanceOf(Page::class, $result->results[1]);
        $this->assertSame(1, $result->results[0]->id);
        $this->assertSame(2, $result->results[1]->id);
    }

    public function testListReturnsPaginationMetadata(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->method('get')
            ->willReturn($this->makePaginatedResponse(
                [$this->makePageData()],
                page: 3,
                totalCount: 50,
            ));

        $resource = new PageResource($http, 'example.com');
        $result = $resource->list(page: 3);

        $this->assertSame(3, $result->pagination->page);
        $this->assertSame(50, $result->pagination->totalCount);
    }

    public function testGetCallsCorrectUrl(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/42/')
            ->willReturn($this->makePageData(42, '/my-page'));

        $resource = new PageResource($http, 'example.com');
        $result = $resource->get('42');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertSame(42, $result->id);
        $this->assertSame('/my-page', $result->url);
    }

    public function testKeywordsCallsCorrectUrl(): void
    {
        $keywordData = [
            'pagination' => [
                'page' => 1,
                'page_size' => 10,
                'total_count' => 2,
                'total_pages' => 1,
            ],
            'results' => [
                [
                    'id' => 1,
                    'keyword' => 'seo tools',
                    'processed_at' => '2025-06-01',
                    'stats' => ['position' => 5],
                ],
                [
                    'id' => 2,
                    'keyword' => 'seo analytics',
                    'processed_at' => null,
                    'stats' => null,
                ],
            ],
        ];

        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/42/keywords/', [
                'page' => 1,
                'page_size' => 10,
            ])
            ->willReturn($keywordData);

        $resource = new PageResource($http, 'example.com');
        $result = $resource->keywords('42');

        $this->assertInstanceOf(PaginatedResult::class, $result);
        $this->assertCount(2, $result->results);
        $this->assertInstanceOf(PageKeyword::class, $result->results[0]);
        $this->assertSame('seo tools', $result->results[0]->keyword);
        $this->assertSame('seo analytics', $result->results[1]->keyword);
    }

    public function testKeywordsAcceptsCustomPagination(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/42/keywords/', [
                'page' => 3,
                'page_size' => 50,
            ])
            ->willReturn([
                'pagination' => [
                    'page' => 3,
                    'page_size' => 50,
                    'total_count' => 200,
                    'total_pages' => 4,
                ],
                'results' => [],
            ]);

        $resource = new PageResource($http, 'example.com');
        $result = $resource->keywords('42', page: 3, pageSize: 50);

        $this->assertSame(3, $result->pagination->page);
        $this->assertSame(50, $result->pagination->pageSize);
    }

    public function testSearchStatsCallsCorrectUrl(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/42/search-stats/', [
                'page' => 1,
                'page_size' => 10,
            ])
            ->willReturn([
                'pagination' => [
                    'page' => 1,
                    'page_size' => 10,
                    'total_count' => 0,
                    'total_pages' => 0,
                ],
                'results' => [],
            ]);

        $resource = new PageResource($http, 'example.com');
        $result = $resource->searchStats('42');

        $this->assertInstanceOf(PaginatedResult::class, $result);
    }

    public function testMetricsHistoryCallsCorrectUrl(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with('websites/example.com/pages/42/metrics-history/', [
                'page' => 1,
                'page_size' => 10,
            ])
            ->willReturn([
                'pagination' => [
                    'page' => 1,
                    'page_size' => 10,
                    'total_count' => 0,
                    'total_pages' => 0,
                ],
                'results' => [],
            ]);

        $resource = new PageResource($http, 'example.com');
        $result = $resource->metricsHistory('42');

        $this->assertInstanceOf(PaginatedResult::class, $result);
    }

    public function testDomainIsEmbeddedInAllUrlPaths(): void
    {
        $http = $this->createMock(HttpClient::class);
        $http->expects($this->once())
            ->method('get')
            ->with($this->stringContains('websites/custom-domain.io/pages/'))
            ->willReturn($this->makePaginatedResponse([$this->makePageData()]));

        $resource = new PageResource($http, 'custom-domain.io');
        $resource->list();
    }
}
