<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Password-reset email that links to the SPA reset page (not the API).
 * The token + email are passed as query params the frontend forwards back to
 * POST /api/auth/reset-password.
 */
class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting("Hello {$notifiable->name}!")
            ->line('You requested a password reset. Click the button below to choose a new password.')
            ->action('Reset Password', $this->url)
            ->line('This link expires in 60 minutes.')
            ->line('If you did not request a password reset, no action is required.');
    }
}
