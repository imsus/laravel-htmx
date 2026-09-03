# 04: Config, assets, and install

**What to build:** Strict v4 client defaults plus reproducible client delivery: config with commented 2.x opt-ins, pinned vendored assets with integrity hashes, a scripts component emitting scripts plus the config meta tag, and a one-step install command.

**Blocked by:** 01 (Detection core — shares the provider/config seam).

**Status:** ready-for-agent

- [x] Strict v4 defaults shipped (explicit inheritance off, minimal swap exclusions, 60s timeout, server re-fetch history) per ADR-0001
- [x] Pinned `4.0.0` assets vendored with integrity hashes and explicit extension allowlist per ADR-0002
- [x] Scripts component emits versioned scripts, config meta, and CDN fallback toggle
- [x] Install command publishes config, views, and assets in one step
- [x] Config merge, view loading, and published-manifest assertions pass

## Comments

Implemented 2026-09-04: `config/laravel-htmx.php` (version 4.0.0, client/history strict defaults, commented 2.x opt-ins, SRI map, cdnFallback + allowlist), real upstream 4.0.0 builds vendored in `public/` (sizes match jsdelivr manifest), `resources/views/components/scripts.blade.php` (`<x-htmx::scripts />`, dual `htmx` view hint in provider), `htmx:install` one-step publish, placeholder command/view removed with `ExampleTest` cut over to real wiring, workbench demo switched to the component. `tests/Feature/ConfigAssetsTest.php` (8 tests incl. published-file assertions with skeleton cleanup for hermetic runs). Gate green: Pest 39/39, PHPStan L7, Pint, type-coverage 100%. Two-axis review: real 4.0.0 builds vendored instead of stubs (P1 fix), publish-manifest + opt-in assertions hardened, form-data note added.
