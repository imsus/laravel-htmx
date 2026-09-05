---
name: laravel-htmx-development
description: >
  Configure and apply the Laravel HTMX package in Laravel applications:
  partial-vs-full-page branching, response headers, the 422 error pattern,
  script includes, and the htmx:upgrade-check migration command.
license: MIT
metadata:
  author: Imam Susanto
---

# Laravel HTMX

Use this skill when a Laravel application needs to integrate the `imsus/laravel-htmx` package (htmx 4 server adapter).

## Primary Goal

- apply the `imsus/laravel-htmx` package's public API in the smallest correct way

## Workflow

### 1. Install and publish

```bash
composer require imsus/laravel-htmx
php artisan htmx:install
```

`htmx:install` publishes config (`config/laravel-htmx.php`), views, and the
pinned htmx 4.0.0 assets. Or publish individually with
`vendor:publish --tag="laravel-htmx-config|laravel-htmx-views|laravel-htmx-assets"`.

### 2. Include the client once per layout

```blade
<head>
    <x-htmx::scripts />
</head>
```

The component emits versioned `<script>` tags with SRI hashes, the
`<meta name="htmx-config">` tag (strict v4 defaults: explicit inheritance,
minimal swap exclusions, 60s timeout, server re-fetch history), and the
extension allowlist. Set `assets.cdnFallback` to `true` to degrade to the
pinned CDN build. 2.x-compatible values exist only as commented opt-ins in
the config file.

### 3. Branch partial vs full page with the only branch

Keep the page and its fragments in one Blade view:

```blade
@fragment('rows')
<ul id="rows">...</ul>
@endfragment
```

```php
Route::get('/items', fn (Request $request) =>
    view('items', ['items' => Item::all()])->fragmentIf($request->isPartial(), 'rows')
);
```

`$request->isPartial()` is true only for fragment requests — history-restore
and boosted navigation always get the full page. Detection surfaces:
`$request->isHtmx()`, `isPartial()`, `isBoosted()`, `isHistoryRestore()`,
`source()` / `target()` (`tag#id` format), `triggerId()` / `triggerName()`
(the firing element's id and name), and `requestType()`. The same API
is reachable via `htmx()` and the `Htmx` facade.

One response can update several elements: mark each fragment root with
`hx-swap-oob` in your markup, then compose them server-side in order —
`htmx()->oob($view, ['todo', 'todo-count'])`. The package renders and
concatenates; targeting stays in the markup where it belongs.

### 4. Drive swaps from the response

```php
return htmx()->headers()
    ->trigger(['itemSaved' => ['target' => '#toast']])
    ->retarget('#rows')
    ->reswap('outerHTML')
    ->applyTo(response($html));
```

All eleven response headers (nine v4, plus `ptag` and `download`): `trigger` (single `HX-Trigger`, JSON detail),
`retarget`, `reswap`, `reselect`, `pushUrl`, `replaceUrl`, `redirect`,
`location`, `refresh`, `ptag`, `download` — one canonical name per header, no aliases.
Equivalents exist as chainable `Response` macros
(`response($html)->retarget('#rows')`). Navigation helpers stay 2xx — htmx
ignores response headers on 3xx. Never emit `HX-Trigger-After-Swap` /
`HX-Trigger-After-Settle`: removed in v4.

### 5. Return validation failures as a 422 error partial

htmx 4 swaps error responses by default. Retarget to a dedicated error slot
so the swap never destroys the list it validates:

```php
if ($validator->fails()) {
    return htmx()->errorPartial(
        view('items', ['items' => [], 'errors' => $validator->errors()]),
        'form-errors',
        '#form-errors',
    );
}
```

One call renders the named Fragment alone, retargets the surviving error slot with `innerHTML`, and answers `422`. Pass a fourth argument to override the status per target; reach for the manual `headers()` chain only for the exceptional shapes below.

Per-target overrides: `200` when the target renders handled content itself;
`200` + `HX-Redirect`/`HX-Location` when the failure leaves the page (never
a real 3xx); another `4xx` when client code distinguishes failure kinds.

### 6. Be explicit about inheritance, events, and deletes

- Strict defaults ship `implicitInheritance: false`: mark inherited swaps
  with the `:inherited` modifier instead of relying on parent attributes.
- Client events are `htmx:*` names (`htmx:swap`, ...). XHR-era events
  (`htmx:xhr:*`, `htmx:abort`) never fire — v4 uses `fetch()`.
- `from:`/`target:` selectors containing spaces or commas need single
  quotes. Boosted links (`hx-boost`) render full pages by default.
- Deletes from a row: `hx-delete hx-include="closest form"`. File uploads:
  `hx-encoding="multipart/form-data"` on the form; the server reads
  `$request->file()` normally and validation failures use the same `422`
  error-`partial` pattern.

### 7. Use the server-backed extensions

- Polling without wasted swaps (`hx-ptag`): answer with
  `htmx()->poll($view, 'news', $current)` — a matching incoming tag
  returns an empty `304`, otherwise the Fragment renders stamped with
  the new tag. The `ptag($tag)` builder and `Response` macro remain for
  hand-assembled responses. When the poll is finished, quit it with
  `htmx()->stopPolling()` — an empty `286` htmx honors by retiring the poller.
- Prompt answers (`hx-prompt`): read `$request->prompt()`.
- Speculative preloads (`hx-preload`): check `$request->isPreloaded()` and
  serve cheaply.
- Streams (`hx-sse`): answer with `htmx()->eventStream([...])` — plain
  strings become HTML-swapped events, arrays carry `event`/`id`/`retry`.
- Side downloads (`hx-download`): `response($html)->download($url)` fetches
  the file while the body swaps normally; direct file responses just use
  `response()->download()`.
- Prefer the max build? Set `assets.core` to `'htmax.js'` and the scripts
  component emits only that file — never alongside standalone extensions.

### 8. Migrate a 2.x app before runtime
```bash
php artisan htmx:upgrade-check --path=resources/views --ext=.blade.php
```

Flags removed attributes (`hx-ext`, `hx-request`, `hx-vars`, `hx-params`),
removed headers (`HX-Trigger-After-*`), XHR-era events, direct extension
`<script>` includes, `hx-inherit`, and unquoted `from:(`/`target:(`
selectors. Exits non-zero while findings remain.

## Rules, References, and Templates

Read before executing:

- `config/laravel-htmx.php` — strict v4 defaults, SRI map, extension allowlist
- `src/Htmx.php` — detection plus the headers() entry point (single owner; macros/helper/facade delegate)
- `src/HtmxHeaders.php` — eleven canonical header methods

## Examples

- Assert the wire contract in a consuming app: `get('/items',
  ['HX-Request' => 'true', 'HX-Request-Type' => 'partial'])` returns the
  fragment without layout; the same URL without headers returns the full page.

## Anti-patterns

- do not branch on `isHtmx()` for layout decisions; use `isPartial()` so
  history-restore and boosted requests keep full pages.
- do not return validation errors with `200` by default; use `422` + an
  error-slot retarget.
- do not include htmx from a CDN `<script>` tag by hand; use
  `<x-htmx::scripts />` (or the `cdnFallback` toggle).
- do not document package internals here; keep the skill focused on adoption in Laravel apps.
