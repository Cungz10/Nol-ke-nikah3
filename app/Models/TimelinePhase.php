<?php

namespace App\Models;

use Database\Factories\TimelinePhaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['wedding_project_id', 'key', 'name', 'sort_order', 'starts_on', 'ends_on'])]
class TimelinePhase extends Model
{
    /** @use HasFactory<TimelinePhaseFactory> */
    use HasFactory;

    public function weddingProject(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date'];
    }
}
