# 01: Detection core

**What to build:** The app can branch `partial`-vs-`full page`: request detection (`htmx request`, `partial`, boosted, history-restore, source, target, request type) exposed through request macros, a helper, and a thin facade over one manager.

**Blocked by:** None (can start immediately).

**Status:** ready-for-agent

- [x] `isHtmx` true whenever the htmx request header is present
- [x] `isPartial` true only for partial requests that are not history-restore
- [x] Boosted and history-restore requests resolve to `full page`, never `partial`
- [x] Source and target parsed in `tag#id` format
- [x] Macros, helper, and facade all reach the same manager (no duplicated logic)
- [x] HTTP-boundary Pest tests cover the full detection matrix

## Comments

Implemented 2026-09-04: `src/HtmxManager.php` (single detection owner), `src/Htmx.php` (delegating entry point; response builder lands in 02), `src/helpers.php` (`htmx()`), `Request` macros + singleton bindings in `HtmxServiceProvider`, `files` autoload in `composer.json`, `tests/Feature/DetectionTest.php` (7 HTTP-boundary tests, macro/helper/facade agreement). Gate green: Pest 17/17, PHPStan L7, Pint, type-coverage 100%. Two-axis review: Standards pass (no findings), Spec pass.
