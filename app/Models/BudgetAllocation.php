<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_project_id',
        'vendor_category_id',
        'percentage',
        'allocated_amount_idr',
        'notes',
    ];

    protected $casts = [
        'percentage' => 'integer',
        'allocated_amount_idr' => 'integer',
    ];

    public function weddingProject(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class);
    }

    public function vendorCategory(): BelongsTo
    {
        return $this->belongsTo(VendorCategory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
