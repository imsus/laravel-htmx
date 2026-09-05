<?php

namespace Tests\Feature;

use App\Models\Issue;
use Database\Seeders\WorkspaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfiniteScrollPatternTest extends TestCase
{
    use RefreshDatabase;

    private const PER_PAGE = 6;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(WorkspaceSeeder::class);
    }

    public function test_home_lists_the_pattern_and_links_to_its_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Infinite Scroll')
            ->assertSee('/patterns/infinite-scroll');
    }

    public function test_pattern_page_renders_newest_events_and_the_scroll_sentinel(): void
    {
        $issue = $this->demoIssue();

        $this->get(route('patterns.infinite-scroll'))
            ->assertOk()
            ->assertSee($issue->title)
            ->assertSee('LGTM') // the newest comment, seeded last
            ->assertSee('Loading earlier activity')
            ->assertSee('hx-trigger="revealed"', false) // attribute, not escaped text
            ->assertSee('page=2')
            ->assertDontSee('Approved the token layer') // second-page event
            ->assertDontSee('created this issue'); // oldest event, last page
    }

    public function test_sentinel_fragment_serves_the_next_older_page_and_advances_the_sentinel(): void
    {
        $this->get(route('patterns.infinite-scroll.feed', ['page' => 2]))
            ->assertOk()
            ->assertSee('Approved the token layer') // an event from the second page
            ->assertDontSee('LGTM')
            ->assertSee('page=3')
            ->assertDontSee('reached the earliest activity');
    }

    public function test_the_last_page_fragment_omits_the_sentinel_and_marks_the_end(): void
    {
        $lastPage = (int) ceil($this->demoIssue()->activity()->count() / self::PER_PAGE);

        $this->get(route('patterns.infinite-scroll.feed', ['page' => $lastPage]))
            ->assertOk()
            ->assertSee('created this issue')
            ->assertDontSee('Loading earlier activity')
            ->assertDontSee('revealed')
            ->assertSee('reached the earliest activity');
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
