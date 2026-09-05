<?php

namespace App\Http\Controllers\Patterns;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class InfiniteScrollController extends Controller
{
    /**
     * Activity events loaded per scroll. Smaller than click-to-load's page
     * size so the sentinel re-triggers several times as the feed grows.
     */
    private const PER_PAGE = 6;

    /**
     * The pattern page: the issue header plus the newest activity page and
     * the scroll sentinel below it.
     */
    public function index(): View
    {
        $issue = $this->demoIssue();

        return view('patterns.infinite-scroll', [
            'issue' => $issue,
            'events' => $this->events($issue),
        ]);
    }

    /**
     * The htmx fragment: the next-older page of activity, plus a fresh
     * sentinel when another page exists. The sentinel on the page swaps
     * itself (outerHTML) for this fragment once it scrolls into view, so
     * the feed keeps growing downward. The final page returns the oldest
     * events with no sentinel and an end-of-feed marker instead.
     */
    public function feed(): View
    {
        $issue = $this->demoIssue();

        return view('patterns.partials.infinite-scroll-activity', [
            'events' => $this->events($issue),
        ]);
    }

    /**
     * The seeded issue whose log is long enough to demo pagination — the
     * one with the most activity (the workspace seeder's first issue).
     */
    private function demoIssue(): Issue
    {
        return Issue::query()
            ->withCount('activity')
            ->orderByDesc('activity_count')
            ->orderBy('id')
            ->firstOrFail();
    }

    /**
     * One page of the issue's activity, newest first, so fragments append
     * predictably. The page number comes from the request's `page` query
     * parameter.
     */
    private function events(Issue $issue): LengthAwarePaginator
    {
        return $issue->activity()
            ->with('actor')
            ->latest('id')
            ->paginate(self::PER_PAGE);
    }
}
