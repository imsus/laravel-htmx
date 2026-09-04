<?php

declare(strict_types=1);

use Htmx\Htmx\Facades\Htmx as HtmxFacade;
use Htmx\Htmx\HtmxHeaders;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

function headerProbeRoutes(): void
{
    Route::get('/_htmx-headers-full', function () {
        return htmx()->headers()
            ->trigger(['saved' => ['target' => '#toast', 'message' => 'Done']])
            ->retarget('#rows')
            ->reswap('outerHTML')
            ->reselect('#rows > tr')
            ->pushUrl('/items?page=2')
            ->location(['path' => '/search', 'target' => '#results'])
            ->applyTo(response('<tr></tr>'));
    });

    Route::get('/_htmx-headers-full-b', function () {
        return htmx()->headers()
            ->trigger('saved')
            ->replaceUrl('/other')
            ->refresh()
            ->applyTo(response('<tr></tr>'));
    });

    Route::get('/_htmx-headers-targeted', function () {
        return htmx()->headers()
            ->trigger('saved', '#toast')
            ->pushUrl(false)
            ->replaceUrl(true)
            ->applyTo(response('<tr></tr>'));
    });

    Route::get('/_htmx-headers-macros', function () {
        return response('<tr></tr>')
            ->retarget('#rows')
            ->reswap('outerHTML')
            ->trigger('saved')
            ->refresh();
    });

    Route::get('/_htmx-headers-facade', function () {
        return HtmxFacade::headers()->reselect('#rows')->applyTo(response('ok'));
    });

    Route::get('/_htmx-headers-redirect', function () {
        return htmx()->headers()->redirect('/dashboard')->applyTo(response('Redirecting...'));
    });
}

it('emits all ten response headers with correct names and shapes', function () {
    headerProbeRoutes();

    test()->get('/_htmx-headers-full')->assertOk()
        ->assertHeader('HX-Trigger', '{"saved":{"target":"#toast","message":"Done"}}')
        ->assertHeader('HX-Retarget', '#rows')
        ->assertHeader('HX-Reswap', 'outerHTML')
        ->assertHeader('HX-Reselect', '#rows > tr')
        ->assertHeader('HX-Push-Url', '/items?page=2')
        ->assertHeader('HX-Location', '{"path":"/search","target":"#results"}');

    test()->get('/_htmx-headers-full-b')->assertOk()
        ->assertHeader('HX-Trigger', 'saved')
        ->assertHeader('HX-Replace-Url', '/other')
        ->assertHeader('HX-Refresh', 'true');
});

it('encodes targeted triggers and boolean urls', function () {
    headerProbeRoutes();

    test()->get('/_htmx-headers-targeted')->assertOk()
        ->assertHeader('HX-Trigger', '{"saved":{"target":"#toast"}}')
        ->assertHeader('HX-Push-Url', 'false')
        ->assertHeader('HX-Replace-Url', 'true');
});

it('derives one response macro per header method', function () {
    foreach (HtmxHeaders::RESPONSE_MACROS as $method) {
        expect(method_exists(HtmxHeaders::class, $method))->toBeTrue();
        expect(Response::hasMacro($method))->toBeTrue();
    }

    expect(HtmxHeaders::RESPONSE_MACROS)->toHaveCount(10);
});

it('chains response macros onto the response', function () {
    headerProbeRoutes();

    test()->get('/_htmx-headers-macros')->assertOk()
        ->assertHeader('HX-Retarget', '#rows')
        ->assertHeader('HX-Reswap', 'outerHTML')
        ->assertHeader('HX-Trigger', 'saved')
        ->assertHeader('HX-Refresh', 'true');
});

it('reaches the builder through the facade', function () {
    headerProbeRoutes();

    test()->get('/_htmx-headers-facade')->assertOk()
        ->assertHeader('HX-Reselect', '#rows');
});

it('keeps navigation headers on 2xx instead of 3xx', function () {
    headerProbeRoutes();

    test()->get('/_htmx-headers-redirect')
        ->assertStatus(200)
        ->assertHeader('HX-Redirect', '/dashboard');
});

it('never emits removed v4 headers', function () {
    headerProbeRoutes();

    $response = test()->get('/_htmx-headers-full')->assertOk();

    expect($response->headers->get('HX-Trigger-After-Swap'))->toBeNull()
        ->and($response->headers->get('HX-Trigger-After-Settle'))->toBeNull();
});
