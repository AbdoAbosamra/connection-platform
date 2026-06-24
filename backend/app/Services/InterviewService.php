<?php

namespace App\Services;

use App\Models\InterviewSchedule;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\InterviewScheduled;
use App\Notifications\InterviewStatusChanged;
use Illuminate\Support\Facades\DB;

class InterviewService
{
    /**
     * Schedule a new interview for an application and move the application into
     * the interview_scheduled stage. The seeker is notified after commit.
     */
    public function schedule(JobApplication $application, User $employerUser, array $data): InterviewSchedule
    {
        $interview = DB::transaction(function () use ($application, $employerUser, $data) {
            $interview = $application->interviewSchedules()->create([
                'scheduled_by' => $employerUser->id,
                'scheduled_at' => $data['scheduled_at'],
                'duration_minutes' => $data['duration_minutes'] ?? 60,
                'format' => $data['format'],
                'meeting_link' => $data['meeting_link'] ?? null,
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Advance the pipeline stage unless the application is already closed.
            if (!in_array($application->status, ['hired', 'rejected', 'withdrawn'])) {
                $application->update(['status' => 'interview_scheduled']);
            }

            return $interview;
        });

        $application->jobSeeker->user->notify(new InterviewScheduled($interview));

        return $interview->fresh();
    }

    /**
     * Reschedule / edit an existing interview. Resets confirmation and re-notifies.
     */
    public function reschedule(InterviewSchedule $interview, array $data): InterviewSchedule
    {
        $interview->update(array_merge(
            array_filter([
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'format' => $data['format'] ?? null,
                'meeting_link' => $data['meeting_link'] ?? null,
                'location' => $data['location'] ?? null,
                'notes' => $data['notes'] ?? null,
            ], fn ($v) => $v !== null),
            // A reschedule invalidates the prior confirmation.
            ['status' => 'pending', 'confirmed_at' => null],
        ));

        $interview->application->jobSeeker->user->notify(new InterviewScheduled($interview->fresh(), rescheduled: true));

        return $interview->fresh();
    }

    /**
     * Seeker confirms attendance. Notifies the employer who scheduled it.
     */
    public function confirm(InterviewSchedule $interview): InterviewSchedule
    {
        $interview->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $this->notifyEmployer($interview);

        return $interview->fresh();
    }

    /**
     * Cancel the interview (by either party). Notifies the employer.
     */
    public function cancel(InterviewSchedule $interview): InterviewSchedule
    {
        $interview->update(['status' => 'cancelled']);
        $this->notifyEmployer($interview);

        return $interview->fresh();
    }

    private function notifyEmployer(InterviewSchedule $interview): void
    {
        $employerUser = $interview->application->job?->employer?->user;
        $employerUser?->notify(new InterviewStatusChanged($interview));
    }
}
