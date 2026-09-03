<?php

declare(strict_types=1);

namespace Htmx\Htmx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Htmx\Htmx\Htmx
 */
class Htmx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Htmx\Htmx\Htmx::class;
    }
}
