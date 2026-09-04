<?php

declare(strict_types=1);

use Htmx\Htmx\Facades\Htmx as HtmxFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        $current = 'v42';

        if ($request->ptag() === $current) {
            return response('', 304);
        }

        return htmx()->headers()->ptag($current)->applyTo(response('<div>Breaking: htmx 4 released!</div>'));
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
        ->assertSee('Breaking: htmx 4 released!');
});

it('emits the poll tag through the response macro', function () {
    extensionProbeRoutes();

    test()->get('/_htmx-ptag-macro')
        ->assertOk()
        ->assertHeader('HX-PTag', 'v42');
});
