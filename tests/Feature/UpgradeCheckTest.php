<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

function upgradeOutput(string $path, string $ext = '.blade.php'): array
{
    $code = Artisan::call('htmx:upgrade-check', ['--path' => $path, '--ext' => $ext]);

    return [$code, Artisan::output()];
}

function upgradePath(string $name): string
{
    return __DIR__.'/../Fixtures/upgrade/'.$name;
}

it('flags 2.x renames, inheritance, events, and extension includes', function () {
    [$code, $output] = upgradeOutput(upgradePath('legacy.blade.php'));

    expect($code)->toBe(1);

    foreach (['hx-vars', 'hx-ext', 'hx-inherit', 'from:(', 'htmx:xhr:loadend', 'HX-Trigger-After-Swap', 'ext/json-enc.js'] as $needle) {
        expect($output)->toContain($needle);
    }
});

it('passes a migrated template', function () {
    [$code, $output] = upgradeOutput(upgradePath('clean.blade.php'));

    expect($code)->toBe(0)
        ->and($output)->toContain('No htmx 2.x patterns found.');
});

it('scans a directory honoring the extension filter', function () {
    [$dirty] = upgradeOutput(__DIR__.'/../Fixtures/upgrade', '.blade.php');
    [$clean] = upgradeOutput(__DIR__.'/../Fixtures/upgrade', '.twig');

    expect($dirty)->toBe(1)
        ->and($clean)->toBe(0);
});
