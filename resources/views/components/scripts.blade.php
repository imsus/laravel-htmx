@php
    $client = config('laravel-htmx.client', []);
    $assets = config('laravel-htmx.assets', []);
    $extensions = $assets['extensions'] ?? [];
    $integrity = $assets['integrity'] ?? [];
    $cdn = (bool) ($assets['cdnFallback'] ?? false);
    $cdnBase = rtrim((string) ($assets['cdnBase'] ?? ''), '/').'/';
    $files = array_merge(['htmx.min.js'], array_map(fn ($slug): string => $slug.'.js', array_keys($extensions)));
    // htmx approves extensions by registration name from a comma-separated
    // string — never a JSON array (registerExtension calls .split on it).
    $htmxConfig = array_merge($client, ['extensions' => implode(',', array_values($extensions))]);
@endphp
<meta name="htmx-config" content="{{ json_encode($htmxConfig, JSON_UNESCAPED_SLASHES) }}">
@foreach ($files as $file)
<script
    src="{{ $cdn ? $cdnBase.$file : asset('vendor/laravel-htmx/'.$file) }}"
    @if (! $cdn && isset($integrity[$file])) integrity="{{ $integrity[$file] }}" @endif
    crossorigin="anonymous"
    defer
></script>
@endforeach
