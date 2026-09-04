<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('ships strict v4 client defaults', function () {
    expect(config('laravel-htmx.version'))->toBe('4.0.0')
        ->and(config('laravel-htmx.client.implicitInheritance'))->toBeFalse()
        ->and(config('laravel-htmx.client.noSwap'))->toBe([204, 304])
        ->and(config('laravel-htmx.client.defaultTimeout'))->toBe(60000)
        ->and(config('laravel-htmx.client.history.refreshOnMiss'))->toBeTrue();
});

it('lets the host app override the merged config', function () {
    config()->set('laravel-htmx.client.defaultTimeout', 5000);

    expect(config('laravel-htmx.client.defaultTimeout'))->toBe(5000);
});

it('keeps 2.x values as commented opt-ins only', function () {
    $stub = file_get_contents(__DIR__.'/../../config/laravel-htmx.php');
    assert(is_string($stub));

    foreach (["// 'implicitInheritance' => true", "// 'noSwap' => [204, 304, 400, 422, 500]", "// 'defaultTimeout' => 0"] as $optIn) {
        expect($stub)->toContain($optIn);
    }

    expect(config('laravel-htmx.client'))->not->toHaveKey('implicitInheritanceCompat');
});

it('vendors pinned 4.0.0 assets with a matching integrity map', function () {
    /** @var array<string, string> $integrity */
    $integrity = config('laravel-htmx.assets.integrity');

    foreach (array_keys($integrity) as $file) {
        $path = __DIR__."/../../public/{$file}";
        expect(is_file($path))->toBeTrue("missing vendored asset {$file}");

        $hash = 'sha384-'.base64_encode(hash('sha384', (string) file_get_contents($path), true));
        expect($integrity[$file] ?? null)->toBe($hash);
    }
});

it('renders the scripts view from the assets module', function () {
    $html = Blade::render('<x-htmx::scripts />');

    expect($html)->toContain('<meta name="htmx-config"')
        ->and($html)->toContain('/vendor/laravel-htmx/htmx.min.js')
        ->and($html)->toContain('integrity="sha384-');
});

it('installs config, views, and assets in one step', function () {
    $this->artisan('htmx:install')->assertSuccessful();

    $published = [
        config_path('laravel-htmx.php'),
        resource_path('views/vendor/htmx/components/scripts.blade.php'),
        public_path('vendor/laravel-htmx/htmx.min.js'),
    ];

    foreach ($published as $file) {
        expect(is_file($file))->toBeTrue("htmx:install did not publish {$file}");
    }
});
