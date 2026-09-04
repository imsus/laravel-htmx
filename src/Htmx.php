<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Read the current request the way htmx thinks about it, and tell htmx
 * what to do next.
 *
 * You never build this class yourself — pull it from the container, the
 * htmx() helper, or the Htmx facade and it is ready to go:
 *
 *     $request->isPartial();
 *     htmx()->isPartial();
 *     Htmx::isPartial();
 *
 * All three spellings ask this same singleton, so pick whichever reads
 * best where you are and move on to the fun part: building the response.
 *
 *     if (htmx()->isPartial()) {
 *         return view('items', ['items' => $items])->fragment('rows');
 *     }
 *
 *     return htmx()->headers()
 *         ->retarget('#form-errors')
 *         ->reswap('innerHTML')
 *         ->applyTo(response($html, 422));
 */
class Htmx
{
    /**
     * Every detection affordance, in registration order.
     *
     * The provider derives the Request macros from this list, so adding
     * a predicate means adding one method plus one entry here — the
     * macro, helper, and facade follow for free.
     *
     * @var list<string>
     */
    public const array REQUEST_MACROS = [
        'isHtmx',
        'isPartial',
        'isBoosted',
        'isHistoryRestore',
        'source',
        'target',
        'requestType',
        'ptag',
        'prompt',
        'isPreloaded',
    ];

    /**
     * Every HX-* wire name the module reads, in one place.
     *
     * @var array<string, string>
     */
    private const array HEADERS = [
        'isHtmx' => 'HX-Request',
        'isBoosted' => 'HX-Boosted',
        'isHistoryRestore' => 'HX-History-Restore-Request',
        'source' => 'HX-Source',
        'target' => 'HX-Target',
        'requestType' => 'HX-Request-Type',
        'ptag' => 'HX-PTag',
        'prompt' => 'HX-Prompt',
        'isPreloaded' => 'HX-Preloaded',
    ];

    /**
     * Start a fresh response header builder.
     *
     * Every call returns a brand new builder, so headers from one request
     * can never leak into the next. Build it, then apply it:
     *
     *     htmx()->headers()->retarget('#rows')->reswap('beforeend');
     */
    public function headers(): HtmxHeaders
    {
        return new HtmxHeaders;
    }

    /**
     * Answer a failed submission with the error Partial.
     *
     * The whole 422 pattern in one call: the named Fragment renders on
     * its own, the swap retargets the surviving error slot with
     * innerHTML — never outerHTML, so the slot lives to see the next
     * submit — and the error status keeps htmx honest:
     *
     *     if ($validator->fails()) {
     *         return htmx()->errorPartial(
     *             view('items', ['items' => [], 'errors' => $validator->errors()]),
     *             'form-errors',
     *             '#form-errors',
     *         );
     *     }
     *
     * Reach past it only for the exceptional targets: a 200 with
     * HX-Redirect when the failure leaves the page, another 4xx when
     * client code distinguishes failure kinds.
     */
    public function errorPartial(View $view, string $fragment, string $slot, int $status = 422): Response
    {
        return $this->headers()
            ->retarget($slot)
            ->reswap('innerHTML')
            ->applyTo(response($view->fragmentIf(true, $fragment), $status));
    }

    /**
     * Answer a poll with either fresh markup or a quiet 304.
     *
     * The whole hx-ptag loop in one call: when the element's stored tag
     * already matches, the empty 304 skips the swap entirely — and the
     * current markup is never rendered. Otherwise the named Fragment
     * renders stamped with its new tag:
     *
     *     return htmx()->poll(
     *         view('feed', ['items' => $items]),
     *         'news',
     *         $version,
     *     );
     */
    public function poll(View $view, string $fragment, string $tag, ?Request $request = null): Response
    {
        if ($this->ptag($request) === $tag) {
            return response('', 304);
        }

        return $this->headers()
            ->ptag($tag)
            ->applyTo(response($view->fragmentIf(true, $fragment)));
    }

    /**
     * Determine if the request was made by htmx.
     *
     * Any request carrying an HX-Request header counts — including boosted
     * navigation, which is just htmx wearing a full-page costume.
     *
     *     if (htmx()->isHtmx()) {
     *         // Skip the marketing hero, return the good stuff.
     *     }
     */
    public function isHtmx(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader(self::HEADERS['isHtmx']);
    }

    /**
     * Determine if the request wants a fragment, not a full page.
     *
     * This is the branch you will reach for in almost every controller.
     * It is true only when htmx asked for a partial fragment — boosted
     * navigation and history restores intentionally return false here,
     * because both need the full page with its layout.
     *
     *     return view('items', ['items' => $items])
     *         ->fragmentIf(htmx()->isPartial(), 'rows');
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
     * Determine if the request is boosted navigation.
     *
     * Boosted links and forms behave like normal navigation — the browser
     * swaps the whole body and merges the head — so always answer with a
     * full page, never a fragment.
     */
    public function isBoosted(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader(self::HEADERS['isBoosted']);
    }

    /**
     * Determine if the request is a history restore.
     *
     * When htmx misses its history cache it re-fetches the page from the
     * server. Even though HX-Request is present, the user needs the whole
     * page back, not a fragment floating without its layout.
     */
    public function isHistoryRestore(?Request $request = null): bool
    {
        return $this->request($request)->hasHeader(self::HEADERS['isHistoryRestore']);
    }

    /**
     * Get the element that triggered the request.
     *
     * Returns the tag and id htmx observed, like "button#save-btn".
     * Lovely for logging, or for deciding which of several buttons on a
     * page kicked things off.
     */
    public function source(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['source']);
    }

    /**
     * Get the swap target for the request.
     *
     * Returns the tag and id htmx will swap into, like "div#rows".
     * Null when htmx is using its default target resolution.
     */
    public function target(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['target']);
    }

    /**
     * Get the normalized request type.
     *
     * Returns "partial" or "full", lowercased for you, or null when htmx
     * did not say. Most apps should prefer isPartial() over reading this
     * directly — it already folds in the boosted and history exceptions.
     */
    public function requestType(?Request $request = null): ?string
    {
        $value = $this->header($request, self::HEADERS['requestType']);

        return $value === null ? null : strtolower($value);
    }

    /**
     * Get the poll tag stored by the hx-ptag extension.
     *
     * Read the stored tag when you need it raw. For the full loop —
     * compare, answer 304, or stamp the fresh tag — reach for poll():
     *
     *     return htmx()->poll($view, 'news', $currentTag, $request);
     */
    public function ptag(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['ptag']);
    }

    /**
     * Get the answer typed into the hx-prompt extension's dialog.
     *
     * Null when no prompt was shown. Just a string — validate it like any
     * other user input.
     */
    public function prompt(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['prompt']);
    }

    /**
     * Determine if the request is a speculative preload.
     *
     * Preloads may never be consumed, so keep them cheap and free of side
     * effects. Think reads, never writes.
     *
     *     if (htmx()->isPreloaded()) {
     *         return view('preview', ['item' => $item])->fragment('card');
     *     }
     */
    public function isPreloaded(?Request $request = null): bool
    {
        return strtolower((string) $this->header($request, self::HEADERS['isPreloaded'])) === 'true';
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
