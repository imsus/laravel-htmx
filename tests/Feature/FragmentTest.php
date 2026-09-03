<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;

function fragmentProbeRoutes(): void
{
    view()->addLocation(__DIR__.'/../Fixtures/views');

    Route::get('/_htmx-page', function () {
        return view('demo-page', ['items' => ['Apples', 'Oranges'], 'errors' => new ViewErrorBag])
            ->fragmentIf(htmx()->isPartial(), 'rows');
    });

    Route::post('/_htmx-items', function (Request $request) {
        $validator = Validator::make($request->all(), ['name' => 'required|min:3']);

        if ($validator->fails()) {
            return htmx()
                ->headers()
                ->retarget('#form-errors')
                ->reswap('innerHTML')
                ->applyTo(response(
                    view('demo-page', ['items' => [], 'errors' => $validator->errors()])
                        ->fragmentIf(true, 'form-errors'),
                    422,
                ));
        }

        return view('demo-page', ['items' => [$request->input('name')], 'errors' => new ViewErrorBag])
            ->fragmentIf(htmx()->isPartial(), 'rows');
    });
}

it('returns the rows partial for htmx requests', function () {
    fragmentProbeRoutes();

    test()->get('/_htmx-page', ['HX-Request' => 'true', 'HX-Request-Type' => 'partial'])
        ->assertOk()
        ->assertSee('<ul id="rows">', false)
        ->assertSee('Apples')
        ->assertDontSee('<h1>Items</h1>', false);
});

it('returns the full page otherwise, with the partial check as the only branch', function () {
    fragmentProbeRoutes();

    test()->get('/_htmx-page')
        ->assertOk()
        ->assertSee('<h1>Items</h1>', false)
        ->assertSee('<ul id="rows">', false);
});

it('returns the full page for history-restore requests', function () {
    fragmentProbeRoutes();

    test()->get('/_htmx-page', [
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
        'HX-History-Restore-Request' => 'true',
    ])->assertOk()->assertSee('<h1>Items</h1>', false);
});

it('returns the error partial with 422 that swaps into the target', function () {
    fragmentProbeRoutes();

    test()->post('/_htmx-items', ['name' => 'x'], ['HX-Request' => 'true', 'HX-Request-Type' => 'partial'])
        ->assertStatus(422)
        ->assertHeader('HX-Retarget', '#form-errors')
        ->assertHeader('HX-Reswap', 'innerHTML')
        ->assertSee('<p>The name field must be at least 3 characters.</p>', false)
        ->assertDontSee('<h1>Items</h1>', false);
});

it('keeps the error slot across repeated failures', function () {
    fragmentProbeRoutes();

    // innerHTML (not the trigger's outerHTML) leaves #form-errors in place,
    // so a second invalid submit still has a target to swap into.
    foreach (['x', 'y'] as $name) {
        test()->post('/_htmx-items', ['name' => $name], ['HX-Request' => 'true', 'HX-Request-Type' => 'partial'])
            ->assertStatus(422)
            ->assertHeader('HX-Retarget', '#form-errors')
            ->assertHeader('HX-Reswap', 'innerHTML');
    }
});

it('returns the rows partial on valid input', function () {
    fragmentProbeRoutes();

    test()->post('/_htmx-items', ['name' => 'Pears'], ['HX-Request' => 'true', 'HX-Request-Type' => 'partial'])
        ->assertOk()
        ->assertSee('<ul id="rows">', false)
        ->assertSee('Pears');
});
