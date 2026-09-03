# Laravel Htmx 4 Server Adapter — Spec

Status: ready-for-agent

## Problem Statement

Laravel developers adding htmx 4 to a Blade app must hand-roll the entire server contract: detecting an `htmx request`, choosing between a `partial` and a `full page`, emitting the nine response headers (including the consolidated `trigger`), designing `422` error responses that now swap by default, re-serving full documents for history-restore and boosted navigation, emitting strict v4 client config, and vendoring versioned assets with the new script-include extension model. Each app solves it slightly differently and drifts from htmx 4 semantics.

## Solution

A focused Laravel adapter that detects htmx requests, branches `partial`-vs-`full page` through the native `fragment` mechanism, emits all v4 response headers through a fluent builder (macros + helper + thin facade), ships strict v4 client config and pinned vendored assets via a scripts component, and provides install and upgrade-check commands plus an agent skill — so the happy path is correct htmx 4 out of the box.

## User Stories

1. As an app developer, I want to detect an `htmx request`, so that controllers can branch server behavior.
2. As an app developer, I want to detect a `partial` request distinctly from any htmx request, so that history-restore requests still get a `full page`.
3. As an app developer, I want boosted requests to default to a `full page`, so that boosted navigation never renders layout-less.
4. As an app developer, I want to read the request source and target, so that server logic can react to which element triggered the request.
5. As an app developer, I want to return a `partial` from the same view via a conditional `fragment`, so that page and rows live in one file without duplication.
6. As an app developer, I want to fire client events via a single `trigger` header with detail and targeting, so that swaps can drive client state without extra endpoints.
7. As an app developer, I want to retarget and reswap the response from the server, so that per-response swap behavior overrides markup defaults.
8. As an app developer, I want to reselect response content from the server, so that partial documents can narrow what gets swapped.
9. As an app developer, I want to push or replace the browser URL from the server, so that partial navigation keeps history meaningful.
10. As an app developer, I want to redirect or refresh from the server via headers, so that post-mutation flows work without full-page hacks.
11. As an app developer, I want short fluent aliases for the four daily methods, so that controllers stay readable.
12. As an app developer, I want validation failures returned as an error `partial` with `422` status that swaps into the target, so that form errors render in place.
13. As an app developer, I want documented per-target status overrides, so that exceptional cases can opt out of error swapping.
14. As an app developer, I want strict v4 client defaults emitted as client config, so that inheritance, swap-exclusion, timeout, and history behave per htmx 4.
15. As an app developer, I want 2.x-compatible values available as commented opt-ins, so that a legacy app can migrate incrementally and visibly.
16. As an app developer, I want a scripts component emitting versioned scripts with integrity hashes plus the config meta tag, so that the client and extension allowlist are reproducible.
17. As an app developer, I want a CDN fallback toggle, so that vendored-first deployments can degrade gracefully.
18. As an app developer, I want an install command publishing config, views, and assets, so that setup is one step.
19. As an app developer, I want an upgrade-check command scanning markup including Blade templates, so that 2.x renames, inheritance, event, and extension issues surface before runtime.
20. As an app developer, I want request/response macros plus a helper and facade over one manager, so that middleware, controllers, and tests all reach the same API idiomatically.
21. As an AI agent, I want a bundled skill documenting the `partial`-vs-`full page` pattern, header helpers, the `422` pattern, explicit inheritance, event naming, script includes, and upgrade-check, so that generated code matches this package.
22. As a package maintainer, I want HTTP-boundary test coverage of detection, branching, all headers, aliases, `422` swapping, and history/boosted full-page behavior, so that regressions fail at the observable contract.

## Implementation Decisions

- Scope is a full server adapter built in three slices: wire contract first, config + assets second, commands + views + skill third.
- Detection vocabulary mirrors the wire: htmx request, `partial` (wire concept) vs `fragment` (Blade mechanism), boosted, history-restore, source, target, request type.
- Rendering house pattern is single-view conditional `fragment`: the page view contains the named `fragment`; the `partial` check is the only branch condition.
- Response surface is all nine v4 headers through one builder; the four high-frequency methods carry one short alias each (`target`, `swap`, `push`, `replace`); removed v4 headers are never emitted.
- Error semantics follow v4: error responses are swap content by default; the documented default is a `422` error `partial`, with per-target overrides for exceptions and strict swap-exclusion defaults retained.
- History-restore and boosted requests resolve to a `full page` by default.
- Client config ships strict v4 defaults (explicit inheritance off, minimal swap exclusions, 60s timeout, server re-fetch history); 2.x values exist only as commented opt-ins per ADR-0001.
- Assets are pinned at `4.0.0` with integrity hashes and an explicit extension allowlist per ADR-0002; the scripts component owns script order, config meta, and CDN fallback.
- Public API is macros + helper primary, thin facade proxying the same manager — no duplicated logic, header-mirroring names.
- No package routes, migrations, or translations; the app owns endpoints and strings.

## Testing Decisions

- Good tests assert external behavior only: headers in, headers + body out, `partial`-vs-`full page` branching, status codes — never manager internals or Blade compilation details.
- Tested at the single HTTP seam: detection matrix, all nine header emissions, alias equivalence, `422` swap, history-restore and boosted full-page, config merge, view loading, command registration and exit, published assets manifest.
- Prior art: the existing Pest Feature suite through the Testbench `TestCase` (singleton resolution, config merge, view loading, artisan command assertions) — extended in place, plus workbench routes as the manual demo for fragment, error, and history flows.
- Full gate remains analyse + lint + type-coverage + Pest.

## Out of Scope

- Package-owned routes, database migrations, and package translations.
- CDN-live or unpinned assets as the default.
- 2.x compatibility shims as defaults.
- Automatic layout-stripping middleware (revisit only on proven repetition pain).
- Aliases beyond the minimal four; removed v4 headers/events; XHR-era behavior.

## Further Notes

- Source material: grilled decisions Q1–Q12, glossary in `CONTEXT.md`, strict-defaults and vendored-assets ADRs, build plan in `docs/plan-htmx4.html`.
- Verification is the package's standard gate plus workbench demos for the fragment, `422`, and history flows.
