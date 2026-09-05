<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Imsus\LaravelHtmx\Facades\Htmx as HtmxFacade;

function stopPollingProbeRoutes(): void
{
    Route::get('/_htmx-stop-polling', function () {
        return htmx()->stopPolling();
    });

    Route::get('/_htmx-stop-polling-facade', function () {
        return HtmxFacade::stopPolling();
    });
}

function expectQuietPollQuit(string $url): void
{
    $response = test()->get($url)->assertStatus(286);

    expect($response->getContent())->toBe('');

    $hx = array_filter(
        $response->headers->keys(),
        fn (string $key): bool => str_starts_with(strtoupper($key), 'HX-'),
    );

    expect($hx)->toBe([]);
}

it('answers a finished poll with an empty 286', function () {
    stopPollingProbeRoutes();

    expectQuietPollQuit('/_htmx-stop-polling');
});

it('exposes stop polling through the facade', function () {
    stopPollingProbeRoutes();

    expectQuietPollQuit('/_htmx-stop-polling-facade');
});
