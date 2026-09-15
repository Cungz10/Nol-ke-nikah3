<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_project_id',
        'vendor_id',
        'budget_allocation_id',
        'title',
        'amount_idr',
        'payment_type',
        'payment_date',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'amount_idr' => 'integer',
        'payment_date' => 'date',
    ];

    public function weddingProject(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function budgetAllocation(): BelongsTo
    {
        return $this->belongsTo(BudgetAllocation::class);
    }
}
