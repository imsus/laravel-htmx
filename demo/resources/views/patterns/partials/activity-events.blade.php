{{--
    One page of an issue's activity log plus a "Show earlier activity"
    button. Rendered for the initial page load and again as the htmx
    fragment: the button swaps itself (hx-target="this",
    hx-swap="outerHTML") for this partial, so the next-older
    events land in its place and the new button takes over. Newest first.
    Rows come from the shared activity-rows partial.

    Requires: $events — an Illuminate\Pagination\LengthAwarePaginator of
    IssueActivity models with their actor loaded.
--}}
@include('patterns.partials.activity-rows', ['events' => $events])

@if ($events->hasMorePages())
    <button
        class="load-more"
        hx-get="{{ route('patterns.click-to-load.activity', ['page' => $events->currentPage() + 1]) }}"
        hx-target="this"
        hx-swap="outerHTML"
    >
        Show earlier activity
    </button>
@endif
