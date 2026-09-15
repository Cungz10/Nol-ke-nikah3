<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $wedding_project_id
 * @property int|null $user_id
 * @property string $action
 * @property string $resource_type
 * @property int|null $resource_id
 * @property string|null $description
 * @property array|null $properties
 */
#[Fillable(['wedding_project_id', 'user_id', 'action', 'resource_type', 'resource_id', 'description', 'properties'])]
class ActivityLog extends Model
{
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public function weddingProject(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
