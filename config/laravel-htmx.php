<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Pinned htmx version
    |--------------------------------------------------------------------------
    |
    | Client assets are vendored at this version (see public/ and ADR-0002).
    | The scripts component and the CDN fallback both pin to it.
    |
    */

    'version' => '4.0.0',

    /*
    |--------------------------------------------------------------------------
    | Strict htmx 4 client defaults
    |--------------------------------------------------------------------------
    |
    | Shipped as-is per ADR-0001: explicit inheritance (no implicit opt-in),
    | minimal swap exclusions (error responses swap by default), a 60s
    | fetch timeout, and server re-fetch on history misses.
    |
    */

    'client' => [
        'implicitInheritance' => false,
        'noSwap' => [204, 304],
        'defaultTimeout' => 60000,
        'history' => [
            'enabled' => true,
            'cacheSize' => 0,
            'refreshOnMiss' => true,
        ],

        // 2.x-compatible values as commented opt-ins for incremental migration.
        // 'implicitInheritance' => true,     // restore implicit inheritance
        // 'noSwap' => [204, 304, 400, 422, 500], // keep error responses out of swaps
        // 'defaultTimeout' => 0,             // disable the fetch timeout
        // 'history' => ['enabled' => true, 'cacheSize' => 10, 'refreshOnMiss' => false],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets
    |--------------------------------------------------------------------------
    |
    | Vendored-first delivery per ADR-0002: the scripts component emits local
    | script tags with SRI hashes. Flip cdnFallback to degrade gracefully to
    | the pinned CDN build for the version above. extensions is the explicit
    | allowlist — htmx 4 loads extensions via direct script tags, so the
    | package owns the list.
    |
    */

    'assets' => [
        'cdnFallback' => false,
        // Slim core or max build. 'htmx.min.js' is the core plus the
        // extensions listed below as separate scripts. 'htmax.js' is
        // htmx bundled with the popular extensions (sse, ws, preload,
        // browser-indicator, download, pending, targets, live, upsert,
        // alpine-compat, history-cache) in one ~6x larger file — the
        // scripts component then emits only htmax.js, never the
        // standalone extension files alongside it. Unknown values fall
        // back to the slim core.
        'core' => 'htmx.min.js',
        'extensions' => [
            // Vendored script slug => extension registration name. htmx 4
            // approves extensions by registration name against a
            // comma-separated allowlist (see scripts component), and names
            // are inconsistent upstream ('ptag' vs 'hx-prompt'), so map them
            // explicitly. htmax.js is the max core build, not an extension.
            'hx-history-cache' => 'history-cache',
            'hx-prompt' => 'hx-prompt',
            'hx-ptag' => 'ptag',
        ],
        'integrity' => [
            'htmx.min.js' => 'sha384-BvJpBiO8Kh31EqtJe5DRIeWrHWnCGkwytKs9NKFi86Hhw96dEqdEMzZDeK9iEGTc',
            'htmx.esm.js' => 'sha384-fh3WoeSX2U60P2sV0M8Y6xvhTB5cpTb+AAF28P71BzGUZ/KI7QYrzKfwTFD/OCr6',
            'htmax.js' => 'sha384-wrTFbAj755gAdIPPR9n4aAVMkLWkzxjr1Zj+8D4kgr2App4h9doO6SXBH9wpivij',
            'hx-history-cache.js' => 'sha384-uAKHk6uA+kfE8F1USwRsZc28yieFgkLRfa0m79Gm7svsqxdy9voYQuCcwiCf3zVR',
            'hx-prompt.js' => 'sha384-AIEfbt9mlav7oV3t1s5OXJO0iBtizXGCWEFfhVtdrIfhGEbW4ni0JgbIRiDHrcRO',
            'hx-ptag.js' => 'sha384-R20vL2ppRMMKzN1SVZdVpoeW1yH54eSH8rX3jz9NqpuWNqzwR5ngAqhCcKnK/b1s',
        ],
    ],

];
