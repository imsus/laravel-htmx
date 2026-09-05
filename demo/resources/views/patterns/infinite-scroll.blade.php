@extends('layouts.app')

@section('title', 'Infinite Scroll · htmx 4 patterns')

@section('content')
    <nav class="crumbs"><a href="{{ route('home') }}">All patterns</a> &nbsp;&rsaquo;&nbsp; Infinite Scroll</nav>

    <p class="eyebrow">htmx.org pattern</p>
    <h1>Infinite Scroll</h1>
    <p class="lede">
        End a feed with a sentinel row and let htmx notice when it scrolls
        into view. <code>hx-trigger="revealed"</code> fires the request, and
        the server answers with the next page of rows plus a fresh sentinel
        that swaps the old one away &mdash; a self-extending chain that stops
        when the feed runs out. No button to click, no scroll listener to
        write.
    </p>
    <p class="lede" style="margin-top: -1rem;">
        Official write-up: <a href="https://four.htmx.org/patterns/infinite-scroll" target="_blank" rel="noopener">four.htmx.org/patterns/infinite-scroll</a>
    </p>

    <section class="card">
        <h2>Demo &mdash; an issue&rsquo;s activity log</h2>
        <p class="how">
            The same seeded log the Click to Load page pages through, but
            here you never click. The newest six events render with the page;
            scroll to the bottom and the <em>Loading earlier activity&hellip;</em>
            sentinel requests
            <code>/patterns/infinite-scroll/feed?page=N</code> and swaps
            itself (<code>hx-swap="outerHTML"</code>) for the next-older six
            events plus a fresh sentinel. The last page returns the oldest
            events and no sentinel &mdash; the feed simply ends.
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

        <div class="activity-list">
            @include('patterns.partials.infinite-scroll-activity', [
                'events' => $events,
            ])
        </div>
    </section>
@endsection
