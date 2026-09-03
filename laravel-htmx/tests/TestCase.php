<?php

declare(strict_types=1);

namespace Htmx\Htmx\Tests;

use Htmx\Htmx\HtmxServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            HtmxServiceProvider::class,
        ];
    }
}
