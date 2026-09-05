@php($assets = app(\Imsus\LaravelHtmx\HtmxAssets::class))
<meta name="htmx-config" content="{{ json_encode($assets->clientConfig(), JSON_UNESCAPED_SLASHES) }}">
@foreach ($assets->scripts() as $script)
<script
    src="{{ $script['src'] }}"
    @if ($script['integrity']) integrity="{{ $script['integrity'] }}" @endif
    crossorigin="anonymous"
    defer
></script>
@endforeach
