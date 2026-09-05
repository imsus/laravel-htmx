<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\IssueActivity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The demo actors, in palette order.
     *
     * @var array<int, array{name: string, email: string}>
     */
    private const USERS = [
        ['name' => 'Priya Patel', 'email' => 'priya@example.dev'],
        ['name' => 'Sam Rivera', 'email' => 'sam@example.dev'],
        ['name' => 'Anna Kowalski', 'email' => 'anna@example.dev'],
        ['name' => 'Dev Shah', 'email' => 'dev@example.dev'],
        ['name' => 'Marta Silva', 'email' => 'marta@example.dev'],
    ];

    /**
     * @var array<int, string>
     */
    private const PROJECTS = [
        'Design System',
        'Mobile App 2.0',
        'Billing & Payments',
    ];

    /**
     * Every seeded issue. The first one is the demo issue: it carries the
     * long activity log the click-to-load pattern pages through.
     *
     * @var array<int, array{
     *     project: int, title: string, status: string, priority: int,
     *     assignee: int|null, age_days: int
     * }>
     */
    private const ISSUES = [
        ['project' => 0, 'title' => 'Dark mode: audit and theme every screen', 'status' => 'In Review', 'priority' => 4, 'assignee' => 0, 'age_days' => 11],
        ['project' => 0, 'title' => 'Unify spacing and radius tokens', 'status' => 'Done', 'priority' => 1, 'assignee' => 2, 'age_days' => 21],
        ['project' => 1, 'title' => 'Fix pull-to-refresh crash on iOS', 'status' => 'In Progress', 'priority' => 3, 'assignee' => 3, 'age_days' => 6],
        ['project' => 1, 'title' => 'Offline queue for draft sync', 'status' => 'Backlog', 'priority' => 2, 'assignee' => null, 'age_days' => 3],
        ['project' => 2, 'title' => 'Stripe webhook idempotency keys', 'status' => 'In Progress', 'priority' => 4, 'assignee' => 4, 'age_days' => 5],
        ['project' => 2, 'title' => 'Annual plan proration edge cases', 'status' => 'Backlog', 'priority' => 1, 'assignee' => null, 'age_days' => 14],
    ];

    /**
     * The demo issue's activity timeline, oldest first. Re-seeding replaces
     * the whole workspace, so the log is deterministic: event N is older
     * than event N+1, and created_at spreads back over ~9.4 days.
     *
     * Each row: [type, actor, ...] where actor indexes into USERS and the
     * tail is type-specific — a comment body, or the from/to values of a
     * field change.
     *
     * @var array<int, array{0: string, 1: int, 2?: string, 3?: string|null, 4?: string}>
     */
    private const FEATURED_ACTIVITY = [
        ['created', 3],
        ['comment', 3, 'Tracking the theme audit here — the web app has 14 hardcoded colors.'],
        ['status_change', 3, 'Backlog', 'Todo'],
        ['assignee_change', 3, null, 'Priya Patel'],
        ['priority_change', 3, 'None', 'Low'],
        ['comment', 0, 'Pulling the design tokens from the audit doc into a branch.'],
        ['comment', 1, 'The light-mode contrast fixes should ride along with this.'],
        ['status_change', 0, 'Todo', 'In Progress'],
        ['comment', 2, 'Found focus-ring styles missing dark variants in the form controls.'],
        ['priority_change', 0, 'Low', 'Medium'],
        ['comment', 3, 'I will sweep the mobile screens once the web tokens land.'],
        ['comment', 0, 'Added --focus-ring and --surface tokens to the theme.'],
        ['comment', 2, 'Modal scrim and tooltip shadows look off on dark surfaces.'],
        ['assignee_change', 0, 'Priya Patel', 'Sam Rivera'],
        ['comment', 1, 'Taking over while Priya is out — picking up the modal scrims.'],
        ['assignee_change', 1, 'Sam Rivera', 'Priya Patel'],
        ['comment', 0, 'Back. Scrims fixed, pushing the contrast report for review.'],
        ['comment', 3, 'Marked the web sweep done in the audit spreadsheet.'],
        ['priority_change', 0, 'Medium', 'High'],
        ['comment', 1, 'The calendar widget still ships light-only inline styles.'],
        ['comment', 0, 'Adding a dark calendar pass to this issue.'],
        ['comment', 2, 'Empty states use two different grays; unifying on --surface-muted.'],
        ['status_change', 0, 'In Progress', 'In Review'],
        ['comment', 3, 'Colors check out, but the keyboard focus trap regressed.'],
        ['status_change', 3, 'In Review', 'In Progress'],
        ['comment', 0, 'Good catch — restoring the focus trap and re-testing.'],
        ['comment', 1, 'Sidebar hover states are inconsistent between themes.'],
        ['comment', 0, 'Normalized them through the --hover-overlay token.'],
        ['status_change', 0, 'In Progress', 'In Review'],
        ['comment', 2, 'Reviewing the diff now; the token docs look solid.'],
        ['comment', 3, 'Approved the token layer. Icons still need an inverted pass.'],
        ['comment', 0, 'The icon set ships a dark variant in the same release.'],
        ['priority_change', 0, 'High', 'Urgent'],
        ['comment', 1, 'Design review moved up — this blocks the App Store submission.'],
        ['comment', 0, 'Folding in the splash screen theme while we are here.'],
        ['comment', 2, 'Splash screen tokens added.'],
        ['comment', 3, 'The audit spreadsheet is 100% green now.'],
        ['comment', 3, 'Web and mobile sweeps both landed in the same release train.'],
        ['comment', 0, 'Contrast checks pass AA everywhere except disabled states.'],
        ['comment', 1, 'Disabled states are exempt by design — worth a note in the report.'],
        ['comment', 0, 'Documented the exemption. Ready for final sign-off.'],
        ['comment', 2, 'LGTM — shipping the audit summary with the release notes.'],
    ];

    /**
     * Seed the Linear-style demo workspace: actors, projects, issues, and
     * the demo issue's activity log.
     */
    public function run(): void
    {
        $users = $this->users();
        $projects = [];

        foreach (self::PROJECTS as $name) {
            $projects[] = Project::create(['name' => $name]);
        }

        foreach (self::ISSUES as $index => $issue) {
            Issue::create([
                'project_id' => $projects[$issue['project']]->id,
                'title' => $issue['title'],
                'status' => $issue['status'],
                'priority' => $issue['priority'],
                'assignee_id' => $issue['assignee'] === null ? null : $users[$issue['assignee']]->id,
                'created_at' => now()->subDays($issue['age_days']),
                'updated_at' => now()->subDays($issue['age_days']),
            ]);
        }

        $demoIssue = Issue::query()->orderBy('id')->first();

        foreach (self::FEATURED_ACTIVITY as $index => $entry) {
            [$type, $actor] = $entry;

            IssueActivity::create([
                'issue_id' => $demoIssue->id,
                'actor_id' => $users[$actor]->id,
                'type' => $type,
                'payload' => $this->payload($type, array_slice($entry, 2)),
                // Event N is (count-1-N) * 5.5h + 40m older than now, so the
                // newest event is 40 minutes old and ordering is stable.
                'created_at' => now()->subMinutes((count(self::FEATURED_ACTIVITY) - 1 - $index) * 330 + 40),
            ]);
        }
    }

    /**
     * @return array<int, User>
     */
    private function users(): array
    {
        $users = [];

        foreach (self::USERS as $index => ['name' => $name, 'email' => $email]) {
            $users[$index] = User::factory()->create([
                'name' => $name,
                'email' => $email,
            ]);
        }

        return $users;
    }

    /**
     * Build the type-specific payload for one activity entry.
     *
     * @param  array<int, string|null>  $args
     * @return array<string, string|null>
     */
    private function payload(string $type, array $args): array
    {
        return match ($type) {
            'comment' => ['body' => $args[0] ?? ''],
            'created' => [],
            default => [
                'from' => $args[0] ?? null,
                'to' => $args[1] ?? null,
            ],
        };
    }
}
