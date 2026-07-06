# Changelog

## 1.2.0 (2026-07-06)

### Breaking Changes

- **`SeoInjector::inject(string $html, Suggestions $suggestions)` → `inject(string $html, array $data): string`.**
  The injector now takes the raw `/suggestions` API payload directly instead of
  a `Suggestions` object.
- **`SmartClient::suggestions(string $url): Suggestions` → `suggestions(string $url): array`.**
  Returns the decoded payload array directly; any network/parse failure now
  returns `[]` instead of throwing `SEOJuiceException`.
- **Why:** the previous `Suggestions::fromArray()` read fictional keys
  (`links`, `meta_tags`, `og_tags`, `structured_data` typed as an array) that
  the backend never sends. Every real `/suggestions` response — which sends
  `structured_data` as a JSON-encoded **string** — threw a `TypeError` in the
  constructor, so server-side injection crashed on every request. `Suggestions`
  is rewritten to the real payload shape and remains available as an optional
  typed convenience wrapper (`fromArray()` / `toArray()` / `isEmpty()`), but is
  no longer part of the `SeoInjector`/`SmartClient` call path.

### Server-Side Injection Parity

Full parity with the Cloudflare Worker (`seojuice-ssr.js`) and the WordPress
plugin's stateless injection path:

- **`src/Injection/Transformer.php`** (new) — all transforms ported as pure
  static methods: `replaceMetaTags` (title/description/keywords/OG tags +
  double-decoded `structured_data` → JSON-LD), `replaceImages` (alt-text fill
  on missing/`<5`-char alt), `injectInternalLinks` (first-occurrence keyword
  linking; Latin and CJK — `\p{Han}`/`\p{Hiragana}`/`\p{Katakana}` — keyword
  boundaries via native PCRE lookbehind), `applyContentDiffs` (replace-only,
  drift/ambiguity/idempotency guards), `applyBrokenLinkFixes` (`replace`/`unlink`,
  reads `new_url ?: replacement_url`), `replaceH1`, `addManifestComment`,
  `addSsrFlag`.
- **`validateApiResponse()` (C1)** — rejects a malformed/empty API response
  (errors present, wrong-typed `suggestions`/`images`/`diffs`/`broken_link_fixes`,
  or nothing actionable) before any transform runs.
- **Content-area targeting (C2)** — `insert_into_content_only` restricts link
  injection to the direct text of `p|li|span|div|td|blockquote|dd|figcaption`,
  never headings/nav/chrome.
- **Fail-open** — any exception, empty result, `<0.5×` original length, or a
  missing `<body>` reverts to the original HTML untouched.
- **Idempotent** via `data-seojuice*` markers; safe to run on an already-injected
  page.
- Verified against the shared Worker-generated golden vectors
  (`tests/fixtures/ssr-parity-vectors/`, `tests/Injection/ParityVectorsTest.php`).

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
