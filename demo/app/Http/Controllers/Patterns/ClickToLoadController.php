<?php

namespace App\Http\Controllers\Patterns;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class ClickToLoadController extends Controller
{
    /**
     * Activity events rendered per request. The demo pages through the
     * featured issue's log in pages of this size.
     */
    private const PER_PAGE = 10;

    /**
     * The pattern page: the issue header plus the newest activity page and
     * the load-more button.
     */
    public function index(): View
    {
        $issue = $this->demoIssue();

        return view('patterns.click-to-load', [
            'issue' => $issue,
            'events' => $this->events($issue),
        ]);
    }

    /**
     * The htmx fragment: the next-older page of activity, plus a fresh
     * load-more button when another page exists. The button on the page
     * swaps itself (outerHTML) for this fragment, so the feed grows in
     * place, newest first.
     */
    public function activity(): View
    {
        $issue = $this->demoIssue();

        return view('patterns.partials.activity-events', [
            'issue' => $issue,
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
