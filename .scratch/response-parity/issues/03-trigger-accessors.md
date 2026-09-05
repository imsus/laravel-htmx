# 03: Trigger name/id accessors

**What to build:** The triggering element's identity from the `HX-Trigger` / `HX-Trigger-Name` request pair, extending the existing detection tables so all spellings arrive free:

```php
$request->triggerId();   // HX-Trigger
$request->triggerName(); // HX-Trigger-Name
```

**Blocked by:** nothing.

**Status:** wontfix

> Research 2026-09-05: the v4 client never sends `HX-Trigger` /
> `HX-Trigger-Name`. The full request-header inventory of the vendored
> 4.0.0 build is `HX-Request`, `HX-Boosted`, `HX-Current-URL`,
> `HX-Request-Type`, `HX-Source`, `HX-Target`,
> `HX-History-Restore-Request` — the trigger pair existed in 2.x (see
> `test/core/headers.js` upstream) and was dropped in v4. Accessors
> would read headers that never arrive. Removed; `currentUrl()` ships
> instead, covering the one v4-sent identity header we had missed.
> Never released (built and reverted on unreleased `main`).

- [ ] `triggerId()` / `triggerName()` added to `REQUEST_MACROS` + `HEADERS` in `src/Htmx.php` (no new registration code)
- [ ] Nullable string return, absent header yields `null` — same contract as `source()` / `target()`
- [ ] Facade docblock gains the two `@method` lines (keeps IDE completion honest)
- [ ] Pest tests prove value shapes for present/absent headers (macro existence is already covered by the `DetectionTest` table loop)

## Comments

Implemented 2026-09-05: `triggerId()` / `triggerName()` on the shared detection tables (`REQUEST_MACROS` + `HEADERS`), so macro/helper/facade spellings arrive with zero registration code. `DetectionTest` probe covers all three surfaces plus a dedicated value-shape test. Gate green: Pest 11/11 Detection, PHPStan 0 errors.
