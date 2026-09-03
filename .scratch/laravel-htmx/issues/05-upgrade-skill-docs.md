# 05: Upgrade-check, skill, and docs

**What to build:** The migration path and agent leverage: an upgrade-check command over Blade templates, error/status documentation, a bundled skill generated from the real implementation, and release notes.

**Blocked by:** 02 (Response header builder), 03 (Fragment and error demo), 04 (Config, assets, and install).

**Status:** ready-for-agent

- [x] Upgrade-check flags 2.x renames, inheritance patterns, old event names, and direct extension includes
- [x] Skill documents `partial`-vs-`full page`, header helpers, the `422` pattern, explicit inheritance, event naming, script includes, and upgrade-check
- [x] Error/status, boosted layout, and form-data notes documented with examples
- [x] README and changelog updated for release

## Comments

Implemented 2026-09-04: `htmx:upgrade-check --path --ext=.blade.php` (offline pure-PHP scanner, 7 rules, path:line findings, exit 1 on findings), `tests/Feature/UpgradeCheckTest.php` (3 tests via Artisan::call — PendingCommand mock fulfills one substring per write, so chained expectsOutputToContain under-reports), bundled skill rewritten from implementation, README gained install/scripts-client/boosted/form-data/upgrade sections, CHANGELOG unreleased entry. Gate green with 04: Pest 39/39, PHPStan L7, Pint, type-coverage 100%.
