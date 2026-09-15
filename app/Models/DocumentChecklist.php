<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Checklist dokumen pernikahan berdasarkan agama/adat.
 *
 * @property int $id
 * @property string|null $religion
 * @property string|null $tradition
 * @property string $title
 * @property string|null $description
 * @property int $sort_order
 * @property string $version
 * @property string|null $version_note
 * @property bool $is_active
 */
#[Fillable(['religion', 'tradition', 'title', 'description', 'sort_order', 'version', 'version_note', 'is_active'])]
class DocumentChecklist extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Ambil checklist aktif berdasarkan agama dan adat user.
     */
    public static function forProject(WeddingProject $project): Collection
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($project) {
                $q->whereNull('religion')                     // dokumen universal
                    ->orWhere('religion', $project->religion);  // dokumen per agama
            })
            ->where(function ($q) use ($project) {
                $q->whereNull('tradition')
                    ->orWhere('tradition', $project->tradition);
            })
            ->orderBy('religion')
            ->orderBy('sort_order')
            ->get();
    }
}
