<?php

declare(strict_types=1);

namespace Workbench\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoDelay
{
    /**
     * Artificial latency for htmx requests so loading states (indicator,
     * htmx-request, swapping) stay visible in instant local demos.
     * Full-page loads pass through undelayed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isHtmx()) {
            usleep(600_000);
        }

        return $response;
    }
}
