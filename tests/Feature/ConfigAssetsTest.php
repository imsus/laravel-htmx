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
    $files = ['htmx.min.js', 'htmx.esm.js', 'htmax.js', 'hx-history-cache.js', 'hx-prompt.js'];

    foreach ($files as $file) {
        $path = __DIR__."/../../public/{$file}";
        expect(is_file($path))->toBeTrue("missing vendored asset {$file}");

        $hash = 'sha384-'.base64_encode(hash('sha384', (string) file_get_contents($path), true));
        /** @var array<string, string> $integrity */
        $integrity = config('laravel-htmx.assets.integrity');
        expect($integrity[$file] ?? null)->toBe($hash);
    }
});

it('renders the scripts component with versioned scripts and config meta', function () {
    $html = Blade::render('<x-htmx::scripts />');

    expect($html)->toContain('/vendor/laravel-htmx/htmx.min.js')
        ->and($html)->toContain('integrity="sha384-')
        ->and($html)->toContain('<meta name="htmx-config"')
        ->and($html)->toContain('&quot;implicitInheritance&quot;:false');
});

it('emits the extension allowlist from the scripts component', function () {
    $html = Blade::render('<x-htmx::scripts />');

    /** @var array<string, string> $extensions */
    $extensions = config('laravel-htmx.assets.extensions');

    // Script tags load by vendored slug; the allowlist approves by
    // registration name as one comma-separated string (never JSON).
    foreach (array_keys($extensions) as $slug) {
        expect($html)->toContain("/vendor/laravel-htmx/{$slug}.js");
    }

    expect($html)
        ->toContain('history-cache,hx-prompt,ptag')
        ->not->toContain('&quot;extensions&quot;:[');
});

it('switches to pinned CDN urls when the fallback toggle is on', function () {
    config()->set('laravel-htmx.assets.cdnFallback', true);

    expect(Blade::render('<x-htmx::scripts />'))
        ->toContain('https://cdn.jsdelivr.net/npm/htmx.org@4.0.0/')
        ->not->toContain('/vendor/laravel-htmx/htmx.min.js');
});

it('installs config, views, and assets in one step', function () {
    $this->artisan('htmx:install')->assertSuccessful();

    $published = [
        config_path('laravel-htmx.php'),
        resource_path('views/vendor/laravel-htmx/components/scripts.blade.php'),
        public_path('vendor/laravel-htmx/htmx.min.js'),
    ];

    foreach ($published as $file) {
        expect(is_file($file))->toBeTrue("htmx:install did not publish {$file}");
    }
});
