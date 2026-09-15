<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_project_id',
        'vendor_id',
        'title',
        'category',
        'file_path',
        'file_name',
        'mime_type',
        'file_size_bytes',
        'notes',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    public function weddingProject(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
