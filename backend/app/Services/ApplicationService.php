<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use App\Notifications\ApplicationReceived;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    public function __construct(private FileUploadService $fileUpload) {}

    public function apply(Job $job, JobSeekerProfile $seeker, array $data): JobApplication
    {
        $application = DB::transaction(function () use ($job, $seeker, $data) {
            // Lock the job row to prevent TOCTOU races on concurrent submissions
            $lockedJob = Job::lockForUpdate()->findOrFail($job->id);

            if ($lockedJob->applications()->where('job_seeker_profile_id', $seeker->id)->exists()) {
                throw ValidationException::withMessages([
                    'job_id' => ['You have already applied to this job.'],
                ]);
            }

            if ($lockedJob->status !== 'active') {
                throw ValidationException::withMessages([
                    'job_id' => ['This job is no longer accepting applications.'],
                ]);
            }

            // Snapshot resume at time of application
            $resumeSnapshot = $seeker->resume;
            if (!empty($data['resume'])) {
                $resumeSnapshot = $this->fileUpload->uploadResume($data['resume'], 'applications');
            }

            $application = JobApplication::create([
                'job_id' => $lockedJob->id,
                'job_seeker_profile_id' => $seeker->id,
                'cover_letter' => $data['cover_letter'] ?? null,
                'resume_snapshot' => $resumeSnapshot,
                'expected_salary' => $data['expected_salary'] ?? null,
                'currency' => $data['currency'] ?? 'USD',
            ]);

            $lockedJob->increment('applications_count');

            return $application->load('job.employer', 'jobSeeker.user');
        });

        // Notify employer outside the transaction so a mail failure cannot roll back a valid application
        $application->job->employer->user->notify(new ApplicationReceived($application));

        return $application;
    }

    public function updateStatus(JobApplication $application, string $status, ?string $notes = null): JobApplication
    {
        $application->update([
            'status' => $status,
            'employer_notes' => $notes ?? $application->employer_notes,
        ]);

        // Notify job seeker
        $application->jobSeeker->user->notify(new ApplicationStatusUpdated($application));

        return $application->fresh();
    }

    public function withdraw(JobApplication $application): void
    {
        // Wrap in a transaction with a row lock to prevent concurrent double-withdraw
        // that would decrement applications_count twice and corrupt the counter.
        DB::transaction(function () use ($application) {
            $locked = JobApplication::lockForUpdate()->findOrFail($application->id);

            if (in_array($locked->status, ['hired', 'rejected', 'withdrawn'])) {
                throw ValidationException::withMessages([
                    'status' => ['Cannot withdraw a closed application.'],
                ]);
            }

            $locked->update(['status' => 'withdrawn']);
            $locked->job()->decrement('applications_count');
        });
    }
}
