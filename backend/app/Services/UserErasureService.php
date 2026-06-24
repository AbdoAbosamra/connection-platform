<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Message;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * GDPR Article 17 "Right to be Forgotten" erasure for a single user.
 *
 * Strategy — pseudonymisation, not hard deletion:
 *   The platform schema wires `messages.sender_id` (and others) to users with
 *   ON DELETE CASCADE, so a hard delete of the users row would silently destroy
 *   the *other* party's chat history. Instead we keep the row as an anonymised
 *   "tombstone" (PII scrubbed, account soft-deleted so it can never log in) and
 *   anonymise everything the person authored — while leaving rows that belong to
 *   innocent counterparts (applications, conversations, moderation records)
 *   intact and referentially valid.
 *
 * Everything runs inside one DB transaction. Uploaded files (avatar, logo,
 * resume, message attachments) are deleted from disk as part of the erasure.
 */
class UserErasureService
{
    /**
     * Erase a user's personal data. Idempotent — safe to re-run.
     */
    public function forget(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Revoke all API tokens — kills every active session immediately.
            $user->tokens()->delete();

            // 2. Delete the user's own private feed (their notifications).
            $user->notifications()->delete();

            // 3. Role-specific profile + content anonymisation.
            if ($user->isEmployer()) {
                $this->forgetEmployer($user);
            } elseif ($user->isJobSeeker()) {
                $this->forgetJobSeeker($user);
            }

            // 4. Scrub the content of messages this user authored. The rows stay
            //    so the other participant keeps their conversation; only the
            //    deleted user's text/attachments are removed.
            $this->anonymiseMessages($user);

            // 5. Pseudonymise the users row itself and soft-delete it.
            $this->pseudonymiseUser($user);
        });
    }

    // ────────────────────────────────────────────────────────────────────────

    private function forgetEmployer(User $user): void
    {
        $profile = $user->employerProfile;
        if (!$profile) {
            return;
        }

        $this->deleteFile($profile->logo);

        // Close the company's listings so they stop appearing publicly, but keep
        // the rows — job seekers' applications/threads reference them.
        Job::where('employer_profile_id', $profile->id)
            ->update(['status' => 'closed', 'is_featured' => false]);

        $profile->forceFill([
            'company_name' => 'Deleted Company',
            'company_slug' => 'deleted-company-'.$profile->id,
            'description' => null,
            'industry' => null,
            'company_size' => null,
            'website' => null,
            'logo' => null,
            'headquarters_city' => null,
            'headquarters_state' => null,
            'linkedin_url' => null,
            'twitter_url' => null,
            'founded_year' => null,
            'is_verified' => false,
            'verified_at' => null,
            'verification_method' => null,
            'linkedin_id' => null,
            'is_featured' => false,
        ])->save();
    }

    private function forgetJobSeeker(User $user): void
    {
        $profile = $user->jobSeekerProfile;
        if (!$profile) {
            return;
        }

        $this->deleteFile($profile->resume);

        // Scrub the personal content attached to each application (cover letter +
        // the resume snapshot file) but keep the application row so the employer's
        // hiring record stays consistent.
        $applications = JobApplication::where('job_seeker_profile_id', $profile->id)->get();
        foreach ($applications as $application) {
            $this->deleteFile($application->resume_snapshot);
        }
        JobApplication::where('job_seeker_profile_id', $profile->id)
            ->update(['cover_letter' => null, 'resume_snapshot' => null]);

        // Saved jobs are purely the user's own data → remove them.
        SavedJob::where('job_seeker_profile_id', $profile->id)->delete();

        // Detach skills (personal profile data).
        $profile->skills()->detach();

        $profile->forceFill([
            'headline' => null,
            'bio' => null,
            'resume' => null,
            'portfolio_url' => null,
            'linkedin_url' => null,
            'github_url' => null,
            'current_city' => null,
            'current_country' => null,
            'nationality' => null,
            'current_job_title' => null,
            'desired_job_title' => null,
            'desired_salary_min' => null,
            'desired_salary_max' => null,
            // Hide from the public professionals directory.
            'profile_complete' => false,
            'is_featured' => false,
        ])->save();
    }

    private function anonymiseMessages(User $user): void
    {
        // Remove any attachment files the user uploaded.
        Message::where('sender_id', $user->id)
            ->whereNotNull('attachment')
            ->pluck('attachment')
            ->each(fn ($path) => $this->deleteFile($path));

        Message::where('sender_id', $user->id)->update([
            'body' => '[User Account Deleted]',
            'attachment' => null,
        ]);
    }

    private function pseudonymiseUser(User $user): void
    {
        $this->deleteFile($user->avatar);

        $user->forceFill([
            'name' => 'Deleted User',
            // id keeps the email unique across repeated erasures.
            'email' => 'deleted_'.$user->id.'@anonymized.invalid',
            'phone' => null,
            'avatar' => null,
            'country' => null,
            'timezone' => null,
            'email_verified_at' => null,
            'is_active' => false,
            // Invalidate the password so the account can never authenticate even
            // if it were somehow restored.
            'password' => bcrypt(bin2hex(random_bytes(16))),
        ])->save();

        // Soft delete: keeps the row (FKs stay valid) but removes it from the
        // app via the global scope, so it can't be queried or logged into.
        $user->delete();
    }

    /**
     * Delete a stored file from the public disk, ignoring missing paths.
     */
    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
