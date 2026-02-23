# SEOJuice PHP SDK

Official PHP SDK for the [SEOJuice](https://seojuice.io) Intelligence API. Provides a resource-oriented client with typed value objects for all SEOJuice API endpoints.

## Requirements

- PHP 8.1+
- Guzzle 7.0+

## Installation

```bash
composer require seojuice/seojuice
```

## Quick Start

```php
use SEOJuice\SEOJuice;

$client = new SEOJuice('your-api-key');

// Get all websites
$websites = $client->websites()->list();

// Get intelligence summary for a domain
$summary = $client->intelligence('example.com')->summary();
echo $summary->seoScore;
echo $summary->totalPages;
```

## Configuration

```php
use SEOJuice\Config;
use SEOJuice\SEOJuice;

$config = new Config(
    baseUrl: 'https://seojuice.com/api/v2',
    smartUrl: 'https://smart.seojuice.io',
    timeout: 60,
    userAgent: 'my-app/1.0',
);

$client = new SEOJuice('your-api-key', $config);
```

## Resources

### Websites

```php
// List all websites
$websites = $client->websites()->list();

foreach ($websites as $website) {
    echo $website->domain;
    echo $website->seoScore;
}

// Get a specific website
$website = $client->websites()->get('example.com');
```

### Pages

```php
$pages = $client->pages('example.com');

// List pages (paginated)
$result = $pages->list(page: 1, pageSize: 20);

foreach ($result->results as $page) {
    echo $page->url;
    echo $page->seoScore;
    echo $page->title;
}

// Get a specific page
$page = $pages->get('123');

// Get page keywords
$keywords = $pages->keywords('123', page: 1, pageSize: 10);

// Get page search stats
$stats = $pages->searchStats('123');

// Get page metrics history
$history = $pages->metricsHistory('123');
```

### Links

```php
$result = $client->links('example.com')->list(page: 1, pageSize: 50);

foreach ($result->results as $link) {
    echo $link->pageFrom . ' -> ' . $link->pageTo;
    echo $link->keyword;
}
```

### Intelligence

```php
use SEOJuice\Enums\Period;

$intel = $client->intelligence('example.com');

// Get summary with history
$summary = $intel->summary(
    period: Period::NinetyDays,
    includeHistory: true,
    includeTrends: true,
);

echo $summary->seoScore;
echo $summary->aisoScore;
echo $summary->totalPages;
echo $summary->orphanPages;

// Get site topology
$topology = $intel->topology();
echo $topology->totalPages;
echo $topology->avgLinksPerPage;

// Get page speed data
$speed = $intel->pageSpeed('https://example.com/blog');
echo $speed->loadingTime;
```

### Clusters

```php
$clusters = $client->clusters('example.com');

// List clusters
$result = $clusters->list(page: 1, pageSize: 10);

foreach ($result->results as $cluster) {
    echo $cluster->name;
    echo $cluster->pageCount;
    echo $cluster->avgPosition;
}

// Get cluster details
$detail = $clusters->get(42);
echo $detail->name;
print_r($detail->topKeywords);
print_r($detail->timeSeries);
```

### Content

```php
$content = $client->content('example.com');

// List content gaps
$gaps = $content->listGaps(
    category: 'blog',
    intent: 'informational',
    page: 1,
    pageSize: 10,
);

foreach ($gaps->results as $gap) {
    echo $gap->pageName;
    echo $gap->seoPotential;
    echo $gap->totalSearchVolume;
}

// List content decay alerts
$alerts = $content->listDecayAlerts(
    isActive: true,
    severity: 'high',
);

foreach ($alerts->results as $alert) {
    echo $alert->pageUrl;
    echo $alert->severity;
    echo $alert->decayType;
}
```

### Competitors

```php
$result = $client->competitors('example.com')->list(
    includeTrends: true,
    page: 1,
    pageSize: 10,
);

foreach ($result->results as $competitor) {
    echo $competitor->domain;
    echo $competitor->score;
    echo $competitor->intersections;
    echo $competitor->avgPosition;
}
```

### AISO (AI Search Optimization)

```php
use SEOJuice\Enums\Period;

$aiso = $client->aiso('example.com')->get(
    period: Period::ThirtyDays,
    includeHistory: true,
);

echo $aiso->aisoScore;
echo $aiso->totalMentions;
echo $aiso->yourMentions;
echo $aiso->positiveRate;
print_r($aiso->providers);
```

### Analysis (Async)

Submit a URL for analysis and poll for completion:

```php
$analysis = $client->analysis('example.com');

// Submit a URL for analysis
$status = $analysis->submit('https://example.com/new-page');
echo $status->analysisId;
echo $status->status;

// Poll until complete (blocks with sleep)
$result = $analysis->waitForCompletion(
    analysisId: $status->analysisId,
    pollInterval: 3,   // seconds between polls
    maxAttempts: 20,    // maximum number of polls
);

if ($result->isComplete()) {
    print_r($result->result);
}

if ($result->isFailed()) {
    echo $result->errorMessage;
}

// Or check status manually
$status = $analysis->status($status->analysisId);
```

### Reports

```php
use SEOJuice\Enums\ReportType;

$reports = $client->reports('example.com');

// List reports
$result = $reports->list();

foreach ($result->results as $report) {
    echo $report->type;
    echo $report->status;
    echo $report->hasPdf ? 'PDF available' : 'No PDF';
}

// Get report details
$detail = $reports->get(42);
print_r($detail->summary);
print_r($detail->data);

// Download PDF
$pdfContent = $reports->downloadPdf(42);
file_put_contents('report.pdf', $pdfContent);

// Create a new report
$response = $reports->create(ReportType::ThisMonth);
```

### Keywords

```php
$result = $client->keywords('example.com')->list(
    category: 'blog',
    page: 1,
    pageSize: 50,
);

foreach ($result->results as $keyword) {
    echo $keyword->name;
    echo $keyword->searchVolume;
    echo $keyword->keywordDifficulty;
    echo $keyword->cpc;
}
```

### Backlinks

```php
$backlinks = $client->backlinks('example.com');

// List backlinks
$result = $backlinks->list(
    status: 'active',
    dofollow: true,
    page: 1,
    pageSize: 20,
);

foreach ($result->results as $backlink) {
    echo $backlink->sourceUrl . ' -> ' . $backlink->targetUrl;
    echo $backlink->anchorText;
    echo $backlink->dofollow ? 'dofollow' : 'nofollow';
}

// List referring domains
$domains = $backlinks->listDomains();

foreach ($domains->results as $domain) {
    echo $domain->domain;
    echo $domain->rank;
    echo $domain->spamScore;
}
```

### Accessibility

```php
$result = $client->accessibility('example.com')->list(
    severity: 'critical',
    category: 'contrast',
    autoFixable: true,
    page: 1,
    pageSize: 20,
);

foreach ($result->results as $issue) {
    echo $issue->pageUrl;
    echo $issue->category;
    echo $issue->severity;
    echo $issue->wcagCriterion;
    echo $issue->description;
    echo $issue->autoFixed ? 'Auto-fixed' : 'Manual fix needed';
}
```

### Changes

```php
$result = $client->changes('example.com')->list(
    status: 'pending',
    changeType: 'meta_title',
    riskLevel: 'low',
);

foreach ($result->results as $change) {
    echo $change->pageUrl;
    echo $change->changeType;
    echo $change->previousValue . ' -> ' . $change->proposedValue;
    echo $change->confidenceScore;
    echo $change->riskLevel;
}
```

### Google Business Profile

```php
$gbp = $client->gbp('example.com');

// List locations
$locations = $gbp->locations();

foreach ($locations as $location) {
    echo $location->name;
    echo $location->averageRating;
    echo $location->totalReviews;
}

// List reviews with filters
$result = $gbp->reviews(
    rating: 1,
    sentiment: 'negative',
    needsAttention: true,
);

foreach ($result->results as $review) {
    echo $review->authorName;
    echo $review->rating;
    echo $review->comment;
    echo $review->sentiment;
}

// Reply to a review
$gbp->replyToReview(
    reviewId: 123,
    replyText: 'Thank you for your feedback!',
);
```

## Pagination

All list endpoints return a `PaginatedResult` with pagination metadata:

```php
$result = $client->pages('example.com')->list(page: 1, pageSize: 10);

echo $result->pagination->page;        // Current page
echo $result->pagination->pageSize;    // Items per page
echo $result->pagination->totalCount;  // Total items
echo $result->pagination->totalPages;  // Total pages

// Navigation helpers
if ($result->hasNextPage()) {
    $nextPage = $client->pages('example.com')->list(
        page: $result->pagination->page + 1,
    );
}

if ($result->hasPreviousPage()) {
    // Go back
}

// Iterate all pages
$page = 1;
do {
    $result = $client->pages('example.com')->list(page: $page, pageSize: 50);

    foreach ($result->results as $item) {
        // Process item
    }

    $page++;
} while ($result->hasNextPage());
```

## SSR Injection (Smart Client)

For server-side rendering, use the Smart Client to fetch SEO suggestions and inject them into your HTML:

```php
use SEOJuice\SEOJuice;
use SEOJuice\Injection\SeoInjector;

$client = new SEOJuice('your-api-key');

// Fetch suggestions for the current page URL
$suggestions = $client->smart()->suggestions('https://example.com/blog/post');

if (!$suggestions->isEmpty()) {
    $injector = new SeoInjector();

    // Get your rendered HTML (from your template engine, etc.)
    $html = getRenderedHtml();

    // Inject SEO improvements
    $html = $injector->inject($html, $suggestions);

    // Output the enhanced HTML
    echo $html;
}
```

The `SeoInjector` will:
- Inject meta tags (title, description, canonical, robots) before `</head>`
- Inject Open Graph tags before `</head>`
- Inject JSON-LD structured data before `</head>`
- Patch image alt attributes for images with empty or missing alt text

## Laravel Integration

Register SEOJuice as a singleton in a service provider:

```php
// AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(SEOJuice::class, fn () => new SEOJuice(
        config('services.seojuice.api_key'),
    ));
}
```

Use middleware to inject SEO tags into responses:

```php
// SeoInjectionMiddleware.php
$suggestions = app(SEOJuice::class)->smart()->suggestions($request->url());
if (!$suggestions->isEmpty()) {
    $html = (new SeoInjector())->inject($response->getContent(), $suggestions);
    $response->setContent($html);
}
```

See [examples/laravel.php](examples/laravel.php) for the full pattern including controller and Artisan command examples.

## Symfony Integration

Register the client as a service:

```yaml
# config/services.yaml
SEOJuice\SEOJuice:
    arguments:
        $apiKey: '%env(SEOJUICE_API_KEY)%'
```

Use an event subscriber to inject SEO tags on `kernel.response`. See [examples/symfony.php](examples/symfony.php) for subscriber and Twig extension patterns.

## Examples

| Example | Description |
|---------|-------------|
| [intelligence_api.php](examples/intelligence_api.php) | Full API workflow: summary, content gaps, decay alerts, topology, PageSpeed, accessibility |
| [laravel.php](examples/laravel.php) | Laravel service provider, middleware, controller, Artisan command |
| [symfony.php](examples/symfony.php) | Symfony event subscriber, Twig extension, controller |
| [drupal.php](examples/drupal.php) | Drupal 10 module: hooks, block plugin, services |
| [magento.php](examples/magento.php) | Magento 2 observer, ViewModel, DI configuration |
| [redis_cache.php](examples/redis_cache.php) | Redis caching layer with Predis |
| [ssr_injection.php](examples/ssr_injection.php) | Server-side rendering: output buffering, PSR-15 middleware |

## Error Handling

All API errors throw typed exceptions extending `SEOJuiceException`:

```php
use SEOJuice\Exceptions\AuthException;
use SEOJuice\Exceptions\ForbiddenException;
use SEOJuice\Exceptions\NotFoundException;
use SEOJuice\Exceptions\RateLimitException;
use SEOJuice\Exceptions\SEOJuiceException;
use SEOJuice\Exceptions\ServerException;
use SEOJuice\Exceptions\ValidationException;

try {
    $website = $client->websites()->get('example.com');
} catch (AuthException $e) {
    // 401 - Invalid API key
    echo 'Authentication failed: ' . $e->getMessage();
} catch (ForbiddenException $e) {
    // 403 - Insufficient permissions
    echo 'Forbidden: ' . $e->getMessage();
} catch (NotFoundException $e) {
    // 404 - Resource not found
    echo 'Not found: ' . $e->getMessage();
} catch (RateLimitException $e) {
    // 429 - Rate limit exceeded
    echo 'Rate limited: ' . $e->getMessage();
} catch (ValidationException $e) {
    // 400/422 - Invalid request
    echo 'Validation error: ' . $e->getMessage();
} catch (ServerException $e) {
    // 5xx - Server error
    echo 'Server error: ' . $e->getMessage();
} catch (SEOJuiceException $e) {
    // Catch-all for any SEOJuice error
    echo 'Error [' . $e->errorCode . ']: ' . $e->getMessage();
}
```

## License

MIT
