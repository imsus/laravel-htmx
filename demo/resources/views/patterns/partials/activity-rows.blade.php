{{--
    An issue's activity rows plus the no-activity empty state, newest
    first. Shared by the click-to-load, infinite-scroll, and lazy-load
    fragments so every feed renders rows identically; the trailing control
    (load-more button, scroll sentinel, or none) stays in the calling
    partial. Rows come from the shared activity-event partial.

    Requires: $events — a LengthAwarePaginator or an Eloquent Collection
    of IssueActivity models with their actor loaded.
--}}
@forelse ($events as $event)
    @include('patterns.partials.activity-event', ['event' => $event])
@empty
    {{-- A paginator past its last page reports a total but no items, while
         a Collection reports its real size; ask whichever is present. --}}
    @if ((method_exists($events, 'total') ? $events->total() : $events->count()) === 0)
        <p class="activity-empty">
            No activity yet &mdash; seed the workspace dataset with
            <code>php artisan db:seed</code>.
        </p>
    @endif
@endforelse
