@extends('layouts.app')

@section('title', 'Lazy Load · htmx 4 patterns')

@section('content')
    <nav class="crumbs"><a href="{{ route('home') }}">All patterns</a> &nbsp;&rsaquo;&nbsp; Lazy Load</nav>

    <p class="eyebrow">htmx.org pattern</p>
    <h1>Lazy Load</h1>
    <p class="lede">
        Swap a placeholder in for content that is slow to produce, then
        fetch it once the page is already up.
        <code>hx-trigger="load"</code> fires the request as soon as htmx
        processes the element, and the server&rsquo;s response replaces the
        placeholder &mdash; the rest of the page paints first, so secondary
        queries never sit in front of first paint.
    </p>
    <p class="lede" style="margin-top: -1rem;">
        Official write-up: <a href="https://four.htmx.org/patterns/lazy-load" target="_blank" rel="noopener">four.htmx.org/patterns/lazy-load</a>
    </p>

    <section class="card">
        <h2>Demo &mdash; an issue detail page</h2>
        <p class="how">
            A Linear-style issue screen: the header &mdash; project, title,
            status, priority, assignee &mdash; renders with the page, while
            the activity pane below it starts as a shimmering skeleton.
            <code>hx-trigger="load"</code> requests
            <code>/patterns/lazy-load/activity</code>, and the response
            &mdash; the issue&rsquo;s full activity log, newest first &mdash;
            swaps in over the skeleton (<code>hx-swap="innerHTML"</code>).
            The skeleton roughly reserves the rows&rsquo; space, so the swap
            causes no layout shift, and the fragment carries no
            <code>hx-trigger</code>, so the load never loops. The
            demo-latency middleware keeps the skeleton visible long enough
            to watch.
        </p>

        <header class="issue-head">
            <p class="project">{{ $issue->project->name }}</p>
            <h3>{{ $issue->title }}</h3>
            <p class="meta">
                <span class="chip">{{ $issue->status }}</span>
                <span class="chip">{{ $issue->priorityLabel() }} priority</span>
                @if ($issue->assignee)
                    <span>assigned to {{ $issue->assignee->name }}</span>
                @endif
            </p>
        </header>

        <div
            class="activity-list"
            id="issue-activity"
            hx-get="{{ route('patterns.lazy-load.activity') }}"
            hx-trigger="load"
            hx-swap="innerHTML"
        >
            <p class="visually-hidden">Loading activity&hellip;</p>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
            <div class="sk-row" aria-hidden="true">
                <span class="sk sk-avatar"></span>
                <span class="sk-copy">
                    <span class="sk sk-line sk-line--meta"></span>
                    <span class="sk sk-line sk-line--body"></span>
                </span>
            </div>
        </div>
    </section>
@endsection
