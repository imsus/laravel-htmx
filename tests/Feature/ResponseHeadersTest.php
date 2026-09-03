<?php

declare(strict_types=1);

use Htmx\Htmx\Facades\Htmx as HtmxFacade;
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

    Route::get('/_htmx-headers-alias-original', function () {
        return htmx()->headers()
            ->retarget('#rows')
            ->reswap('outerHTML')
            ->pushUrl('/items')
            ->replaceUrl('/other')
            ->applyTo(response('x'));
    });

    Route::get('/_htmx-headers-alias-aliased', function () {
        return htmx()->headers()
            ->target('#rows')
            ->swap('outerHTML')
            ->push('/items')
            ->replace('/other')
            ->applyTo(response('x'));
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

it('emits all nine v4 headers with correct names and shapes', function () {
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

it('proves alias equivalence on emitted headers', function () {
    headerProbeRoutes();

    $headers = ['HX-Retarget', 'HX-Reswap', 'HX-Push-Url', 'HX-Replace-Url'];

    $original = test()->get('/_htmx-headers-alias-original')->assertOk();
    $aliased = test()->get('/_htmx-headers-alias-aliased')->assertOk();

    foreach ($headers as $name) {
        expect($aliased->headers->get($name))->toBe($original->headers->get($name));
    }

    expect($original->headers->get('HX-Retarget'))->toBe('#rows');
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
