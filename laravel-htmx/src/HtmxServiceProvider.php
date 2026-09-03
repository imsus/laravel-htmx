<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Htmx\Htmx\Console\Commands\HtmxCommand;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class HtmxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-htmx.php', 'laravel-htmx');

        $this->app->singleton(HtmxManager::class);
        $this->app->singleton(Htmx::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRequestMacros();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-htmx');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-htmx.php' => config_path('laravel-htmx.php'),
        ], ['laravel-htmx', 'laravel-htmx-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-htmx'),
        ], ['laravel-htmx', 'laravel-htmx-views']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/laravel-htmx'),
        ], ['laravel-htmx', 'laravel-htmx-assets']);

        $this->commands([
            HtmxCommand::class,
        ]);
    }

    /**
     * Request macros delegate to the shared manager — no duplicated logic.
     */
    private function registerRequestMacros(): void
    {
        Request::macro('isHtmx', function (): bool {
            return app(HtmxManager::class)->isHtmx($this);
        });

        Request::macro('isPartial', function (): bool {
            return app(HtmxManager::class)->isPartial($this);
        });

        Request::macro('isBoosted', function (): bool {
            return app(HtmxManager::class)->isBoosted($this);
        });

        Request::macro('isHistoryRestore', function (): bool {
            return app(HtmxManager::class)->isHistoryRestore($this);
        });

        Request::macro('source', function (): ?string {
            return app(HtmxManager::class)->source($this);
        });

        Request::macro('target', function (): ?string {
            return app(HtmxManager::class)->target($this);
        });

        Request::macro('requestType', function (): ?string {
            return app(HtmxManager::class)->requestType($this);
        });
    }
}
