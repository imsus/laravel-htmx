# 03: Fragment and error demo

**What to build:** The single-view conditional `fragment` pattern works end to end, and validation failures render as an error `partial` that swaps into the target — both demoable in the workbench app.

**Blocked by:** 01 (Detection core — needs the `partial` check).

**Status:** ready-for-agent

- [ ] Page view containing the named `fragment` returns `partial` for htmx and `full page` otherwise, with the `partial` check as the only branch
- [ ] Validation failure returns the error `partial` with `422` status and swaps into the target
- [ ] Per-target status overrides documented for exceptional cases
- [ ] Workbench demo covers fragment, `422`, and history-restore full-page flows
