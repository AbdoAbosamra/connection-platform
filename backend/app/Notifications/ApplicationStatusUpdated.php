<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private const STATUS_LABELS = [
        'viewed' => 'Your application has been viewed',
        'shortlisted' => 'Great news — you\'ve been shortlisted!',
        'interview_scheduled' => 'You\'ve been invited for an interview',
        'offer_extended' => 'An offer has been extended to you!',
        'rejected' => 'Your application was not selected',
        'hired' => 'Congratulations! You\'ve been hired!',
    ];

    public function __construct(private JobApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = self::STATUS_LABELS[$this->application->status] ?? 'Your application status has changed';
        // Null-safe: job or employer may be soft-deleted by the time the queued
        // notification fires. Fall back to generic strings to avoid TypeError.
        $jobTitle = $this->application->job?->title ?? 'a position';
        $company = $this->application->job?->employer?->company_name ?? 'the company';

        return (new MailMessage)
            ->subject("Application Update: {$jobTitle} at {$company}")
            ->greeting("Hello {$notifiable->name}!")
            ->line($label)
            ->line("Position: **{$jobTitle}** at **{$company}**")
            ->action('View Application', url("/job-seeker/applications/{$this->application->id}"))
            ->line('Log in to see more details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_status_updated',
            'application_id' => $this->application->id,
            'status' => $this->application->status,
            'job_title' => $this->application->job?->title ?? 'Unknown Position',
            'company_name' => $this->application->job?->employer?->company_name ?? 'Unknown Company',
        ];
    }
}
