<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_project_id',
        'vendor_category_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'instagram',
        'website',
        'quote_amount_idr',
        'deal_amount_idr',
        'status',
        'notes',
        'follow_up_date',
    ];

    protected $casts = [
        'quote_amount_idr' => 'integer',
        'deal_amount_idr' => 'integer',
        'follow_up_date' => 'date',
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
