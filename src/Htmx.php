<?php

declare(strict_types=1);

namespace Imsus\LaravelHtmx;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Every detection method, also available as a Request macro.
     *
     * One list backs all three spellings — `$request->isPartial()`,
     * `htmx()->isPartial()`, `Htmx::isPartial()` — so each name below
     * works everywhere.
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
        'triggerId',
        'triggerName',
        'requestType',
        'ptag',
        'prompt',
        'isPreloaded',
    ];

    /**
     * The HX-* header behind each detection method, in one place.
     *
     * @var array<string, string>
     */
    private const array HEADERS = [
        'isHtmx' => 'HX-Request',
        'isBoosted' => 'HX-Boosted',
        'isHistoryRestore' => 'HX-History-Restore-Request',
        'source' => 'HX-Source',
        'target' => 'HX-Target',
        'triggerId' => 'HX-Trigger',
        'triggerName' => 'HX-Trigger-Name',
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
     * Reach past it only for the exceptions: a 200 with
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
     * Answer with several fragments at once, each bound for its own element.
     *
     * One request, many swaps: every named Fragment renders from the same
     * view and the bodies concatenate in the order you list them. The
     * markup owns the targeting — each fragment root carries hx-swap-oob,
     * so htmx routes every piece to its element:
     *
     *     return htmx()->oob(
     *         view('todos', ['todo' => $todo, 'left' => $left]),
     *         ['todo', 'todo-count'],
     *     );
     *
     * An empty list answers an empty 200 — polling loops compose these
     * calls without special-casing. Unknown names fail exactly the way
     * core fragment rendering fails: loudly, not silently skipped.
     *
     * @param  list<string>  $names
     */
    public function oob(View $view, array $names): Response
    {
        return response($view->fragments($names));
    }

    /**
     * Answer a poll with either fresh markup or a quiet 304.
     *
     * The whole hx-ptag loop in one call: when the element's stored tag
     * already matches, the empty 304 skips the swap — the fragment never
     * renders. Otherwise the named Fragment renders stamped with its
     * new tag:
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
     * Tell a poller the show is over.
     *
     * htmx polling honors the 286 status by quietly retiring the poll —
     * no swap, no error, no further requests. Empty body, no headers:
     * there is nothing to swap, so nothing is said:
     *
     *     if ($feed->finished()) {
     *         return htmx()->stopPolling();
     *     }
     */
    public function stopPolling(): Response
    {
        return response('', 286);
    }

    /**
     * Answer with a stream of HTML updates for the hx-sse extension.
     *
     * Each entry becomes one SSE block: a plain string is an unnamed
     * event swapped as HTML, while an array may carry event, id, and
     * retry fields around its data. Multiline data splits into one
     * `data:` line per line, and every block ends with a blank line:
     *
     *     return htmx()->eventStream([
     *         '<p>warming up</p>',
     *         ['data' => '<p>done</p>', 'id' => 'e42'],
     *         ['event' => 'done', 'data' => 'Complete'],
     *     ]);
     *
     * Unnamed `data:` blocks swap per the request's hx-target/hx-swap;
     * named `event:` blocks dispatch DOM events instead. Send an `id:`
     * and the client resumes from `Last-Event-ID` after a disconnect.
     *
     * @param  iterable<string|array{data?: string|string[], event?: string, id?: string, retry?: int}>  $events
     */
    public function eventStream(iterable $events): StreamedResponse
    {
        $frames = [];

        foreach ($events as $event) {
            $frames[] = $this->sseFrame($event);
        }

        return new StreamedResponse(
            function () use ($frames): void {
                foreach ($frames as $frame) {
                    echo $frame;
                }

                flush();
            },
            200,
            [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-store',
                'X-Accel-Buffering' => 'no',
            ],
        );
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
     * Get the id of the element that fired the request.
     *
     * Null when the trigger carries no id. Pair it with triggerName()
     * when several controls share one endpoint and the server must tell
     * them apart.
     */
    public function triggerId(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['triggerId']);
    }

    /**
     * Get the name of the element that fired the request.
     *
     * Null when the trigger carries no name — most swaps do not, so
     * treat this as a hint for shared endpoints, not a branch condition
     * for layout decisions.
     */
    public function triggerName(?Request $request = null): ?string
    {
        return $this->header($request, self::HEADERS['triggerName']);
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

    /**
     * Format one SSE block, always terminated by a blank line.
     *
     * @param  string|array{data?: string|string[], event?: string, id?: string, retry?: int}  $event
     */
    private function sseFrame(string|array $event): string
    {
        if (is_string($event)) {
            $event = ['data' => $event];
        }

        $lines = [];

        if (isset($event['event']) && $event['event'] !== '') {
            $lines[] = "event: {$event['event']}";
        }

        $data = $event['data'] ?? null;
        $dataLines = is_array($data) ? $data : explode("\n", (string) $data);

        foreach ($dataLines as $line) {
            $lines[] = 'data: '.rtrim($line, "\r");
        }

        foreach (['id', 'retry'] as $field) {
            if (isset($event[$field]) && $event[$field] !== '') {
                $lines[] = "{$field}: {$event[$field]}";
            }
        }

        return implode("\n", $lines)."\n\n";
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
