<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx\Console\Commands;

use Illuminate\Console\Command;
use Imsus\LaravelHtmx\Console\AssetHasher;

/**
 * Print the SRI integrity block for the vendored client assets.
 *
 * After refreshing the files under public/, run this and paste the block
 * into the assets.integrity map in config — the values are derived from
 * the bytes, never hand-copied:
 *
 *     php artisan htmx:hash-assets
 */
class HashAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'htmx:hash-assets
        {--path= : Directory holding the vendored assets (defaults to the package public directory)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print SRI hashes for the vendored htmx assets.';

    /**
     * Derive hashes from the vendored bytes and print the config block.
     */
    public function handle(AssetHasher $hasher): int
    {
        $path = (string) ($this->option('path') ?: __DIR__.'/../../../public');

        if (! is_dir($path)) {
            $this->components->error("Asset directory does not exist: {$path}.");

            return self::FAILURE;
        }

        foreach ($hasher->hashes($path) as $file => $hash) {
            $this->line("'{$file}' => '{$hash}',");
        }

        $this->components->info('Paste the block above into the assets.integrity map.');

        return self::SUCCESS;
    }
}
