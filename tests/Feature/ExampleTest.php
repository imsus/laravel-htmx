<?php

declare(strict_types=1);

use Htmx\Htmx\Htmx;
use Illuminate\Support\Facades\Artisan;

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
    expect(view()->exists('laravel-htmx::components.scripts'))->toBeTrue()
        ->and(view()->exists('htmx::components.scripts'))->toBeTrue();
});

it('registers the artisan commands', function () {
    expect(array_keys(Artisan::all()))->toContain('htmx:install', 'htmx:upgrade-check');
});
