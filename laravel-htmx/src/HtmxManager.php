<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Illuminate\Http\Request;

/**
 * Single owner of htmx request detection.
 *
 * Request macros, the htmx() helper, and the Htmx facade all reach this
 * manager — detection logic lives here exactly once.
 */
class HtmxManager
{
    /**
     * Any request carrying the HX-Request header (boosted is a subtype).
     */
    public function isHtmx(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader('HX-Request');
    }

    /**
     * A fragment-only request: htmx + partial type, excluding
     * history-restore and boosted requests (both resolve to a full page).
     */
    public function isPartial(?Request $request = null): bool
    {
        $request = $this->request($request);

        return $this->isHtmx($request)
            && $this->requestType($request) === 'partial'
            && ! $this->isHistoryRestore($request)
            && ! $this->isBoosted($request);
    }

    /**
     * Boosted navigation — defaults to a full page, never a partial.
     */
    public function isBoosted(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader('HX-Boosted');
    }

    /**
     * History-restore re-fetch — needs a full page even with HX-Request present.
     */
    public function isHistoryRestore(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader('HX-History-Restore-Request');
    }

    /**
     * Element that triggered the request, in tag#id format (e.g. button#save-btn).
     */
    public function source(?Request $request = null): ?string
    {
        return $this->header($request, 'HX-Source');
    }

    /**
     * Swap target of the request, in tag#id format (e.g. div#rows).
     */
    public function target(?Request $request = null): ?string
    {
        return $this->header($request, 'HX-Target');
    }

    /**
     * Normalized HX-Request-Type value (partial|full) or null when absent.
     */
    public function requestType(?Request $request = null): ?string
    {
        $value = $this->header($request, 'HX-Request-Type');

        return $value === null ? null : strtolower($value);
    }

    private function header(?Request $request, string $name): ?string
    {
        $value = $this->request($request)->headers->get($name);

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function request(?Request $request): Request
    {
        return $request ?? request();
    }
}
