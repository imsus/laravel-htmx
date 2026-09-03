# Vendored htmx assets with SRI

Vendor pinned `htmx@4.0.0` (`htmx.min.js`, `htmx.esm.*`, `htmax.js`, `hx-history-cache`, `hx-prompt`) into `public/vendor/htmx` and emit them via `<x-htmx::scripts>` with SRI hashes from config, plus a `cdnFallback` toggle. Do not default to CDN-live or unpinned versions.

htmx 4 loads extensions via direct `<script>` tags with an `extensions` allowlist in `htmx-config` — the package must own the exact script list to keep it correct. Pinned + SRI is the only reproducible story; unpinned CDN breaks apps on every upstream patch.
