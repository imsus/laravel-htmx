<?php

declare(strict_types=1);

use Htmx\Htmx\Htmx;

it('resolves the singleton', function () {
    expect(app(Htmx::class))->toBeInstanceOf(Htmx::class);
});

it('returns the same instance from the container', function () {
    expect(app(Htmx::class))->toBe(app(Htmx::class));
});

it('merges the package config', function () {
    expect(config('laravel-htmx.placeholder'))->toBe('default');
});

it('loads the package views', function () {
    expect(view()->exists('laravel-htmx::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('laravel-htmx:placeholder')
        ->expectsOutputToContain('Htmx placeholder command executed.')
        ->assertSuccessful();
});
