<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx\Tests;

use Imsus\LaravelHtmx\HtmxServiceProvider;
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
