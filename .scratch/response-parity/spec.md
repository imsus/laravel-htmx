# Response parity — Spec

Status: needs-triage

## Problem Statement

Comparing against the 1.x-era incumbent (`mauricius/laravel-htmx`) surfaced three response-side gaps that are all still valid on the v4 wire: multi-fragment OOB swaps (our `fragment` story is single-slot), the `286` stop-polling status (polling survives in v4, we only cover the `ptag`/`304` loop), and trigger name/id accessors (we expose `source`/`target` as `tag#id` but never the `HX-Trigger` / `HX-Trigger-Name` pair).

## Solution

Close the three gaps in the package's own idiom — entry-point methods on `Htmx` plus the existing macro/helper/facade spellings — with Pest coverage at the HTTP seam. No new Blade directives, no one-purpose response subclasses, no 1.x headers.

## User Stories

1. As an app developer, I want one response to update several elements (a row plus its counter), so that related swaps stay in a single request.
2. As an app developer, I want to stop a poller with the `286` status, so that finished polls quit without client code.
3. As an app developer, I want the triggering element's id and name, so that server logic can react to *which* control fired.

## Implementation Decisions

- OOB is composition, not a new mechanism: render several named `fragment`s from one view, concatenate in order, return one response. The app marks OOB roots with `hx-swap-oob` in its own markup — the package never rewrites markup.
- `286` ships as an entry-point method with an empty body; same registration path as `poll()` / `errorPartial()`.
- Trigger accessors extend the existing `REQUEST_MACROS` + `HEADERS` tables, so helper/macro/facade spellings arrive free.
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
