{{--
    One page of an issue's activity log plus an infinite-scroll sentinel.
    Rendered for the initial page load and again as the htmx fragment: the
    sentinel swaps itself (hx-swap="outerHTML") for this partial once it
    scrolls into view (hx-trigger="revealed"), so the next-older events
    land in its place and a fresh sentinel takes over below them. The final
    page returns the oldest events with no sentinel, just an end-of-feed
    marker. Newest first. Rows come from the shared activity-rows partial.

    Requires: $events — an Illuminate\Pagination\LengthAwarePaginator of
    IssueActivity models with their actor loaded.
--}}
@include('patterns.partials.activity-rows', ['events' => $events])

@if ($events->hasMorePages())
    <div
        class="feed-sentinel"
        hx-get="{{ route('patterns.infinite-scroll.feed', ['page' => $events->currentPage() + 1]) }}"
        hx-trigger="revealed"
        hx-swap="outerHTML"
    >
        <span class="sentinel-spinner" aria-hidden="true"></span>
        Loading earlier activity&hellip;
    </div>
@elseif ($events->total() > 0)
    <p class="feed-end">You&rsquo;ve reached the earliest activity on this issue.</p>
@endif
