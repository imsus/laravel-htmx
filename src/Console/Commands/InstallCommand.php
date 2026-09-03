<?php

declare(strict_types=1);

namespace Htmx\Htmx\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'htmx:install';

    /**
     * The command description.
     */
    protected $description = 'Publish the Laravel Htmx config, views, and assets in one step.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->call('vendor:publish', ['--tag' => 'laravel-htmx-config']);
        $this->call('vendor:publish', ['--tag' => 'laravel-htmx-views']);
        $this->call('vendor:publish', ['--tag' => 'laravel-htmx-assets']);

        $this->components->info('Laravel Htmx installed: config, views, and assets published.');

        return self::SUCCESS;
    }
}
