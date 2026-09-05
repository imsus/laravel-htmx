<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Imsus\LaravelHtmx\Facades\Htmx as HtmxFacade;
use Imsus\LaravelHtmx\Htmx;

it('resolves the singleton', function () {
    expect(app(Htmx::class))->toBeInstanceOf(Htmx::class);
});

it('returns the same instance from the container', function () {
    expect(app(Htmx::class))->toBe(app(Htmx::class));
});

it('merges the package config', function () {
    expect(config('laravel-htmx.version'))->toBe('4.0.0');
});

it('loads the package views', function () {
    expect(view()->exists('htmx::components.scripts'))->toBeTrue();
});

it('registers the artisan commands', function () {
    expect(array_keys(Artisan::all()))->toContain('htmx:install', 'htmx:upgrade-check', 'htmx:hash-assets');
});

it('mirrors the Htmx interface on the facade', function () {
    $docblock = (string) file_get_contents((string) (new ReflectionClass(HtmxFacade::class))->getFileName());

    preg_match_all('/@method\s+static\s+\S+\s+(\w+)\(/', $docblock, $matches);

    $actual = $matches[1];
    sort($actual);

    // Response macros live behind headers(), not on the entry point.
    $expected = array_merge(Htmx::REQUEST_MACROS, ['headers', 'errorPartial', 'eventStream', 'oob', 'poll']);
    sort($expected);

    expect($actual)->toBe($expected);
});
