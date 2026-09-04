<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;
use Workbench\App\Http\Middleware\DemoDelay;

// The workbench app boots from the default skeleton, so the demo views load
// by file path instead of by name. Registering the directory as well so the
// views can share partials via @include.
view()->addLocation(__DIR__.'/../resources/views');

$demoView = fn (array $data): View => view()->file(__DIR__.'/../resources/views/demo.blade.php', $data);

// Fragment + 422 + history-restore demo (see resources/views/demo.blade.php).
Route::get('/', function (Request $request) use ($demoView) {
    return $demoView([
        'items' => session('items', ['Apples', 'Oranges']),
        'errors' => new ViewErrorBag,
    ])->fragmentIf($request->isPartial(), 'rows');
})->middleware(DemoDelay::class);

Route::post('/items', function (Request $request) use ($demoView) {
    $validator = Validator::make($request->all(), ['name' => 'required|min:3']);

    if ($validator->fails()) {
        return htmx()
            ->headers()
            ->retarget('#form-errors')
            ->reswap('innerHTML')
            ->applyTo(response(
                $demoView(['items' => [], 'errors' => $validator->errors()])
                    ->fragmentIf(true, 'form-errors'),
                422,
            ));
    }

    $items = [...session('items', ['Apples', 'Oranges']), $request->input('name')];
    session(['items' => $items]);

    return $demoView(['items' => $items, 'errors' => new ViewErrorBag])
        ->fragmentIf($request->isPartial(), 'rows');
})->middleware(DemoDelay::class);

Route::delete('/items', function (Request $request) use ($demoView) {
    session(['items' => []]);

    return $demoView(['items' => [], 'errors' => new ViewErrorBag])
        ->fragmentIf($request->isPartial(), 'rows');
})->middleware(DemoDelay::class);

// Pattern gallery: server communication through the package API
// (see resources/views/patterns.blade.php).
$patternsView = fn (array $data): View => view()->file(__DIR__.'/../resources/views/patterns.blade.php', $data);

$patternItems = fn (): array => session('items', ['Apples', 'Oranges']);

Route::get('/patterns', function (Request $request) use ($patternsView, $patternItems) {
    return $patternsView([
        'items' => $patternItems(),
        'errors' => new ViewErrorBag,
        'q' => (string) $request->query('q', ''),
    ]);
})->middleware(DemoDelay::class);

Route::get('/patterns/search', function (Request $request) use ($patternsView, $patternItems) {
    $q = (string) $request->query('q', '');

    $items = array_values(array_filter(
        $patternItems(),
        fn (string $item): bool => str_contains(strtolower($item), strtolower($q)),
    ));

    $response = response($patternsView([
        'items' => $items,
        'errors' => new ViewErrorBag,
        'q' => $q,
    ])->fragmentIf($request->isPartial(), 'results'));

    if ($request->isPartial() && $q !== '') {
        htmx()->headers()->pushUrl('/patterns?q='.urlencode($q))->applyTo($response);
    }

    return $response;
})->middleware(DemoDelay::class);

Route::delete('/patterns/items/{name}', function (Request $request, string $name) use ($patternItems) {
    session(['items' => array_values(array_filter(
        $patternItems(),
        fn (string $item): bool => $item !== $name,
    ))]);

    $reason = $request->prompt();
    $message = $reason !== null && $reason !== '' ? "{$name} deleted ({$reason})" : "{$name} deleted";

    return htmx()
        ->headers()
        ->trigger(['itemDeleted' => ['message' => $message]])
        ->applyTo(response('', 200));
})->middleware(DemoDelay::class);

Route::post('/patterns/validate', function (Request $request) use ($patternsView, $patternItems) {
    $validator = Validator::make($request->all(), ['name' => 'required|min:3']);

    $headers = htmx()->headers()->retarget('#v-errors')->reswap('innerHTML');

    if ($validator->fails()) {
        return $headers->applyTo(response(
            $patternsView(['items' => $patternItems(), 'errors' => $validator->errors(), 'q' => ''])
                ->fragmentIf(true, 'v-errors'),
            422,
        ));
    }

    return $headers->applyTo(response(
        $patternsView(['items' => $patternItems(), 'errors' => new ViewErrorBag, 'q' => ''])
            ->fragmentIf(true, 'v-errors'),
    ));
})->middleware(DemoDelay::class);

Route::get('/patterns/news', function (Request $request) use ($patternsView, $patternItems) {
    $current = 'v'.count($patternItems());

    if ($request->ptag() === $current) {
        return response('', 304);
    }

    return htmx()->headers()->ptag($current)->applyTo(response(
        $patternsView(['items' => $patternItems(), 'errors' => new ViewErrorBag, 'q' => ''])
            ->fragmentIf(true, 'news'),
    ));
})->middleware(DemoDelay::class);
