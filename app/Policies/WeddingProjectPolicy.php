<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeddingProject;

class WeddingProjectPolicy
{
    /**
     * User dapat melihat project jika mereka adalah anggota team yang memiliki project tersebut.
     */
    public function view(User $user, WeddingProject $weddingProject): bool
    {
        return $user->teams()->where('teams.id', $weddingProject->team_id)->exists();
    }

    /**
     * User dapat mengupdate project jika mereka adalah anggota team yang memiliki project tersebut.
     */
    public function update(User $user, WeddingProject $weddingProject): bool
    {
        return $user->teams()->where('teams.id', $weddingProject->team_id)->exists();
    }

    /**
     * User dapat menghapus project jika mereka adalah anggota team yang memiliki project tersebut.
     */
    public function delete(User $user, WeddingProject $weddingProject): bool
    {
        return $user->teams()->where('teams.id', $weddingProject->team_id)->exists();
    }
}
