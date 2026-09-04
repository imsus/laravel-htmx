<?php

declare(strict_types=1);

namespace Htmx\Htmx\Console;

use Symfony\Component\Finder\Finder;

/**
 * Derive SRI hashes from vendored bytes on disk.
 *
 * The integrity map in config stays the carrier ADR-0002 requires —
 * this is how its values are born, so a refresh never means
 * hand-copying hashes again. Filenames sort, JavaScript only:
 *
 *     app(AssetHasher::class)->hashes($directory);
 *     // ['a.js' => 'sha384-…', …]
 */
class AssetHasher
{
    /**
     * @return array<string, string> filename => "sha384-…" value
     */
    public function hashes(string $directory): array
    {
        $hashes = [];

        foreach ((new Finder)->files()->in($directory)->name('*.js')->sortByName() as $file) {
            $hashes[$file->getFilename()] = 'sha384-'.base64_encode(
                hash('sha384', (string) file_get_contents($file->getRealPath()), true),
            );
        }

        return $hashes;
    }
}
