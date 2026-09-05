<?php

namespace App\Http\Controllers\Patterns;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class LazyLoadController extends Controller
{
    /**
     * The pattern page: the issue header plus the skeleton that stands in
     * for the activity pane until the lazy request lands.
     */
    public function index(): View
    {
        return view('patterns.lazy-load', [
            'issue' => $this->demoIssue(),
        ]);
    }

    /**
     * The htmx fragment: the demo issue's whole activity log, newest
     * first. The skeleton on the page requested it with
     * hx-trigger="load" and hx-swap="innerHTML", and the rows land in
     * its place. The fragment carries no trigger attributes, so the load
     * never repeats.
     */
    public function activity(): View
    {
        return view('patterns.partials.activity-rows', [
            'events' => $this->events(),
        ]);
    }

    /**
     * The seeded issue whose log is long enough to demo lazy loading — the
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
     * The issue's full activity log in one response — an issue detail page
     * shows the whole timeline, not a page of it.
     *
     * @return Collection<int, IssueActivity>
     */
    private function events(): Collection
    {
        return $this->demoIssue()->activity()
            ->with('actor')
            ->latest('id')
            ->get();
    }
}
