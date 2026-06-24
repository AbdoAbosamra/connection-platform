<?php

namespace App\Notifications;

use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to the employer when the seeker confirms or declines an interview.
 * Database channel only.
 */
class InterviewStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private InterviewSchedule $interview) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'interview_'.$this->interview->status,
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->job_application_id,
            'status' => $this->interview->status,
            'job_title' => $this->interview->application->job?->title ?? 'Unknown Position',
        ];
    }
}
