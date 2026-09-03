<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Illuminate\Http\Request;

/**
 * Primary htmx entry point (resolved via the htmx() helper and Htmx facade).
 *
 * Detection delegates to the shared HtmxManager — no logic is duplicated here.
 * Response-header methods land with issue 02 on this same class.
 */
class Htmx
{
    public function __construct(private readonly HtmxManager $manager) {}

    public function manager(): HtmxManager
    {
        return $this->manager;
    }

    /**
     * Fresh response-header builder (never shared between requests).
     */
    public function headers(): HtmxHeaders
    {
        return new HtmxHeaders;
    }

    public function isHtmx(?Request $request = null): bool
    {
        return $this->manager->isHtmx($request);
    }

    public function isPartial(?Request $request = null): bool
    {
        return $this->manager->isPartial($request);
    }

    public function isBoosted(?Request $request = null): bool
    {
        return $this->manager->isBoosted($request);
    }

    public function isHistoryRestore(?Request $request = null): bool
    {
        return $this->manager->isHistoryRestore($request);
    }

    public function source(?Request $request = null): ?string
    {
        return $this->manager->source($request);
    }

    public function target(?Request $request = null): ?string
    {
        return $this->manager->target($request);
    }

    public function requestType(?Request $request = null): ?string
    {
        return $this->manager->requestType($request);
    }
}
