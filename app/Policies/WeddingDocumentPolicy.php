<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeddingDocument;

class WeddingDocumentPolicy
{
    public function view(User $user, WeddingDocument $document): bool
    {
        return $user->teams()->where('teams.id', $document->weddingProject->team_id)->exists();
    }

    public function delete(User $user, WeddingDocument $document): bool
    {
        return $user->teams()->where('teams.id', $document->weddingProject->team_id)->exists();
    }
}
