# 02: Response header builder

**What to build:** The server drives swaps from the response: all nine v4 headers (`trigger`, retarget, reswap, reselect, push/replace URL, redirect, location, refresh) through one fluent builder, with the four approved short aliases.

**Blocked by:** 01 (Detection core — needs the manager seam).

**Status:** ready-for-agent

- [x] All nine headers emitted with correct names and value shapes (`trigger` single header, JSON detail, target key)
- [x] Removed v4 headers never emitted (no after-swap/after-settle variants)
- [x] Alias equivalence proven: `target`, `swap`, `push`, `replace` match their originals
- [x] Headers ignored on 3xx semantics respected where applicable
- [x] Header-in/header-out Pest tests, no internal-state assertions

## Comments

Implemented 2026-09-04: `src/HtmxHeaders.php` (fresh-per-call fluent builder, `toArray`/`applyTo`), `Htmx::headers()` factory, 13 `Response` macros in `HtmxServiceProvider`, `illuminate/http` in composer require. Navigation helpers stay 2xx (htmx ignores headers on 3xx). Gate green: Pest 23/23, PHPStan L7, Pint, type-coverage 100%. Two-axis review: Standards pass (no findings); Spec found 2 test gaps (wire proof of HX-Replace-Url, header-out alias equivalence) — both fixed before commit.
