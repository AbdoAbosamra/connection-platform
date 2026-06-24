<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function view(User $user, Job $job): bool
    {
        // Null-safe: employer may be null if EmployerProfile was soft-deleted
        return $user->isAdmin()
            || $job->employer?->user_id === $user->id;
    }

    public function update(User $user, Job $job): bool
    {
        return $user->isAdmin()
            || $job->employer?->user_id === $user->id;
    }

    public function delete(User $user, Job $job): bool
    {
        return $user->isAdmin()
            || $job->employer?->user_id === $user->id;
    }
}
