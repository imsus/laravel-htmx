<?php

declare(strict_types=1);

namespace Htmx\Htmx\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * Scans Blade markup for htmx 2.x patterns that break under v4 semantics.
 *
 * Pure-PHP so it runs offline in CI: renames, inheritance, XHR-era events,
 * and direct extension includes. Findings print as path:line entries and
 * the command fails when any are present.
 */
class UpgradeCheckCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'htmx:upgrade-check
        {--path= : File or directory to scan (defaults to the app views path)}
        {--ext=.blade.php : Only scan files with this extension}';

    /**
     * The command description.
     */
    protected $description = 'Flag htmx 2.x markup patterns that break under htmx 4.';

    /**
     * Pattern => remediation. The matched snippet is echoed with each finding.
     *
     * @var array<string, string>
     */
    private const array RULES = [
        '/\bhx-(ext|request|vars|params)\b/' => 'removed in htmx 4: hx-ext/hx-request/hx-vars/hx-params no longer exist (extensions load via vendored <script> tags, see <x-htmx::scripts />)',
        '/HX-Trigger-After-(Swap|Settle)/' => 'removed in htmx 4: use the single HX-Trigger header with JSON detail instead',
        '/htmx:xhr:[a-z-]+/' => 'removed XHR-era event: htmx 4 uses fetch(), so htmx:xhr:* events never fire (listen to htmx:* instead)',
        '/\bhtmx:abort\b/' => 'removed XHR-era event: htmx 4 uses fetch(), so htmx:abort never fires',
        '/<script[^>]+src=["\'][^"\']*\/ext\/[^"\']*["\']/' => 'direct extension include: vendor the extension at 4.0.0 and list it in the laravel-htmx assets.extensions allowlist',
        '/\bhx-inherit\b/' => 'renamed in htmx 4: mark explicit inheritance with the :inherited modifier instead of hx-inherit',
        '/\b(?:from|target):\(/' => 'stale selector syntax: quote from:/target: selectors containing spaces or commas with single quotes',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = (string) ($this->option('path') ?: resource_path('views'));
        $ext = (string) ($this->option('ext') ?: '.blade.php');

        $files = $this->scanTargets($path, $ext);

        if ($files === []) {
            $this->components->warn("No *{$ext} files found under {$path}.");

            return self::SUCCESS;
        }

        $issues = 0;
        $dirty = 0;

        foreach ($files as $file) {
            $found = $this->scanFile($file);

            if ($found === []) {
                continue;
            }

            $dirty++;
            $issues += count($found);

            foreach ($found as $line => $entries) {
                foreach ($entries as [$match, $advice]) {
                    $this->line("{$file}:{$line}: [{$match}] {$advice}");
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

    /**
     * @return list<string>
     */
    private function scanTargets(string $path, string $ext): array
    {
        if (is_file($path)) {
            return str_ends_with($path, $ext) ? [$path] : [];
        }

        if (! is_dir($path)) {
            $this->components->warn("Scan path does not exist: {$path}.");

            return [];
        }

        $files = [];
        foreach ((new Finder)->files()->in($path)->name('*'.$ext) as $file) {
            $files[] = (string) $file->getRealPath();
        }
        sort($files);

        return $files;
    }

    /**
     * @return array<int, list<array{string, string}>>
     */
    private function scanFile(string $file): array
    {
        $contents = file_get_contents($file);

        if (! is_string($contents)) {
            return [];
        }

        $findings = [];

        foreach (explode("\n", $contents) as $index => $line) {
            foreach (self::RULES as $pattern => $advice) {
                if (preg_match_all($pattern, $line, $matches) === false || $matches[0] === []) {
                    continue;
                }

                foreach (array_unique($matches[0]) as $match) {
                    $findings[$index + 1][] = [$match, $advice];
                }
            }
        }

        return $findings;
    }
}
