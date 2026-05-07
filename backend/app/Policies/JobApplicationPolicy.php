<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function view(User $user, JobApplication $application): bool
    {
        if ($user->isAdmin()) return true;

        // Job seeker can view their own
        if ($user->isJobSeeker()) {
            return $application->jobSeeker->user_id === $user->id;
        }

        // Employer can view applications to their jobs
        if ($user->isEmployer()) {
            return $application->job->employer->user_id === $user->id;
        }

        return false;
    }

    public function update(User $user, JobApplication $application): bool
    {
        if ($user->isAdmin()) return true;

        // Only the job seeker can withdraw
        if ($user->isJobSeeker()) {
            return $application->jobSeeker->user_id === $user->id;
        }

        // Employer can update status
        if ($user->isEmployer()) {
            return $application->job->employer->user_id === $user->id;
        }

        return false;
    }
}
