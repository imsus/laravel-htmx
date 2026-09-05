<?php

namespace Tests\Feature;

use App\Models\Issue;
use Database\Seeders\WorkspaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickToLoadPatternTest extends TestCase
{
    use RefreshDatabase;

    private const PER_PAGE = 10;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WorkspaceSeeder::class);
    }

    public function test_home_lists_the_pattern_and_links_to_its_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Click to Load')
            ->assertSee('/patterns/click-to-load');
    }

    public function test_pattern_page_renders_the_issue_and_newest_events(): void
    {
        $issue = $this->demoIssue();

        $this->get(route('patterns.click-to-load'))
            ->assertOk()
            ->assertSee($issue->title)
            ->assertSee($issue->project->name)
            ->assertSee('LGTM') // the newest comment, seeded last
            ->assertSee('Show earlier activity')
            ->assertDontSee('created this issue'); // oldest event, last page
    }

    public function test_load_more_fragment_serves_the_next_older_page_and_advances_the_button(): void
    {
        $this->get(route('patterns.click-to-load.activity', ['page' => 2]))
            ->assertOk()
            ->assertSee('keyboard focus trap regressed') // an event from the second page
            ->assertDontSee('LGTM')
            ->assertSee('page=3');
    }

    public function test_the_last_page_fragment_omits_the_load_more_button(): void
    {
        $lastPage = (int) ceil($this->demoIssue()->activity()->count() / self::PER_PAGE);

        $this->get(route('patterns.click-to-load.activity', ['page' => $lastPage]))
            ->assertOk()
            ->assertSee('created this issue')
            ->assertDontSee('Show earlier activity');
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
