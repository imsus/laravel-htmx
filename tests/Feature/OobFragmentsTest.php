<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Imsus\LaravelHtmx\Facades\Htmx as HtmxFacade;

function oobProbeRoutes(): void
{
    view()->addLocation(__DIR__.'/../Fixtures/views');

    Route::patch('/_htmx-todos', function () {
        return htmx()->oob(
            view('oob-page', ['todo' => 'Milk', 'left' => 3]),
            ['todo', 'todo-count'],
        );
    });

    Route::patch('/_htmx-todos-facade', function () {
        return HtmxFacade::oob(
            view('oob-page', ['todo' => 'Milk', 'left' => 3]),
            ['todo-count', 'todo'],
        );
    });

    Route::patch('/_htmx-todos-empty', function () {
        return htmx()->oob(view('oob-page', ['todo' => 'Milk', 'left' => 3]), []);
    });
}

it('returns several fragments in one response, in the given order', function () {
    oobProbeRoutes();

    $body = test()->patch('/_htmx-todos')->assertOk()->getContent();
    assert(is_string($body));

    expect($body)->toContain('<li id="todo-1" hx-swap-oob="true">Milk</li>', '<span id="todo-count" hx-swap-oob="true">')
        ->and(strpos($body, 'todo-1') < strpos($body, 'todo-count'))->toBeTrue();
});

it('respects the requested fragment order through the facade', function () {
    oobProbeRoutes();

    $body = test()->patch('/_htmx-todos-facade')->assertOk()->getContent();
    assert(is_string($body));

    expect(strpos($body, 'todo-count') < strpos($body, 'todo-1'))->toBeTrue();
});

it('answers an empty fragment list with an empty body', function () {
    oobProbeRoutes();

    $body = test()->patch('/_htmx-todos-empty')->assertOk()->getContent();

    expect($body)->toBe('');
});
