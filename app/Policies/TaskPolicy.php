<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->teams()->where('teams.id', $task->timelinePhase->weddingProject->team_id)->exists();
    }

    public function update(User $user, Task $task): bool
    {
        return $user->teams()->where('teams.id', $task->timelinePhase->weddingProject->team_id)->exists();
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->teams()->where('teams.id', $task->timelinePhase->weddingProject->team_id)->exists();
    }
}
