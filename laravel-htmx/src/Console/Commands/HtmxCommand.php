<?php

declare(strict_types=1);

namespace Htmx\Htmx\Console\Commands;

use Illuminate\Console\Command;

class HtmxCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'laravel-htmx:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package laravel-htmx.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Htmx placeholder command executed.');

        return self::SUCCESS;
    }
}
