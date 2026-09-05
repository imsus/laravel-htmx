# Release Notes

## [Unreleased](https://github.com/imsus/laravel-htmx/compare/v0.2.0...HEAD)

## [v0.2.0](https://github.com/imsus/laravel-htmx/compare/v0.1.0...v0.2.0) - 2026-09-05

One response, many swaps — plus a docs pass.

- OOB multi-fragment responses: `htmx()->oob($view, ['todo', 'todo-count'])` composes named fragments in order; unknown names throw instead of swapping a full page.
- `currentUrl()` detection from the v4-sent `HX-Current-URL` header, across request macro, helper, and facade.
- README: package comparison with mauricius/laravel-htmx and a Laravel-voice rewrite of the README and PHPDocs.
- Bundled skill documents the OOB pattern and the v4 poll-quit pattern (trigger-less element).

## [v0.1.0](https://github.com/imsus/laravel-htmx/compare/...v0.1.0) - 2026-09-05

- Request detection core: `isHtmx` / `isPartial` / `isBoosted` /
  `isHistoryRestore` / `source` / `target` / `requestType` via request macros,
  the `htmx()` helper, and the `Htmx` facade.
- v4 response headers: eleven through a fluent builder (`trigger`, `retarget`,
  `reswap`, `reselect`, `pushUrl`, `replaceUrl`, `redirect`, `location`,
  `refresh`) with `target` / `swap` / `push` / `replace` aliases, plus
  chainable `Response` macros.
- Single-view conditional `fragment` pattern with a `422` error `partial`
  retargeted to a dedicated slot; workbench `/` covers fragment, `422`,
  and history-restore flows.
- Strict v4 client config (`config/laravel-htmx.php`) with commented 2.x
  opt-ins; pinned 4.0.0 vendored assets with SRI hashes; `<x-htmx::scripts />`
  component with config meta tag, extension allowlist, and CDN fallback;
  one-step `htmx:install`.
- `htmx:upgrade-check` markup scanner, bundled agent skill, and release docs.
- Extension server support: `ptag` / `prompt` / `isPreloaded` detection plus
  the `HX-PTag` response header, vendored `hx-ptag.js`, and a `/patterns`
  polling ticker (see `docs/extensions-server-support.md`).
