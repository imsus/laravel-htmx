<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx;

use Illuminate\Contracts\Config\Repository;

/**
 * Everything the scripts component needs, derived from config.
 *
 * Which files to load, the extension allowlist, the client config for
 * the meta tag, and each file's URL with its integrity hash — the view
 * only loops:
 *
 *     app(HtmxAssets::class)->scripts();
 *     // [['src' => '…/htmx.min.js', 'integrity' => 'sha384-…'], …]
 */
class HtmxAssets
{
    /**
     * Config root every read below hangs off.
     */
    private const string CONFIG = 'laravel-htmx';

    public function __construct(private readonly Repository $config) {}

    /**
     * Get the client config payload for the htmx-config meta tag.
     *
     * Your strict v4 defaults plus the extension allowlist, merged into
     * the one array the meta tag encodes.
     *
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        /** @var array<string, mixed> $client */
        $client = $this->config->get(self::CONFIG.'.client', []);

        return array_merge($client, ['extensions' => $this->extensionAllowlist()]);
    }

    /**
     * Get one row per script tag the layout should emit.
     *
     * Core build first, then one file per allowlisted extension slug.
     * Local URLs carry their SRI hash; CDN fallback URLs carry none.
     *
     * @return list<array{src: string, integrity: ?string}>
     */
    public function scripts(): array
    {
        $cdn = (bool) $this->config->get(self::CONFIG.'.assets.cdnFallback', false);

        return array_map(
            fn (string $file): array => [
                'src' => $this->url($file),
                'integrity' => $cdn ? null : $this->integrity($file),
            ],
            $this->scriptFiles(),
        );
    }

    /**
     * Get the core build filename to emit.
     *
     * Either vendored core build; anything else falls back to the slim
     * core so the layout never emits a file without an SRI hash.
     */
    public function coreFile(): string
    {
        $core = (string) $this->config->get(self::CONFIG.'.assets.core', 'htmx.min.js');

        return in_array($core, ['htmx.min.js', 'htmax.js'], true) ? $core : 'htmx.min.js';
    }

    /**
     * Get the vendored filenames to emit, core build first.
     *
     * With the max build as core, extensions ride inside htmax.js, so
     * only it is emitted — standalone extension scripts alongside it
     * would register twice.
     *
     * @return list<string>
     */
    public function scriptFiles(): array
    {
        if ($this->coreFile() === 'htmax.js') {
            return ['htmax.js'];
        }

        /** @var array<string, string> $extensions */
        $extensions = $this->config->get(self::CONFIG.'.assets.extensions', []);

        return array_merge([$this->coreFile()], array_map(
            fn (string $slug): string => $slug.'.js',
            array_keys($extensions),
        ));
    }

    /**
     * Get the extensions htmx should approve, as one comma-separated string.
     *
     * These are registration names, which don't always match the file
     * slugs — the config map keeps the pairing explicit.
     */
    public function extensionAllowlist(): string
    {
        /** @var array<string, string> $extensions */
        $extensions = $this->config->get(self::CONFIG.'.assets.extensions', []);

        return implode(',', array_values($extensions));
    }

    /**
     * Get one opt-in row for a vendored file outside the auto set.
     *
     * The layout already emits the core build plus extensions; the ESM
     * and max builds stay out of your markup until you ask for one:
     *
     *     $esm = app(HtmxAssets::class)->variant('htmx.esm.js');
     *
     * Null for unknown files. Local URLs carry their SRI hash.
     *
     * @return array{src: string, integrity: ?string}|null
     */
    public function variant(string $file): ?array
    {
        if ($this->integrity($file) === null) {
            return null;
        }

        $cdn = (bool) $this->config->get(self::CONFIG.'.assets.cdnFallback', false);

        return [
            'src' => $this->url($file),
            'integrity' => $cdn ? null : $this->integrity($file),
        ];
    }

    private function url(string $file): string
    {
        if ($this->config->get(self::CONFIG.'.assets.cdnFallback', false)) {
            return "https://cdn.jsdelivr.net/npm/htmx.org@{$this->version()}/{$file}";
        }

        return asset('vendor/laravel-htmx/'.$file);
    }

    private function integrity(string $file): ?string
    {
        /** @var array<string, string> $map */
        $map = $this->config->get(self::CONFIG.'.assets.integrity', []);

        return $map[$file] ?? null;
    }

    private function version(): string
    {
        return (string) $this->config->get(self::CONFIG.'.version', '4.0.0');
    }
}
