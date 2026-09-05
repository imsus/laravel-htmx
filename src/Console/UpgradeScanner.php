<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx\Console;

use Symfony\Component\Finder\Finder;

/**
 * Find the htmx 2.x habits that quietly break under htmx 4.
 *
 * Pure findings in, pure findings out — no console, no exit codes. Feed
 * it a path or a raw string of Blade and get back exactly what to fix,
 * grouped by line number:
 *
 *     app(UpgradeScanner::class)->scanContents('<div hx-vars="…">');
 *     // [12 => [['hx-vars', 'removed in htmx 4: …']]]
 *
 * The Artisan command renders these findings; tests assert them directly.
 */
class UpgradeScanner
{
    /**
     * Pattern => remediation. The matched snippet travels with each finding.
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
     * Resolve the sorted scan targets for a path and extension.
     *
     * A file path scans itself when it matches; anything else resolves
     * to the matching files beneath it, or an empty list.
     *
     * @return list<string>
     */
    public function files(string $path, string $ext): array
    {
        if (is_file($path)) {
            return str_ends_with($path, $ext) ? [$path] : [];
        }

        if (! is_dir($path)) {
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
     * Scan one file, grouped by 1-based line number.
     *
     * @return array<int, list<array{match: string, advice: string}>>
     */
    public function scanFile(string $file): array
    {
        if (! is_file($file) || ! is_readable($file)) {
            return [];
        }

        $contents = file_get_contents($file);

        if (! is_string($contents)) {
            return [];
        }

        return $this->scanContents($contents);
    }

    /**
     * Scan raw markup, grouped by 1-based line number.
     *
     * The seam tests reach for: no filesystem, no console, just the
     * RULES table answering honestly about a string.
     *
     * @return array<int, list<array{match: string, advice: string}>>
     */
    public function scanContents(string $contents): array
    {
        $findings = [];

        foreach (explode("\n", $contents) as $index => $line) {
            foreach (self::RULES as $pattern => $advice) {
                if (preg_match_all($pattern, $line, $matches) === false || $matches[0] === []) {
                    continue;
                }

                foreach (array_unique($matches[0]) as $match) {
                    $findings[$index + 1][] = ['match' => $match, 'advice' => $advice];
                }
            }
        }

        return $findings;
    }
}
