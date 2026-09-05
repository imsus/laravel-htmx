<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx\Console\Commands;

use Illuminate\Console\Command;
use Imsus\LaravelHtmx\Console\UpgradeScanner;

/**
 * Find the htmx 2.x habits that quietly break under htmx 4.
 *
 * htmx 4 removed a handful of attributes, headers, and XHR-era events —
 * and renamed a few more. This command scans your Blade views offline
 * (lovely in CI) and tells you exactly what to fix, file by file:
 *
 *     php artisan htmx:upgrade-check --path=resources/views
 *
 * It exits with a non-zero status while findings remain, so your pipeline
 * can hold the line until the markup is ready.
 */
class UpgradeCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'htmx:upgrade-check
        {--path= : File or directory to scan (defaults to the app views path)}
        {--ext=.blade.php : Only scan files with this extension}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flag htmx 2.x markup patterns that break under htmx 4.';

    /**
     * Scan views for htmx 2.x patterns and report what to migrate.
     */
    public function handle(UpgradeScanner $scanner): int
    {
        $path = (string) ($this->option('path') ?: resource_path('views'));
        $ext = (string) ($this->option('ext') ?: '.blade.php');

        $files = $scanner->files($path, $ext);

        if ($files === []) {
            $this->components->warn("No *{$ext} files found under {$path}.");

            return self::SUCCESS;
        }

        $issues = 0;
        $dirty = 0;

        foreach ($files as $file) {
            $found = $scanner->scanFile($file);

            if ($found === []) {
                continue;
            }

            $dirty++;
            $issues += count($found);

            foreach ($found as $line => $entries) {
                foreach ($entries as $entry) {
                    $this->line("{$file}:{$line}: [{$entry['match']}] {$entry['advice']}");
                }
            }
        }

        if ($issues === 0) {
            $this->components->info('No htmx 2.x patterns found.');

            return self::SUCCESS;
        }

        $this->components->error("{$issues} htmx 2.x pattern(s) in {$dirty} file(s) — migrate before shipping htmx 4.");

        return self::FAILURE;
    }
}
