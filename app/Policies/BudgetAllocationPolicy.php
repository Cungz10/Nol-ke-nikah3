<?php

namespace App\Policies;

use App\Models\BudgetAllocation;
use App\Models\User;

class BudgetAllocationPolicy
{
    public function view(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->teams()->where('teams.id', $budgetAllocation->weddingProject->team_id)->exists();
    }

    public function update(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->teams()->where('teams.id', $budgetAllocation->weddingProject->team_id)->exists();
    }

    public function delete(User $user, BudgetAllocation $budgetAllocation): bool
    {
        return $user->teams()->where('teams.id', $budgetAllocation->weddingProject->team_id)->exists();
    }
}
