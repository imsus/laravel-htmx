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

You may publish all of the package's resources at once:

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
history-restore demo.

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
