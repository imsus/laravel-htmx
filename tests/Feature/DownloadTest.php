<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Imsus\LaravelHtmx\HtmxHeaders;

function downloadProbeRoutes(): void
{
    Route::get('/_htmx-download-builder', function () {
        return htmx()->headers()->download('/files/report.pdf')->applyTo(response('<span>Started</span>'));
    });

    Route::get('/_htmx-download-macro', function () {
        return response('<span>Started</span>')->download('/files/report.pdf');
    });
}

it('points the client at a secondary download while swapping normally', function () {
    downloadProbeRoutes();

    test()->get('/_htmx-download-builder')
        ->assertOk()
        ->assertHeader('HX-Download', '/files/report.pdf')
        ->assertSee('<span>Started</span>', false);

    test()->get('/_htmx-download-macro')
        ->assertOk()
        ->assertHeader('HX-Download', '/files/report.pdf');
});

it('derives a download response macro from the builder', function () {
    expect(HtmxHeaders::RESPONSE_MACROS)->toContain('download')
        ->and(Response::hasMacro('download'))->toBeTrue();
});
