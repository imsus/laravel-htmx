<?php

declare(strict_types=1);

namespace Htmx\Htmx;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Fluent builder for the nine htmx 4 response headers.
 *
 * Always fresh (never shared): reach it via htmx()->headers(), the Htmx
 * facade, or the Response macros. Navigation helpers only set headers —
 * htmx ignores response headers on 3xx, so these responses stay 2xx and the
 * client navigates itself.
 */
class HtmxHeaders
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * Single HX-Trigger header: plain names, or a JSON object for detail.
     * A $target with a string event nests as {"event":{"target":"..."}}.
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

    public function retarget(string $selector): static
    {
        $this->headers['HX-Retarget'] = $selector;

        return $this;
    }

    /**
     * Alias of retarget().
     */
    public function target(string $selector): static
    {
        return $this->retarget($selector);
    }

    public function reswap(string $option): static
    {
        $this->headers['HX-Reswap'] = $option;

        return $this;
    }

    /**
     * Alias of reswap().
     */
    public function swap(string $option): static
    {
        return $this->reswap($option);
    }

    public function reselect(string $selector): static
    {
        $this->headers['HX-Reselect'] = $selector;

        return $this;
    }

    public function pushUrl(string|bool $url): static
    {
        $this->headers['HX-Push-Url'] = $this->urlValue($url);

        return $this;
    }

    /**
     * Alias of pushUrl().
     */
    public function push(string|bool $url): static
    {
        return $this->pushUrl($url);
    }

    public function replaceUrl(string|bool $url): static
    {
        $this->headers['HX-Replace-Url'] = $this->urlValue($url);

        return $this;
    }

    /**
     * Alias of replaceUrl().
     */
    public function replace(string|bool $url): static
    {
        return $this->replaceUrl($url);
    }

    /**
     * Client-side full-page reload to $url (stays 2xx; the client navigates).
     */
    public function redirect(string $url): static
    {
        $this->headers['HX-Redirect'] = $url;

        return $this;
    }

    /**
     * Ajax navigation without reload: plain path or htmx.ajax() options.
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

    public function refresh(): static
    {
        $this->headers['HX-Refresh'] = 'true';

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->headers;
    }

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
