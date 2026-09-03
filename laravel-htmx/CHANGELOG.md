# Release Notes

## [Unreleased](https://github.com/imsus/laravel-htmx/compare/v0.1.0...1.x)

- Request detection core: `isHtmx` / `isPartial` / `isBoosted` /
  `isHistoryRestore` / `source` / `target` / `requestType` via request macros,
  the `htmx()` helper, and the `Htmx` facade over one `HtmxManager`.
- v4 response headers: all nine through a fluent builder (`trigger`, `retarget`,
  `reswap`, `reselect`, `pushUrl`, `replaceUrl`, `redirect`, `location`,
  `refresh`) with `target` / `swap` / `push` / `replace` aliases, plus
  chainable `Response` macros.
- Single-view conditional `fragment` pattern with a `422` error `partial`
  retargeted to a dedicated slot; workbench `/demo` covers fragment, `422`,
  and history-restore flows.
- Strict v4 client config (`config/laravel-htmx.php`) with commented 2.x
  opt-ins; pinned 4.0.0 vendored assets with SRI hashes; `<x-htmx::scripts />`
  component with config meta tag, extension allowlist, and CDN fallback;
  one-step `htmx:install`.
- `htmx:upgrade-check` markup scanner, bundled agent skill, and release docs.


## [v0.1.0](https://github.com/imsus/laravel-htmx/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
