<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function view(User $user, Vendor $vendor): bool
    {
        return $user->teams()->where('teams.id', $vendor->weddingProject->team_id)->exists();
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return $user->teams()->where('teams.id', $vendor->weddingProject->team_id)->exists();
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->teams()->where('teams.id', $vendor->weddingProject->team_id)->exists();
    }
}
