<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Illuminate\Contracts\Config\Repository;

/**
 * The vendored htmx client, translated into markup-ready data.
 *
 * Config holds the raw ingredients — version, client defaults, extension
 * slugs, SRI hashes — and this module owns every derivation the scripts
 * view needs: which files to emit, the comma-separated allowlist htmx
 * splits on, the combined client config payload, and per-file URLs with
 * their integrity strings. The view only loops.
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
     * Get the combined client config payload for the htmx-config meta tag.
     *
     * Strict v4 defaults plus the extension allowlist as one
     * comma-separated string — never a JSON array, since the client
     * approves extensions by splitting that string.
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
     * Get the extension allowlist exactly as htmx expects it.
     *
     * Registration names (not file slugs — upstream names are
     * inconsistent, hence the explicit map in config), joined into the
     * single comma-separated string the client splits on.
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
     * The layout emits the core build plus extensions on its own; the
     * ESM and max builds stay out of the markup until asked for —
     * this is the asking:
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
