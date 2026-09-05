@extends('layouts.app')

@section('title', 'Click to Load · htmx 4 patterns')

@section('content')
    <nav class="crumbs"><a href="{{ route('home') }}">All patterns</a> &nbsp;&rsaquo;&nbsp; Click to Load</nav>

    <p class="eyebrow">htmx.org pattern</p>
    <h1>Click to Load</h1>
    <p class="lede">
        Put a button at the end of a list and have it ask the server for the
        next page. The server answers with the new items plus a fresh button
        for the page after that &mdash; and stops sending the button when the
        list runs out. No client-side state, no JavaScript beyond the htmx
        attributes on the button.
    </p>
    <p class="lede" style="margin-top: -1rem;">
        Official write-up: <a href="https://four.htmx.org/patterns/click-to-load" target="_blank" rel="noopener">four.htmx.org/patterns/click-to-load</a>
    </p>

    <section class="card">
        <h2>Demo &mdash; an issue&rsquo;s activity log</h2>
        <p class="how">
            The newest ten events render with the page. Each
            <em>Show earlier activity</em> button requests
            <code>/patterns/click-to-load/activity?page=N</code> and swaps
            itself (<code>hx-swap="outerHTML"</code>) for the next-older ten
            events plus the next button, newest first.
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
            @include('patterns.partials.activity-events', [
                'issue' => $issue,
                'events' => $events,
            ])
        </div>
    </section>
@endsection
