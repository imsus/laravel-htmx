<div align="center">
    <h1>Laravel Htmx</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/imsus/laravel-htmx"><img src="https://img.shields.io/packagist/v/imsus/laravel-htmx.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/imsus/laravel-htmx"><img src="https://img.shields.io/packagist/php-v/imsus/laravel-htmx.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/imsus/laravel-htmx"><img src="https://badge.laravel.cloud/badge/imsus/laravel-htmx?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/imsus/laravel-htmx/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/imsus/laravel-htmx/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/imsus/laravel-htmx"><img src="https://img.shields.io/packagist/dt/imsus/laravel-htmx.svg?style=flat-square" alt="Total Downloads"></a>
</p>

HTMX integration for Laravel

## Installation

You can install the package via Composer:

```bash
composer require imsus/laravel-htmx
```

Publish config, views, and pinned htmx 4.0.0 assets in one step:

```bash
php artisan htmx:install
```

Or publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="laravel-htmx"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="laravel-htmx-config"
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="laravel-htmx-views"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="laravel-htmx-assets"
```

## Usage

### Partial vs full page

Keep the page and its fragments in one Blade view. The `partial` check is the only branch:

```blade
{{-- resources/views/items.blade.php --}}
<h1>Items</h1>

@fragment('rows')
<ul id="rows">
    @foreach ($items as $item)
        <li>{{ $item }}</li>
    @endforeach
</ul>
@endfragment
```

```php
use Illuminate\Http\Request;

Route::get('/items', function (Request $request) {
    return view('items', ['items' => Item::all()])
        ->fragmentIf($request->isPartial(), 'rows');
});
```

Htmx requests get the `rows` fragment only; every other request — including
history-restore and boosted navigation — gets the full page.

### Validation errors

htmx 4 swaps error responses by default, so return validation failures as an
error `partial` with `422`. Retarget it to a dedicated error slot so the swap
never destroys the list it validates — the slot is always present in the page,
the fragment carries only the messages:

```blade
<div id="form-errors"></div>

@fragment('form-errors')
@foreach ($errors->all() as $error)
<p>{{ $error }}</p>
@endforeach
@endfragment
```

```php
Route::post('/items', function (Request $request) {
    $validator = Validator::make($request->all(), ['name' => 'required|min:3']);

    if ($validator->fails()) {
        return htmx()
            ->headers()
            ->retarget('#form-errors')
            ->reswap('innerHTML') // fill the slot; without this the form's outerHTML swap would replace (and destroy) it
            ->applyTo(response(
                view('items', ['items' => [], 'errors' => $validator->errors()])
                    ->fragmentIf(true, 'form-errors'),
                422
            ));
    }

    // ...
});
```

### Per-target status overrides

The `422` default fits form targets. For exceptional targets, override per
response instead of changing the default:

- Swap without error semantics: return the error `partial` with `200` when the
  target must render content the app handles itself (e.g. a live region that
  announces both success and failure markup the same way).
- Navigate instead of swapping: return `200` with `HX-Redirect` or
  `HX-Location` (via `htmx()->headers()`) when the failure leaves the current
  page behind — for example an expired session that must land on login. Never
  use a real `3xx` status: htmx ignores response headers on `3xx` responses.
- Keep the failure visible to error listeners: return another `4xx` status
  with the error `partial` when client code distinguishes failure kinds.

See `workbench/routes/web.php` (`/demo`) for a runnable fragment, `422`, and
history-restore demo, and `/patterns` for a server-communication gallery —
active search (`partial` + `HX-Push-Url`), delete in place (`HX-Trigger`
toast), and active validation (`422` error `partial`).

### Client scripts and config

Include the client once per layout — versioned scripts with SRI hashes, the
strict v4 config meta tag, and the extension allowlist:

```blade
<head>
    <x-htmx::scripts />
</head>
```

Strict v4 defaults ship in `config/laravel-htmx.php`: explicit inheritance
off, minimal swap exclusions (`204`, `304`), a 60s fetch timeout, and server
re-fetch history. 2.x-compatible values exist only as commented opt-ins.
Set `assets.cdnFallback` to `true` to degrade to the pinned
`htmx.org@4.0.0` CDN build; `assets.extensions` is the explicit allowlist
(htmx 4 loads extensions via direct `<script>` tags, so the package owns the
list). The ESM build is vendored for bundler consumers but not auto-included.

### Boosted layouts and row deletes

Boosted navigation (`hx-boost`) defaults to a `full page` — never a layout-less
`partial` — because `isPartial()` is false for boosted requests. Client events
are `htmx:*` names; XHR-era events (`htmx:xhr:*`, `htmx:abort`) never fire
since v4 uses `fetch()`. Quote `from:`/`target:` selectors containing spaces
or commas with single quotes. Deletes scoped to a row's form:

```html
<button hx-delete="/items/1" hx-include="closest form">Delete</button>
```

Forms submit as usual — file uploads just need explicit multipart encoding,
and the `422` error-`partial` pattern applies unchanged:

```html
<form hx-post="/avatar" hx-encoding="multipart/form-data" hx-target="#form-errors" hx-swap="innerHTML">
    <input type="file" name="avatar">
    <button type="submit">Upload</button>
</form>
```

The server reads `$request->file('avatar')` normally; validation failures
return the `422` error `partial` into `#form-errors` like any other form.

### Extension server support

Three extensions need the server to speak their wire contract, so the
package covers them (see `docs/extensions-server-support.md` for the full
17-extension survey):

```php
// hx-ptag: skip the swap with 304 while the poll tag is current.
if ($request->ptag() === $current) {
    return response('', 304);
}

return response($html)->ptag($current);

// hx-prompt: read the pre-request prompt answer.
$reason = $request->prompt();

// hx-preload: detect speculative preload GETs.
if ($request->isPreloaded()) {
    // serve cheaply; the response may never be consumed.
}
```

`hx-ptag.js` ships vendored with an SRI hash and allowlist entry, emitted by
`<x-htmx::scripts />` like the other extensions.

### Upgrading from 2.x
Scan Blade markup — including templates — before runtime:

```bash
php artisan htmx:upgrade-check --path=resources/views --ext=.blade.php
```

Flags removed attributes (`hx-ext`, `hx-request`, `hx-vars`, `hx-params`),
removed headers (`HX-Trigger-After-Swap`, `HX-Trigger-After-Settle`), XHR-era
events, direct extension `<script>` includes, `hx-inherit` (use the
`:inherited` modifier), and unquoted `from:(`/`target:(` selectors. Exits
non-zero while findings remain.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Htmx! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Imam Susanto](https://github.com/imsus)
- [All Contributors](../../contributors)

## License

Laravel Htmx is open-sourced software licensed under the [MIT license](LICENSE.md).
