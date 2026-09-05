<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use Imsus\LaravelHtmx\HtmxAssets;

function htmaxAssets(array $overrides = []): HtmxAssets
{
    $base = config('laravel-htmx');

    return new HtmxAssets(new Repository([
        'laravel-htmx' => array_replace_recursive(is_array($base) ? $base : [], $overrides),
    ]));
}

it('ships the slim core build by default', function () {
    expect(config('laravel-htmx.assets.core'))->toBe('htmx.min.js')
        ->and(htmaxAssets()->scriptFiles()[0])->toBe('htmx.min.js');
});

it('emits only the max build when htmax is the core', function () {
    $files = htmaxAssets(['assets' => ['core' => 'htmax.js']])->scriptFiles();

    // htmax already bundles the popular extensions — emitting their
    // standalone scripts alongside would register them twice.
    expect($files)->toBe(['htmax.js']);
});

it('resolves htmax script rows with integrity hashes', function () {
    $scripts = htmaxAssets(['assets' => ['core' => 'htmax.js']])->scripts();

    expect($scripts)->toHaveCount(1)
        ->and($scripts[0]['src'])->toContain('/vendor/laravel-htmx/htmax.js')
        ->and($scripts[0]['integrity'])->toStartWith('sha384-');
});

it('resolves the htmax cdn build without integrity hashes', function () {
    $scripts = htmaxAssets(['assets' => ['core' => 'htmax.js', 'cdnFallback' => true]])->scripts();

    expect($scripts)->toBe([
        ['src' => 'https://cdn.jsdelivr.net/npm/htmx.org@4.0.0/htmax.js', 'integrity' => null],
    ]);
});

it('falls back to the slim core on unknown builds', function () {
    expect(htmaxAssets(['assets' => ['core' => 'htmx.ultimate.js']])->scriptFiles()[0])
        ->toBe('htmx.min.js');
});
