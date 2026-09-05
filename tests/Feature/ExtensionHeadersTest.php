<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Imsus\LaravelHtmx\Facades\Htmx as HtmxFacade;

function extensionProbeRoutes(): void
{
    Route::get('/_htmx-ptag-probe', function (Request $request) {
        return response()->json([
            'macro' => ['ptag' => $request->ptag(), 'prompt' => $request->prompt(), 'isPreloaded' => $request->isPreloaded()],
            'helper' => ['ptag' => htmx()->ptag(), 'prompt' => htmx()->prompt(), 'isPreloaded' => htmx()->isPreloaded()],
            'facade' => ['ptag' => HtmxFacade::ptag(), 'prompt' => HtmxFacade::prompt(), 'isPreloaded' => HtmxFacade::isPreloaded()],
        ]);
    });

    Route::get('/_htmx-news', function (Request $request) {
        view()->addLocation(__DIR__.'/../Fixtures/views');

        return htmx()->poll(
            view('demo-page', ['items' => ['Apples'], 'errors' => new ViewErrorBag]),
            'rows',
            'v42',
            $request,
        );
    });

    Route::get('/_htmx-ptag-macro', function () {
        return response('ok')->ptag('v42');
    });
}

it('reads the poll tag and prompt answer across all surfaces', function () {
    extensionProbeRoutes();

    $via = test()->get('/_htmx-ptag-probe', ['HX-PTag' => '"v42"', 'HX-Prompt' => 'typo fix', 'HX-Preloaded' => 'true'])
        ->assertOk()
        ->json();

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface])->toBe(['ptag' => '"v42"', 'prompt' => 'typo fix', 'isPreloaded' => true]);
    }
});

it('returns null poll tag and prompt when absent', function () {
    extensionProbeRoutes();

    $via = test()->get('/_htmx-ptag-probe')->assertOk()->json();

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface])->toBe(['ptag' => null, 'prompt' => null, 'isPreloaded' => false]);
    }
});

it('skips the swap with 304 when the poll tag is current', function () {
    extensionProbeRoutes();

    test()->get('/_htmx-news', ['HX-PTag' => 'v42'])
        ->assertStatus(304)
        ->assertSee('', false);

    test()->get('/_htmx-news')
        ->assertOk()
        ->assertHeader('HX-PTag', 'v42')
        ->assertSee('<ul id="rows">', false)
        ->assertDontSee('<h1>Items</h1>', false);
});

it('emits the poll tag through the response macro', function () {
    extensionProbeRoutes();

    test()->get('/_htmx-ptag-macro')
        ->assertOk()
        ->assertHeader('HX-PTag', 'v42');
});

it('folds the preload flag case', function () {
    extensionProbeRoutes();

    $via = test()->get('/_htmx-ptag-probe', ['HX-Preloaded' => 'True'])
        ->assertOk()
        ->json();

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface]['isPreloaded'])->toBeTrue();
    }
});
