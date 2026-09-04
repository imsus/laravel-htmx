<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Htmx\Htmx\Console\Commands\HashAssetsCommand;
use Htmx\Htmx\Console\Commands\InstallCommand;
use Htmx\Htmx\Console\Commands\UpgradeCheckCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;

/**
 * Wire htmx into your Laravel app, the Laravel way.
 *
 * Once registered — which happens automatically via package discovery —
 * you get an expressive Request and Response vocabulary, a config file,
 * Blade views, vendored client assets, and two Artisan commands.
 * Nothing to bootstrap by hand.
 */
class HtmxServiceProvider extends ServiceProvider
{
    /**
     * Publish groups, shared with the install command.
     *
     * Renaming a tag here renames it for both publishes() calls below
     * and htmx:install — neither side re-spells the strings.
     *
     * @var array<string, string>
     */
    public const array PUBLISH_TAGS = [
        'config' => 'laravel-htmx-config',
        'views' => 'laravel-htmx-views',
        'assets' => 'laravel-htmx-assets',
    ];

    /**
     * Register the htmx entry point in the container.
     *
     * A single stateless singleton sits behind the helper, the facade,
     * and every macro, so there is exactly one definition of "is this
     * a partial?" to learn and trust.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-htmx.php', 'laravel-htmx');

        $this->app->singleton(Htmx::class);
        $this->app->singleton(HtmxAssets::class);
    }

    /**
     * Boot request macros, response macros, views, and publishable assets.
     */
    public function boot(): void
    {
        $this->registerRequestMacros();
        $this->registerResponseMacros();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'htmx');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-htmx.php' => config_path('laravel-htmx.php'),
        ], ['laravel-htmx', self::PUBLISH_TAGS['config']]);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/htmx'),
        ], ['laravel-htmx', self::PUBLISH_TAGS['views']]);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-htmx'),
        ], ['laravel-htmx', self::PUBLISH_TAGS['assets']]);

        $this->commands([
            InstallCommand::class,
            UpgradeCheckCommand::class,
            HashAssetsCommand::class,
        ]);
    }

    /**
     * Give every request a fluent htmx vocabulary.
     *
     * Registrations derive from Htmx::REQUEST_MACROS — one shared,
     * stateless singleton behind them — so the detection logic lives in
     * exactly one place:
     *
     *     $request->isPartial();
     *     $request->target();
     */
    private function registerRequestMacros(): void
    {
        $htmx = $this->app->make(Htmx::class);

        foreach (Htmx::REQUEST_MACROS as $method) {
            Request::macro($method, function () use ($htmx, $method) {
                return $htmx->$method($this);
            });
        }
    }

    /**
     * Give every response a one-line way to speak htmx.
     *
     * Registrations derive from HtmxHeaders::RESPONSE_MACROS — each macro
     * forwards its arguments to a fresh builder and applies it, staying
     * chainable with the rest of your response building:
     *
     *     return response($html)->retarget('#rows')->reswap('beforeend');
     */
    private function registerResponseMacros(): void
    {
        $htmx = $this->app->make(Htmx::class);

        foreach (HtmxHeaders::RESPONSE_MACROS as $method) {
            Response::macro($method, function (...$args) use ($htmx, $method): Response {
                return $htmx->headers()->$method(...$args)->applyTo($this);
            });
        }
    }
}
