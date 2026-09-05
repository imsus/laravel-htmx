<?php

declare(strict_types=1);

use Imsus\LaravelHtmx\Console\AssetHasher;

it('derives sorted sri hashes from bytes on disk', function () {
    $dir = sys_get_temp_dir().'/htmx-assets-'.uniqid();
    mkdir($dir);
    file_put_contents($dir.'/b.js', 'console.log("b");');
    file_put_contents($dir.'/a.js', 'console.log("a");');
    file_put_contents($dir.'/notes.txt', 'not javascript');

    $hashes = app(AssetHasher::class)->hashes($dir);

    expect(array_keys($hashes))->toBe(['a.js', 'b.js'])
        ->and($hashes['a.js'])->toStartWith('sha384-');

    file_put_contents($dir.'/a.js', 'console.log("tampered");');

    expect(app(AssetHasher::class)->hashes($dir)['a.js'])->not->toBe($hashes['a.js']);

    array_map(unlink(...), glob($dir.'/*'));
    rmdir($dir);
});

it('prints the integrity block for the vendored assets', function () {
    $this->artisan('htmx:hash-assets', ['--path' => __DIR__.'/../../public'])
        ->assertSuccessful()
        ->expectsOutputToContain("'htmx.min.js' => 'sha384-");
});

it('refuses a missing asset directory', function () {
    $this->artisan('htmx:hash-assets', ['--path' => __DIR__.'/missing'])
        ->assertFailed();
});

it('fails loudly on a missing directory instead of hashing nothing', function () {
    expect(fn () => app(AssetHasher::class)->hashes(__DIR__.'/missing'))
        ->toThrow(InvalidArgumentException::class);
});
