<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Imsus\LaravelHtmx\Facades\Htmx as HtmxFacade;
use Imsus\LaravelHtmx\Htmx;

function probeRoutes(): void
{
    Route::get('/_htmx-probe', function (Request $request) {
        $via = [
            'macro' => [
                'isHtmx' => $request->isHtmx(),
                'isPartial' => $request->isPartial(),
                'isBoosted' => $request->isBoosted(),
                'isHistoryRestore' => $request->isHistoryRestore(),
                'source' => $request->source(),
                'target' => $request->target(),
                'currentUrl' => $request->currentUrl(),
                'requestType' => $request->requestType(),
            ],
            'helper' => [
                'isHtmx' => htmx()->isHtmx(),
                'isPartial' => htmx()->isPartial(),
                'isBoosted' => htmx()->isBoosted(),
                'isHistoryRestore' => htmx()->isHistoryRestore(),
                'source' => htmx()->source(),
                'target' => htmx()->target(),
                'currentUrl' => htmx()->currentUrl(),
                'requestType' => htmx()->requestType(),
            ],
            'facade' => [
                'isHtmx' => HtmxFacade::isHtmx(),
                'isPartial' => HtmxFacade::isPartial(),
                'isBoosted' => HtmxFacade::isBoosted(),
                'isHistoryRestore' => HtmxFacade::isHistoryRestore(),
                'source' => HtmxFacade::source(),
                'target' => HtmxFacade::target(),
                'currentUrl' => HtmxFacade::currentUrl(),
                'requestType' => HtmxFacade::requestType(),
            ],
        ];

        return response()->json($via);
    });
}

function probe(array $headers = []): array
{
    probeRoutes();

    return test()->get('/_htmx-probe', $headers)->assertOk()->json();
}

function expectSurfacesAgree(array $via, array $expected): void
{
    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface])->toBe($expected);
    }
}

it('treats a plain request as a full page', function () {
    expectSurfacesAgree(probe(), [
        'isHtmx' => false,
        'isPartial' => false,
        'isBoosted' => false,
        'isHistoryRestore' => false,
        'source' => null,
        'target' => null,
        'currentUrl' => null,
        'requestType' => null,
    ]);
});

it('detects a partial request', function () {
    expectSurfacesAgree(probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
    ]), [
        'isHtmx' => true,
        'isPartial' => true,
        'isBoosted' => false,
        'isHistoryRestore' => false,
        'source' => null,
        'target' => null,
        'currentUrl' => null,
        'requestType' => 'partial',
    ]);
});

it('does not treat an htmx request without a partial type as partial', function () {
    $via = probe(['HX-Request' => 'true']);

    expectSurfacesAgree($via, [
        'isHtmx' => true,
        'isPartial' => false,
        'isBoosted' => false,
        'isHistoryRestore' => false,
        'source' => null,
        'target' => null,
        'currentUrl' => null,
        'requestType' => null,
    ]);
});

it('does not treat a full-type htmx request as partial', function () {
    $via = probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'full',
    ]);

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface]['isHtmx'])->toBeTrue();
        expect($via[$surface]['isPartial'])->toBeFalse();
        expect($via[$surface]['requestType'])->toBe('full');
    }
});

it('resolves history-restore requests to a full page', function () {
    expectSurfacesAgree(probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
        'HX-History-Restore-Request' => 'true',
    ]), [
        'isHtmx' => true,
        'isPartial' => false,
        'isBoosted' => false,
        'isHistoryRestore' => true,
        'source' => null,
        'target' => null,
        'currentUrl' => null,
        'requestType' => 'partial',
    ]);
});

it('resolves boosted requests to a full page', function () {
    expectSurfacesAgree(probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
        'HX-Boosted' => 'true',
    ]), [
        'isHtmx' => true,
        'isPartial' => false,
        'isBoosted' => true,
        'isHistoryRestore' => false,
        'source' => null,
        'target' => null,
        'currentUrl' => null,
        'requestType' => 'partial',
    ]);
});

it('reads source and target in tag#id format', function () {
    $via = probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
        'HX-Source' => 'button#save-btn',
        'HX-Target' => 'div#rows',
    ]);

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface]['source'])->toBe('button#save-btn');
        expect($via[$surface]['target'])->toBe('div#rows');
        expect($via[$surface]['isPartial'])->toBeTrue();
    }
});

it('registers every detection affordance as a request macro', function () {
    foreach (Htmx::REQUEST_MACROS as $method) {
        expect(method_exists(Htmx::class, $method))->toBeTrue()
            ->and(Request::hasMacro($method))->toBeTrue();
    }
});

it('maps every detection affordance to a wire name except the composite', function () {
    $headers = (new ReflectionClass(Htmx::class))->getConstant('HEADERS');

    foreach (Htmx::REQUEST_MACROS as $method) {
        if ($method === 'isPartial') {
            continue;
        }

        expect($headers)->toHaveKey($method);
    }
});

it('treats blank headers as absent and folds header case', function () {
    $via = probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'PARTIAL',
        'HX-Target' => '',
    ]);

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface]['target'])->toBeNull()
            ->and($via[$surface]['requestType'])->toBe('partial')
            ->and($via[$surface]['isPartial'])->toBeTrue();
    }
});

it('reads the browser URL htmx reports', function () {
    $via = probe([
        'HX-Request' => 'true',
        'HX-Request-Type' => 'partial',
        'HX-Current-URL' => 'https://example.com/items',
    ]);

    foreach (['macro', 'helper', 'facade'] as $surface) {
        expect($via[$surface]['currentUrl'])->toBe('https://example.com/items');
    }
});
