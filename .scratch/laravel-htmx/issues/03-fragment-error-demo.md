# 03: Fragment and error demo

**What to build:** The single-view conditional `fragment` pattern works end to end, and validation failures render as an error `partial` that swaps into the target — both demoable in the workbench app.

**Blocked by:** 01 (Detection core — needs the `partial` check).

**Status:** ready-for-agent

- [x] Page view containing the named `fragment` returns `partial` for htmx and `full page` otherwise, with the `partial` check as the only branch
- [x] Validation failure returns the error `partial` with `422` status and swaps into the target
- [x] Per-target status overrides documented for exceptional cases
- [x] Workbench demo covers fragment, `422`, and history-restore full-page flows

## Comments

Implemented 2026-09-04: no new `src` needed — native `fragmentIf` + ticket 01/02 APIs deliver the pattern. `tests/Feature/FragmentTest.php` + fixture view prove partial/full/history/422/valid flows; README documents the house pattern, the `422`+retarget error slot, and per-target overrides; workbench `/demo` serves all flows live (also required registering the provider in `testbench.yaml` and loading the demo view by file path — skeleton boots from the default path). Gate green: Pest 28/28, PHPStan L7, Pint, type-coverage 100%. Two-axis review fixed 2 findings pre-commit: 422 now retargets `#form-errors` (was destroying `#rows`) and the routes-file global helper became a route-local closure.
