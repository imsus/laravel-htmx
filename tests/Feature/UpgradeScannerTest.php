<?php

declare(strict_types=1);

use Imsus\LaravelHtmx\Console\UpgradeScanner;

function scan(string $contents): array
{
    return app(UpgradeScanner::class)->scanContents($contents);
}

it('flags every rule on a minimal snippet', function (string $snippet, string $needle) {
    $found = scan($snippet);

    expect($found)->not->toBe([])
        ->and(implode(' ', array_column(array_merge(...array_values($found)), 'match')))->toContain($needle);
})->with([
    'removed attribute' => ['<div hx-vars="{}">', 'hx-vars'],
    'removed header' => ['HX-Trigger-After-Swap: saved', 'HX-Trigger-After-Swap'],
    'xhr-era event' => ['htmx:xhr:loadend', 'htmx:xhr:loadend'],
    'abort event' => ['htmx:abort', 'htmx:abort'],
    'direct extension include' => ['<script src="/ext/json-enc.js">', 'ext/json-enc.js'],
    'renamed inherit' => ['<div hx-inherit="*">', 'hx-inherit'],
    'stale selector' => ['from:(closest div)', 'from:('],
]);

it('passes migrated markup with no findings', function () {
    expect(scan('<x-htmx::scripts />
<div hx-get="/rows" hx-target="#rows:inherited" hx-swap="outerHTML">
    <button hx-delete="/items/1" hx-include="closest form">Delete</button>
</div>'))->toBe([]);
});

it('groups findings by 1-based line number', function () {
    $found = scan("<div>clean</div>\n<div hx-ext=\"foo\">\n<span>clean</span>");

    expect(array_keys($found))->toBe([2])
        ->and($found[2][0]['match'])->toBe('hx-ext')
        ->and($found[2][0]['advice'])->toContain('removed in htmx 4');
});

it('dedupes repeat matches on one line', function () {
    $found = scan('<div hx-vars="a" hx-params="b">');

    expect($found[1])->toHaveCount(2);
});

it('resolves a single file, an empty missing dir, and unreadable paths to lists', function () {
    $scanner = app(UpgradeScanner::class);

    $dir = sys_get_temp_dir().'/htmx-scan-'.uniqid();
    mkdir($dir);
    file_put_contents($dir.'/legacy.blade.php', '<div hx-vars="{}">');
    file_put_contents($dir.'/notes.txt', '<div hx-vars="{}">');

    expect($scanner->files($dir, '.blade.php'))->toBe([realpath($dir.'/legacy.blade.php')])
        ->and($scanner->files($dir.'/legacy.blade.php', '.blade.php'))->toBe([$dir.'/legacy.blade.php'])
        ->and($scanner->files($dir.'/legacy.blade.php', '.twig'))->toBe([])
        ->and($scanner->files($dir.'/missing', '.blade.php'))->toBe([])
        ->and($scanner->scanFile($dir.'/missing.blade.php'))->toBe([]);

    expect($scanner->scanFile($dir.'/legacy.blade.php'))->not->toBe([]);

    array_map(unlink(...), glob($dir.'/*'));
    rmdir($dir);
});
