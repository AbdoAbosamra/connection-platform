<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function view(User $user, JobApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Null-safe: profile or its job may be missing (soft-deleted employer, deleted profile)
        if ($user->isJobSeeker()) {
            return $application->jobSeeker?->user_id === $user->id;
        }

        if ($user->isEmployer()) {
            return $application->job?->employer?->user_id === $user->id;
        }

        return false;
    }

    public function update(User $user, JobApplication $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isJobSeeker()) {
            return $application->jobSeeker?->user_id === $user->id;
        }

        if ($user->isEmployer()) {
            return $application->job?->employer?->user_id === $user->id;
        }

        return false;
    }
}
