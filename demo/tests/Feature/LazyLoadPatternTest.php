<?php

namespace Tests\Feature;

use App\Models\Issue;
use Database\Seeders\WorkspaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LazyLoadPatternTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WorkspaceSeeder::class);
    }

    public function test_home_lists_the_pattern_and_links_to_its_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Lazy Load')
            ->assertSee('/patterns/lazy-load');
    }

    public function test_pattern_page_renders_the_issue_header_and_skeleton_not_the_log(): void
    {
        $issue = $this->demoIssue();

        $this->get(route('patterns.lazy-load'))
            ->assertOk()
            ->assertSee($issue->title)
            ->assertSee($issue->project->name)
            ->assertSee('hx-trigger="load"', false)
            ->assertSee('hx-swap="innerHTML"', false)
            ->assertSee('patterns/lazy-load/activity')
            ->assertSee('class="sk-row"', false)
            ->assertSee('Loading activity')
            ->assertDontSee('LGTM'); // the activity rows are not server-rendered
    }

    public function test_the_lazy_fragment_returns_the_whole_log_without_skeleton_or_controls(): void
    {
        $issue = $this->demoIssue();

        $response = $this->get(route('patterns.lazy-load.activity'));

        $response->assertOk()
            ->assertSee('LGTM') // the newest comment
            ->assertSee('created this issue') // the oldest event, same response
            ->assertDontSee('sk-row')
            ->assertDontSee('Show earlier activity')
            ->assertDontSee('Loading earlier activity')
            ->assertDontSee('hx-trigger', false); // no trigger, so the load never loops

        // One row per activity event: the whole log ships in this response.
        // Each row is rendered by the shared activity-event partial, which
        // opens exactly one <div class="activity-event"> per event.
        $rowCount = substr_count($response->getContent(), 'class="activity-event"');

        $this->assertSame($issue->activity()->count(), $rowCount);
    }

    /**
     * The seeded demo issue: the one with the longest activity log.
     */
    private function demoIssue(): Issue
    {
        return Issue::query()
            ->withCount('activity')
            ->orderByDesc('activity_count')
            ->orderBy('id')
            ->firstOrFail();
    }
}
