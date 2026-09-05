<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'));

// Live htmx round-trip. The home page button posts here; the package's
// response macro answers with the fragment and an HX-Trigger event.
Route::post('/hello', function (Request $request) {
    $name = $request->string('name')->toString() ?: 'htmx';

    return response('<p>Hello, '.e($name).'! This fragment came from a POST.</p>')
        ->trigger(['greeted' => ['name' => $name]]);
});
