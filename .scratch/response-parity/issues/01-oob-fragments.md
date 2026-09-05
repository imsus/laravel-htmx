# 01: OOB multi-fragment response

**What to build:** One response that updates several elements. New entry-point method composing named `fragment` renders from a single view, concatenated in order:

```php
return htmx()->oob(
    view('todos', ['todo' => $todo, 'left' => $left]),
    ['todo', 'todo-count'],
);
```

The app marks OOB roots with `hx-swap-oob` in its own Blade markup; the package renders and concatenates, never rewrites markup.

**Blocked by:** nothing.

**Status:** ready-for-agent

- [ ] `oob($view, $names)` renders each named `fragment` via the core mechanism and returns one `200` response with the bodies concatenated in the given order
- [ ] Empty `$names` returns an empty `200` response (no exception — polling loops compose these calls)
- [ ] Unknown fragment names throw `InvalidArgumentException` naming the offender — deliberately louder than core `fragment()`, which falls back to the full view (verified against `View::render()`; a whole page in a row slot is never what a typo meant)
- [ ] Reachable all three ways: `htmx()->oob()`, `Htmx::oob()`, and whatever macro spelling the provider gives entry-point methods (mirror `poll()`)
- [ ] Skill documents the OOB pattern (markup owns `hx-swap-oob`, package owns composition)
- [ ] Pest Feature tests at the HTTP seam: two-fragment body order, empty-names body, status code

## Comments

Implemented 2026-09-05: `Htmx::oob($view, $names)` delegating composition to core `View::fragments()` (no hand-rolled loop, no markup rewriting), reachable via helper + facade. `tests/Fixtures/views/oob-page.blade.php` carries two `hx-swap-oob` roots; `tests/Feature/OobFragmentsTest.php` proves order, facade order-independence, and empty-list empty body. Skill documents the pattern. Gate green: Pest 3/3, PHPStan 0 errors.

Review fix 2026-09-05: Spec review asked for the unknown-name path in tests; writing it exposed that core `fragment()` falls back to the *full view* for missing names (verified in `View::render()`), which would swap a whole page into a row slot. `oob()` now validates inside the render callback and throws `InvalidArgumentException` naming the offender — spec checkbox updated to the deliberate deviation. `ExampleTest` facade mirror extended with `oob` + `stopPolling`.
