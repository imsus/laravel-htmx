<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Htmx\Htmx\Console\Commands\InstallCommand;
use Htmx\Htmx\Console\Commands\UpgradeCheckCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        $this->registerResponseMacros();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-htmx');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'htmx');

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
            InstallCommand::class,
            UpgradeCheckCommand::class,
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

        Request::macro('ptag', function (): ?string {
            return app(HtmxManager::class)->ptag($this);
        });

        Request::macro('prompt', function (): ?string {
            return app(HtmxManager::class)->prompt($this);
        });

        Request::macro('isPreloaded', function (): bool {
            return app(HtmxManager::class)->isPreloaded($this);
        });
    }

    /**
     * Response macros apply one builder header and stay chainable.
     */
    private function registerResponseMacros(): void
    {
        Response::macro('trigger', function (string|array $events, ?string $target = null): Response {
            (new HtmxHeaders)->trigger($events, $target)->applyTo($this);

            return $this;
        });

        Response::macro('retarget', function (string $selector): Response {
            (new HtmxHeaders)->retarget($selector)->applyTo($this);

            return $this;
        });

        Response::macro('target', function (string $selector): Response {
            (new HtmxHeaders)->target($selector)->applyTo($this);

            return $this;
        });

        Response::macro('reswap', function (string $option): Response {
            (new HtmxHeaders)->reswap($option)->applyTo($this);

            return $this;
        });

        Response::macro('swap', function (string $option): Response {
            (new HtmxHeaders)->swap($option)->applyTo($this);

            return $this;
        });

        Response::macro('reselect', function (string $selector): Response {
            (new HtmxHeaders)->reselect($selector)->applyTo($this);

            return $this;
        });

        Response::macro('pushUrl', function (string|bool $url): Response {
            (new HtmxHeaders)->pushUrl($url)->applyTo($this);

            return $this;
        });

        Response::macro('push', function (string|bool $url): Response {
            (new HtmxHeaders)->push($url)->applyTo($this);

            return $this;
        });

        Response::macro('replaceUrl', function (string|bool $url): Response {
            (new HtmxHeaders)->replaceUrl($url)->applyTo($this);

            return $this;
        });

        Response::macro('replace', function (string|bool $url): Response {
            (new HtmxHeaders)->replace($url)->applyTo($this);

            return $this;
        });

        Response::macro('redirect', function (string $url): Response {
            (new HtmxHeaders)->redirect($url)->applyTo($this);

            return $this;
        });

        Response::macro('location', function (string|array $value): Response {
            (new HtmxHeaders)->location($value)->applyTo($this);

            return $this;
        });

        Response::macro('refresh', function (): Response {
            (new HtmxHeaders)->refresh()->applyTo($this);

            return $this;
        });

        Response::macro('ptag', function (string $tag): Response {
            (new HtmxHeaders)->ptag($tag)->applyTo($this);

            return $this;
        });
    }
}
