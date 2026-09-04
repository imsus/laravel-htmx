<?php

declare(strict_types=1);

namespace Htmx\Htmx\Console\Commands;

use Htmx\Htmx\HtmxServiceProvider;
use Illuminate\Console\Command;

/**
 * Publish everything htmx needs in one warm, welcoming step.
 *
 * Config, Blade views, and the pinned htmx 4 client assets land where
 * Laravel expects them — no manual copying, no wondering what you missed:
 *
 *     php artisan htmx:install
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'htmx:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the Laravel Htmx config, views, and assets in one step.';

    /**
     * Publish config, views, and client assets to the application.
     */
    public function handle(): int
    {
        foreach (HtmxServiceProvider::PUBLISH_TAGS as $tag) {
            $this->call('vendor:publish', ['--tag' => $tag]);
        }

        $this->components->info('Laravel Htmx installed: config, views, and assets published.');

        return self::SUCCESS;
    }
}
