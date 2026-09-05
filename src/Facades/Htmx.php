<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Your static-flavored shortcut to the htmx entry point.
 *
 *     use Imsus\LaravelHtmx\Facades\Htmx;
 *
 *     Htmx::isPartial();
 *     Htmx::headers()->retarget('#rows');
 *
 * Prefer the htmx() helper or $request macros in controllers — reach for
 * the facade in service classes and jobs where injection feels heavy.
 *
 * @see \Imsus\LaravelHtmx\Htmx
 *
 * @method static bool isHtmx(\Illuminate\Http\Request|null $request = null)
 * @method static bool isPartial(\Illuminate\Http\Request|null $request = null)
 * @method static bool isBoosted(\Illuminate\Http\Request|null $request = null)
 * @method static bool isHistoryRestore(\Illuminate\Http\Request|null $request = null)
 * @method static string|null source(\Illuminate\Http\Request|null $request = null)
 * @method static string|null target(\Illuminate\Http\Request|null $request = null)
 * @method static string|null triggerId(\Illuminate\Http\Request|null $request = null)
 * @method static string|null triggerName(\Illuminate\Http\Request|null $request = null)
 * @method static string|null requestType(\Illuminate\Http\Request|null $request = null)
 * @method static string|null ptag(\Illuminate\Http\Request|null $request = null)
 * @method static string|null prompt(\Illuminate\Http\Request|null $request = null)
 * @method static bool isPreloaded(\Illuminate\Http\Request|null $request = null)
 * @method static \Imsus\LaravelHtmx\HtmxHeaders headers()
 * @method static \Symfony\Component\HttpFoundation\StreamedResponse eventStream(iterable<string|array{data?: string|string[], event?: string, id?: string, retry?: int}> $events)
 * @method static \Illuminate\Http\Response errorPartial(\Illuminate\View\View $view, string $fragment, string $slot, int $status = 422)
 * @method static \Illuminate\Http\Response poll(\Illuminate\View\View $view, string $fragment, string $tag, \Illuminate\Http\Request|null $request = null)
 * @method static \Illuminate\Http\Response stopPolling()
 */
class Htmx extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Imsus\LaravelHtmx\Htmx::class;
    }
}
