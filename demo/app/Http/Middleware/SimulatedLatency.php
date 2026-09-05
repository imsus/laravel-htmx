<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SimulatedLatency
{
    /**
     * Slow htmx fetches down so the demo's swap transitions are visible.
     *
     * On a local server the round trip is near-instant, which makes the
     * load-more animation imperceptible. Only htmx requests (HX-Request
     * header) are delayed — full page navigations stay fast — and only
     * outside production; the deployed demo is never slowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction() && $request->header('HX-Request')) {
            usleep(random_int(100, 300) * 1000);
        }

        return $next($request);
    }
}
