<?php

declare(strict_types=1);

use Htmx\Htmx\Htmx;

if (! function_exists('htmx')) {
    function htmx(): Htmx
    {
        return app(Htmx::class);
    }
}
