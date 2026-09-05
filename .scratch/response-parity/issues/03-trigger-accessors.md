# 03: Trigger name/id accessors

**What to build:** The triggering element's identity from the `HX-Trigger` / `HX-Trigger-Name` request pair, extending the existing detection tables so all spellings arrive free:

```php
$request->triggerId();   // HX-Trigger
$request->triggerName(); // HX-Trigger-Name
```

**Blocked by:** nothing.

**Status:** ready-for-agent

- [ ] `triggerId()` / `triggerName()` added to `REQUEST_MACROS` + `HEADERS` in `src/Htmx.php` (no new registration code)
- [ ] Nullable string return, absent header yields `null` — same contract as `source()` / `target()`
- [ ] Facade docblock gains the two `@method` lines (keeps IDE completion honest)
- [ ] Pest tests prove value shapes for present/absent headers (macro existence is already covered by the `DetectionTest` table loop)

## Comments

Implemented 2026-09-05: `triggerId()` / `triggerName()` on the shared detection tables (`REQUEST_MACROS` + `HEADERS`), so macro/helper/facade spellings arrive with zero registration code. `DetectionTest` probe covers all three surfaces plus a dedicated value-shape test. Gate green: Pest 11/11 Detection, PHPStan 0 errors.
