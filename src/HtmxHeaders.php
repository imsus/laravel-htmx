<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Tell htmx what to do after it receives your response.
 *
 * Grab a fresh builder, describe the client-side behavior fluently, then
 * apply it to any response. Nothing is sent until you say so:
 *
 *     return htmx()->headers()
 *         ->retarget('#form-errors')
 *         ->reswap('innerHTML')
 *         ->applyTo(response($html, 422));
 *
 * Prefer the tiny Response macros for one-liners:
 *
 *     return response($html)->retarget('#rows');
 *
 * One builder per response, never shared. And a small htmx rule worth
 * knowing: htmx ignores response headers on 3xx redirects, so navigation
 * helpers like redirect() and location() stay 2xx and let the client
 * navigate itself.
 */
class HtmxHeaders
{
    /**
     * Every header method, in registration order.
     *
     * The provider derives the Response macros from this list, so adding
     * a header means adding one method plus one entry here.
     *
     * @var list<string>
     */
    public const array RESPONSE_MACROS = [
        'trigger',
        'retarget',
        'reswap',
        'reselect',
        'pushUrl',
        'replaceUrl',
        'redirect',
        'location',
        'refresh',
        'ptag',
    ];

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * Fire a client-side event after the swap.
     *
     * Pass a single name, or an array when you want to send detail along:
     *
     *     ->trigger('item-created');
     *     ->trigger(['item-created' => ['id' => $item->id]]);
     *
     * @param  string|array<string, mixed>  $events
     */
    public function trigger(string|array $events, ?string $target = null): static
    {
        if (is_string($events) && $target === null) {
            $this->headers['HX-Trigger'] = $events;
        } elseif (is_string($events)) {
            $this->headers['HX-Trigger'] = json_encode([$events => ['target' => $target]], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } else {
            $this->headers['HX-Trigger'] = json_encode($events, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        return $this;
    }

    /**
     * Swap the response into a different element.
     *
     *     ->retarget('#form-errors')
     */
    public function retarget(string $selector): static
    {
        $this->headers['HX-Retarget'] = $selector;

        return $this;
    }

    /**
     * Change how the response is swapped in.
     *
     * Any htmx swap strategy works: "innerHTML", "outerHTML", "beforeend",
     * "afterbegin", "none", and friends.
     *
     *     ->reswap('innerHTML')
     */
    public function reswap(string $option): static
    {
        $this->headers['HX-Reswap'] = $option;

        return $this;
    }

    /**
     * Narrow the swap to a fragment inside the response.
     *
     * Handy when the response carries a full document but only one piece
     * should land in the page.
     *
     *     ->reselect('#rows')
     */
    public function reselect(string $selector): static
    {
        $this->headers['HX-Reselect'] = $selector;

        return $this;
    }

    /**
     * Push a URL into the browser's history.
     *
     * Pass false to leave history alone, true to push the request URL.
     *
     *     ->pushUrl('/items?page=2')
     *     ->pushUrl(false)
     */
    public function pushUrl(string|bool $url): static
    {
        $this->headers['HX-Push-Url'] = $this->urlValue($url);

        return $this;
    }

    /**
     * Replace the current history entry instead of pushing a new one.
     *
     * Lovely for search-as-you-type, where every keystroke should not
     * become its own back-button stop.
     */
    public function replaceUrl(string|bool $url): static
    {
        $this->headers['HX-Replace-Url'] = $this->urlValue($url);

        return $this;
    }

    /**
     * Ask htmx to navigate to a new page, without a server redirect.
     *
     * The response itself stays 2xx — htmx ignores response headers on
     * real 3xx redirects, so this header does the navigating client-side.
     *
     *     ->redirect('/login')
     */
    public function redirect(string $url): static
    {
        $this->headers['HX-Redirect'] = $url;

        return $this;
    }

    /**
     * Navigate without a full reload, optionally with swap detail.
     *
     * Pass a path for the simple case, or the full htmx.ajax() options
     * array when you need control over method, target, and swap.
     *
     *     ->location('/items/1')
     *     ->location(['path' => '/items/1', 'target' => '#modal'])
     *
     * @param  string|array<string, mixed>  $value
     */
    public function location(string|array $value): static
    {
        $this->headers['HX-Location'] = is_string($value)
            ? $value
            : json_encode($value, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return $this;
    }

    /**
     * Ask htmx to reload the page, as if the user pressed refresh.
     */
    public function refresh(): static
    {
        $this->headers['HX-Refresh'] = 'true';

        return $this;
    }

    /**
     * Stamp the response with its poll tag for the hx-ptag extension.
     *
     * htmx stores the tag on the element and sends it back next poll, so
     * your controller can answer 304 when nothing changed and skip the
     * swap entirely.
     */
    public function ptag(string $tag): static
    {
        $this->headers['HX-PTag'] = $tag;

        return $this;
    }

    /**
     * Get the headers collected so far, ready to set on a response.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->headers;
    }

    /**
     * Apply the collected headers to any Symfony-compatible response.
     *
     * Returns the same instance it was given, so it slots neatly at the
     * end of a chain — and the concrete type survives for what follows:
     *
     *     return htmx()->headers()->refresh()->applyTo(response($html));
     *
     * @template T of SymfonyResponse
     *
     * @param  T  $response
     * @return T
     */
    public function applyTo(SymfonyResponse $response): SymfonyResponse
    {
        foreach ($this->headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }

    private function urlValue(string|bool $url): string
    {
        return is_string($url) ? $url : ($url ? 'true' : 'false');
    }
}
