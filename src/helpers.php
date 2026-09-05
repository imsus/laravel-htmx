<?php

declare(strict_types=1);

use Imsus\LaravelHtmx\Htmx;

if (! function_exists('htmx')) {
    /**
     * Get the htmx entry point for the current request.
     *
     * The whole package in one little helper — ask what htmx wants, then
     * tell it what to do next:
     *
     *     if (htmx()->isPartial()) {
     *         return view('items')->fragment('rows');
     *     }
     *
     *     return htmx()->headers()
     *         ->retarget('#rows')
     *         ->applyTo(response($html));
     */
    function htmx(): Htmx
    {
        return app(Htmx::class);
    }
}
