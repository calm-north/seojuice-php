# Changelog

## 1.1.0 (2026-03-13)

### New Features

- **Changes Management** — `ChangeResource` expanded from 1 method to 11: `list`, `get`, `stats`, `settings`, `updateSettings`, `approve`, `reject`, `revert`, `pull`, `verify`, `bulk`
- **Action Items** — New `ActionItemResource` with `list`, `get`, `create`, `update`, `summary`, `groups` methods
- **Auto-Pagination** — New `AutoPaginator` helper class with `paginate()` generator and `all()` collector for iterating through all pages automatically
- **Domain Health** — New `DomainHealthResource` with `get()` method
- **SERP Landscape** — New `SerpLandscapeResource` with `get()` method
- **Benchmarks** — New `BenchmarkResource` with `get()` method
- **URL Submission** — New `UrlResource` with `submit()` and `status()` methods
- **Page-Scoped Endpoints** — `PageResource` gains `content()`, `contentQuality()`, `geoReadiness()` methods
- **PATCH Support** — `HttpClient` now supports PATCH requests

### New Data Objects

- `ChangeStats`, `ChangeSettings`, `BulkActionResult`
- `ActionItem`, `ActionItemSummary`, `ActionItemGroup`

### New Enums

- `ChangeStatus` — Pending, Approved, Applied, Pulled, Verified, Rejected, Reverted, Expired
- `ChangeType` — InternalLink, MetaDescription, MetaTitle, AltText, HeadingStructure, SchemaMarkup, CanonicalTag, OpenGraph, Robots, Redirect, ContentUpdate, CustomHtml
- `AutomationMode` — Off, Suggest, ManualDeploy, AutoDeploy

### New Examples

- [`changes_management.php`](examples/changes_management.php) — Full change lifecycle: stats, triage, bulk approve, review/reject, automation settings, velocity monitoring
- [`webhook_receiver.php`](examples/webhook_receiver.php) — HMAC-SHA256 signature verification, event routing, async processing with `fastcgi_finish_request()`
- [`action_items.php`](examples/action_items.php) — Action item summary, listing, groups, create, update workflow

### Bug Fixes

- Fixed `AnalysisResource` URL paths (`analysis/` → `analyze/`)
- Fixed `AnalysisStatus::isComplete()` checking `'complete'` instead of `'completed'`
- Fixed `ContentDecayAlert` field names (`*Previous` → `*Baseline`) and added missing `*ChangePct` fields
- Fixed `ChangeRecord` expanded from 10 fields to 23 fields to match API response
- Fixed `ChangeResource::list()` filter parameters to match API (removed `riskLevel`, added `url`)
- Fixed `webhook_receiver.php` `getenv()` crash when env var not set
- Removed duplicate standalone `ContentQualityResource` and `GeoReadinessResource` (now page-scoped on `PageResource`)

## 1.0.0 (2026-02-23)

- Initial Release
