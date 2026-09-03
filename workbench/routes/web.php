<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

Route::get('/', function () {
    return view('welcome');
});

// Fragment + 422 + history-restore demo (see resources/views/demo.blade.php).
// The workbench app boots from the default skeleton, so the demo view loads
// by file path instead of by name.
$demoView = fn (array $data): View => view()->file(__DIR__.'/../resources/views/demo.blade.php', $data);

Route::get('/demo', function (Request $request) use ($demoView) {
    return $demoView([
        'items' => session('items', ['Apples', 'Oranges']),
        'errors' => new ViewErrorBag,
    ])->fragmentIf($request->isPartial(), 'rows');
});

Route::post('/demo/items', function (Request $request) use ($demoView) {
    $validator = Validator::make($request->all(), ['name' => 'required|min:3']);

    if ($validator->fails()) {
        return htmx()
            ->headers()
            ->retarget('#form-errors')
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
});
