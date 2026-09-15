<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->teams()->where('teams.id', $payment->weddingProject->team_id)->exists();
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->teams()->where('teams.id', $payment->weddingProject->team_id)->exists();
    }
}
