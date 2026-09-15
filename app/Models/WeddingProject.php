<?php

namespace App\Models;

use Database\Factories\WeddingProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['team_id', 'partner_one_name', 'partner_two_name', 'target_date', 'budget_total', 'guest_count', 'city', 'religion', 'tradition', 'status'])]
class WeddingProject extends Model
{
    /** @use HasFactory<WeddingProjectFactory> */
    use HasFactory;

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function timelinePhases(): HasMany
    {
        return $this->hasMany(TimelinePhase::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function budgetAllocations(): HasMany
    {
        return $this->hasMany(BudgetAllocation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WeddingDocument::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'budget_total' => 'integer',
            'guest_count' => 'integer',
        ];
    }
}
