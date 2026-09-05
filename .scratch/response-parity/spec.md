# Response parity — Spec

Status: needs-triage

## Problem Statement

Comparing against the 1.x-era incumbent (`mauricius/laravel-htmx`) surfaced three response-side gaps that are all still valid on the v4 wire: multi-fragment OOB swaps (our `fragment` story is single-slot), the `286` stop-polling status (polling survives in v4, we only cover the `ptag`/`304` loop), and trigger name/id accessors (we expose `source`/`target` as `tag#id` but never the `HX-Trigger` / `HX-Trigger-Name` pair).

## Solution

Close the three gaps in the package's own idiom — entry-point methods on `Htmx` plus the existing macro/helper/facade spellings — with Pest coverage at the HTTP seam. No new Blade directives, no one-purpose response subclasses, no 1.x headers.

## User Stories

1. As an app developer, I want one response to update several elements (a row plus its counter), so that related swaps stay in a single request.
2. As an app developer, I want finished polls to quit without client code, so that completed jobs stop polling v4-style (trigger-less element).
3. As an app developer, I want the browser URL htmx reports with each request, so that redirects-back and logging see the address bar rather than the endpoint.

## Implementation Decisions

- OOB is composition, not a new mechanism: render several named `fragment`s from one view, concatenate in order, return one response. The app marks OOB roots with `hx-swap-oob` in its own markup — the package never rewrites markup.
- Polls quit v4-style by returning the element without trigger attributes (documented in the skill); no status-code helper, since v4 honors none.
- Detection additions (`currentUrl()`) extend the existing `REQUEST_MACROS` + `HEADERS` tables, so helper/macro/facade spellings arrive free.
- v4 research reversed two takes: the client never sends `HX-Trigger` / `HX-Trigger-Name` (dropped after 2.x), and no `286` handling exists in v4 (polls quit by returning the element without trigger attributes) — both reverted before any release, `currentUrl()` (`HX-Current-URL`, genuinely v4-sent) ships instead.
- Glossary kept: `partial` (wire), `fragment` (Blade mechanism), `trigger` (client event).

## Testing Decisions

- Pest Feature tests through the Testbench `TestCase` at the HTTP seam: bodies, status codes, header-out — never internals.
- Detection-table additions inherit the macro-existence loop in `DetectionTest`; new tests prove value shapes only.
- Full gate stays analyse + lint + type-coverage + Pest.

## Out of Scope

- Custom Blade directives (core `@fragment` covers it).
- Multi-root or nested-fragment parsing (no markup rewriting, no new `ext-mbstring` requirement).
- 1.x trigger variants, one-purpose response subclasses.

## Further Notes

- Prior art: `mauricius/laravel-htmx` `addFragment()` (OOB), `HtmxResponseStopPolling` (286), `getTriggerName()` / `getTriggerId()`. Comparison lives in `README.md` ("Choosing between htmx packages").
