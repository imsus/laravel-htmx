# 02: Stop-polling response

**What to build:** The v4 poll-quit signal: an empty response with the `286` status htmx polling honors. Entry-point method on the same registration path as `poll()` / `errorPartial()`:

```php
if ($feed->finished()) {
    return htmx()->stopPolling();
}
```

**Blocked by:** nothing.

**Status:** ready-for-agent

- [ ] `stopPolling()` returns an empty-body response with status `286`
- [ ] No htmx headers applied (there is nothing to swap — headers would lie)
- [ ] Reachable all three ways: `htmx()->stopPolling()`, `Htmx::stopPolling()`, macro spelling mirroring `poll()`
- [ ] Pest Feature tests at the HTTP seam: status code, empty body, header absence

## Comments

Implemented 2026-09-05: `Htmx::stopPolling()` returning an empty `286` with no headers, reachable via helper + facade (mirrors `poll()` — no `Response` macro, same as the other entry-point methods). `tests/Feature/StopPollingTest.php` proves status, empty body, and zero `HX-*` headers on both spellings. Gate green: Pest 2/2, PHPStan 0 errors.
