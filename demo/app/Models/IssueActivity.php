<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable entry in an issue's activity timeline — a comment or a
 * field change (status, priority, assignee). The payload holds the
 * type-specific detail: a comment body, or from/to values.
 */
#[Fillable(['issue_id', 'actor_id', 'type', 'payload'])]
class IssueActivity extends Model
{
    /**
     * One log table per issue, not the pluralized default.
     */
    protected $table = 'issue_activity';

    /**
     * A log is immutable: no updated_at column.
     */
    public const UPDATED_AT = null;

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
