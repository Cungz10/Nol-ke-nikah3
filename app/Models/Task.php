<?php

namespace App\Models;

use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['timeline_phase_id', 'title', 'description', 'due_date', 'status', 'sort_order', 'is_critical', 'assigned_to'])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    public function timelinePhase(): BelongsTo
    {
        return $this->belongsTo(TimelinePhase::class);
    }

    protected function casts(): array
    {
        return ['due_date' => 'date', 'is_critical' => 'boolean'];
    }
}
