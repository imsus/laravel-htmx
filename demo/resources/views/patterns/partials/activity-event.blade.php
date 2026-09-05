{{--
    One row of an issue's activity log: avatar, actor, and the
    type-specific line or comment body. Shared by the click-to-load and
    infinite-scroll feeds so both pattern pages render rows identically.

    Requires: $event — an IssueActivity model with its actor loaded.
--}}
<div class="activity-event">
    <span class="avatar c{{ $event->actor_id % 5 }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($event->actor->name, 0, 1)) }}</span>

    <div class="activity-content">
        @if ($event->type === 'comment')
            <p class="activity-meta">
                <strong>{{ $event->actor->name }}</strong> commented
                &middot; <time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->diffForHumans(short: true) }}</time>
            </p>
            <p class="comment-body">{{ $event->payload['body'] }}</p>
        @else
            <p class="activity-meta">
                <strong>{{ $event->actor->name }}</strong>
                @if ($event->type === 'created')
                    created this issue
                @elseif ($event->type === 'status_change')
                    moved status from <span class="chip">{{ $event->payload['from'] }}</span>
                    to <span class="chip">{{ $event->payload['to'] }}</span>
                @elseif ($event->type === 'priority_change')
                    changed priority from <span class="chip">{{ $event->payload['from'] }}</span>
                    to <span class="chip">{{ $event->payload['to'] }}</span>
                @elseif ($event->type === 'assignee_change')
                    @if ($event->payload['from'] === null)
                        assigned this to <span class="chip">{{ $event->payload['to'] }}</span>
                    @else
                        reassigned from <span class="chip">{{ $event->payload['from'] }}</span>
                        to <span class="chip">{{ $event->payload['to'] }}</span>
                    @endif
                @endif
                &middot; <time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->diffForHumans(short: true) }}</time>
            </p>
        @endif
    </div>
</div>
