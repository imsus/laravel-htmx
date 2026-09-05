<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Imsus\LaravelHtmx\Facades\Htmx;
use Symfony\Component\HttpFoundation\StreamedResponse;

function eventStreamBody(StreamedResponse $response): string
{
    ob_start();
    $response->sendContent();
    $body = ob_get_clean();

    assert(is_string($body));

    return $body;
}

it('streams html fragments as unnamed sse events', function () {
    Route::get('/_htmx-stream', function () {
        return htmx()->eventStream([
            '<p>one</p>',
            ['data' => '<p>two</p>'],
        ]);
    });

    $response = test()->get('/_htmx-stream')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/event-stream')
        ->and($response->headers->get('Cache-Control'))->toContain('no-store');

    $body = eventStreamBody($response->baseResponse);

    expect($body)->toBe("data: <p>one</p>\n\n"."data: <p>two</p>\n\n");
});

it('frames named events with id and retry fields', function () {
    Route::get('/_htmx-stream-named', function () {
        return htmx()->eventStream([
            ['event' => 'done', 'data' => 'Complete', 'id' => 'e42', 'retry' => 3000],
        ]);
    });

    $response = test()->get('/_htmx-stream-named')->assertOk();

    $body = eventStreamBody($response->baseResponse);

    expect($body)->toBe("event: done\ndata: Complete\nid: e42\nretry: 3000\n\n");
});

it('splits multiline data into one data line each', function () {
    Route::get('/_htmx-stream-multiline', function () {
        return htmx()->eventStream([
            ['data' => "<p>one</p>\n<p>two</p>"],
        ]);
    });

    $response = test()->get('/_htmx-stream-multiline')->assertOk();

    expect(eventStreamBody($response->baseResponse))->toBe("data: <p>one</p>\ndata: <p>two</p>\n\n");
});

it('reaches the stream through the facade', function () {
    Route::get('/_htmx-stream-facade', function () {
        return Htmx::eventStream(['<p>hi</p>']);
    });

    $response = test()->get('/_htmx-stream-facade')->assertOk();

    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');
});
