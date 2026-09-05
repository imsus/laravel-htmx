<?php

use App\Http\Controllers\Patterns\ClickToLoadController;
use App\Http\Controllers\Patterns\InfiniteScrollController;
use App\Http\Controllers\Patterns\LazyLoadController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');

/*
|--------------------------------------------------------------------------
| Pattern showcase pages
|--------------------------------------------------------------------------
|
| The homepage lists every pattern; each one gets its own detail page under
| /patterns/<slug>. All patterns drive the shared seeded workspace data
| (database/seeders/WorkspaceSeeder.php).
|
*/

// demo-latency slows htmx fetches (not page loads) outside production so
// swap transitions are visible; future pattern fragment routes inherit it.
Route::middleware('demo-latency')->prefix('patterns')->group(function (): void {
    Route::get('/click-to-load', [ClickToLoadController::class, 'index'])
        ->name('patterns.click-to-load');

    // htmx fragment: the next-older page of the demo issue's activity log,
    // plus a load-more button when more remain. The button on the page
    // swaps itself for this fragment.
    Route::get('/click-to-load/activity', [ClickToLoadController::class, 'activity'])
        ->name('patterns.click-to-load.activity');

    Route::get('/infinite-scroll', [InfiniteScrollController::class, 'index'])
        ->name('patterns.infinite-scroll');

    // htmx fragment: the next-older page of the demo issue's activity log,
    // plus a fresh sentinel when more remain. The sentinel on the page
    // swaps itself for this fragment once it scrolls into view; the final
    // page omits the sentinel and shows an end-of-feed marker instead.
    Route::get('/infinite-scroll/feed', [InfiniteScrollController::class, 'feed'])
        ->name('patterns.infinite-scroll.feed');

    Route::get('/lazy-load', [LazyLoadController::class, 'index'])
        ->name('patterns.lazy-load');

    // htmx fragment: the demo issue's full activity log. The skeleton on
    // the page fetches it with hx-trigger="load" and swaps it in; the
    // rows carry no trigger attributes, so the load never repeats.
    Route::get('/lazy-load/activity', [LazyLoadController::class, 'activity'])
        ->name('patterns.lazy-load.activity');
});
