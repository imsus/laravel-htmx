<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'title', 'status', 'priority', 'assignee_id'])]
class Issue extends Model
{
    /**
     * Linear-style priority levels, indexed by the stored value.
     */
    public const PRIORITIES = ['None', 'Low', 'Medium', 'High', 'Urgent'];

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? self::PRIORITIES[0];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function activity(): HasMany
    {
        return $this->hasMany(IssueActivity::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }
}
