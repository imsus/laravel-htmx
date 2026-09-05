@extends('layouts.app')

@section('title', 'htmx 4 patterns · Laravel showcase')

@section('content')
    <p class="eyebrow">imsus/laravel-htmx &middot; htmx 4.0.0</p>
    <h1>htmx 4 patterns on Laravel</h1>
    <p class="lede">
        Working recreations of the official htmx 4 patterns, rendered by
        Laravel. The demo data models a small Linear-style workspace &mdash;
        projects, issues, and their activity logs &mdash; and every pattern
        drives that same dataset the way it would in a real product.
    </p>

    <section aria-labelledby="patterns-title">
        <h2 id="patterns-title" class="section-title">Patterns</h2>

        <a class="pattern-card" href="{{ route('patterns.click-to-load') }}">
            <span class="copy">
                <span class="name">Click to Load</span>
                <span class="blurb">
                    Page through an issue&rsquo;s activity log: the newest
                    events render first, and &ldquo;Show earlier
                    activity&rdquo; fetches older ones a page at a time.
                </span>
            </span>
            <span class="go" aria-hidden="true">&rarr;</span>
        </a>

        <a class="pattern-card" href="{{ route('patterns.infinite-scroll') }}">
            <span class="copy">
                <span class="name">Infinite Scroll</span>
                <span class="blurb">
                    Scroll the same issue log instead of clicking: a sentinel
                    at the bottom of the feed reveals itself and asks for the
                    next older page, chaining until the log runs out.
                </span>
            </span>
            <span class="go" aria-hidden="true">&rarr;</span>
        </a>

        <a class="pattern-card" href="{{ route('patterns.lazy-load') }}">
            <span class="copy">
                <span class="name">Lazy Load</span>
                <span class="blurb">
                    A Linear-style issue detail page: header first, then a
                    shimmering skeleton that asks for the issue&rsquo;s
                    activity log and swaps it in after the page loads.
                </span>
            </span>
            <span class="go" aria-hidden="true">&rarr;</span>
        </a>
    </section>
@endsection
