<?php

namespace App\Notifications;

use App\Models\InterviewSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the job seeker when an employer schedules (or reschedules) an interview.
 */
class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private InterviewSchedule $interview, private bool $rescheduled = false) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->interview->application->job?->title ?? 'a position';
        $when = $this->interview->scheduled_at->format('M j, Y \a\t g:i A');
        $verb = $this->rescheduled ? 'rescheduled' : 'scheduled';

        return (new MailMessage)
            ->subject("Interview {$verb}: {$jobTitle}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your interview for **{$jobTitle}** has been {$verb} for **{$when}**.")
            ->line('Format: '.ucfirst(str_replace('_', '-', $this->interview->format)))
            ->action('View Interview', url("/job-seeker/applications/{$this->interview->job_application_id}"))
            ->line('Please confirm your availability.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->rescheduled ? 'interview_rescheduled' : 'interview_scheduled',
            'interview_id' => $this->interview->id,
            'application_id' => $this->interview->job_application_id,
            'scheduled_at' => $this->interview->scheduled_at->toIso8601String(),
            'format' => $this->interview->format,
            'job_title' => $this->interview->application->job?->title ?? 'Unknown Position',
        ];
    }
}
