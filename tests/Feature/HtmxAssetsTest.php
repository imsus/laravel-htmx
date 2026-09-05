<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Imsus\LaravelHtmx\HtmxAssets;

function assets(array $overrides = []): HtmxAssets
{
    $base = config('laravel-htmx');

    return new HtmxAssets(new Repository([
        'laravel-htmx' => array_replace_recursive(is_array($base) ? $base : [], $overrides),
    ]));
}

it('lists the core build first, then one file per extension slug', function () {
    expect(assets()->scriptFiles())->toBe([
        'htmx.min.js',
        'hx-history-cache.js',
        'hx-prompt.js',
        'hx-ptag.js',
    ]);
});

it('approves extensions by registration name as one comma-separated string', function () {
    $allowlist = assets()->extensionAllowlist();

    expect($allowlist)->toBe('history-cache,hx-prompt,ptag')
        ->and(json_decode($allowlist))->toBeNull();
});

it('merges client defaults with the allowlist for the meta payload', function () {
    $payload = assets()->clientConfig();

    expect($payload['implicitInheritance'])->toBeFalse()
        ->and($payload['extensions'])->toBe('history-cache,hx-prompt,ptag');
});

it('resolves local urls with integrity hashes', function () {
    $scripts = assets()->scripts();

    expect($scripts[0]['src'])->toContain('/vendor/laravel-htmx/htmx.min.js')
        ->and($scripts[0]['integrity'])->toStartWith('sha384-');
});

it('resolves pinned cdn urls without integrity hashes', function () {
    $scripts = assets(['assets' => ['cdnFallback' => true]])->scripts();

    expect($scripts[0]['src'])->toBe('https://cdn.jsdelivr.net/npm/htmx.org@4.0.0/htmx.min.js')
        ->and($scripts[0]['integrity'])->toBeNull();
});

it('derives the cdn base from the pinned version', function () {
    $scripts = assets(['version' => '4.1.0', 'assets' => ['cdnFallback' => true]])->scripts();

    expect($scripts[0]['src'])->toContain('htmx.org@4.1.0/');
});

it('resolves opt-in rows for files outside the auto set', function () {
    $esm = assets()->variant('htmx.esm.js');

    expect($esm['src'] ?? null)->toContain('/vendor/laravel-htmx/htmx.esm.js')
        ->and($esm['integrity'] ?? null)->toStartWith('sha384-')
        ->and(assets()->variant('nope.js'))->toBeNull();
});

it('resolves opt-in rows from the cdn without integrity hashes', function () {
    $esm = assets(['assets' => ['cdnFallback' => true]])->variant('htmx.esm.js');

    expect($esm)->toBe([
        'src' => 'https://cdn.jsdelivr.net/npm/htmx.org@4.0.0/htmx.esm.js',
        'integrity' => null,
    ]);
});
