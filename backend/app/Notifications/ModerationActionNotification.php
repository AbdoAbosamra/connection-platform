<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a user about a moderation action taken on their account
 * (warning, suspension, reinstatement).
 */
class ModerationActionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $action, private ?string $notes = null) {}

    public function via(object $notifiable): array
    {
        // Suspensions/warnings are emailed; lighter actions stay in-app only.
        // All variants also broadcast for the live notification bell.
        return in_array($this->action, ['warning', 'suspension'], true)
            ? ['mail', 'database', 'broadcast']
            : ['database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->action) {
            'warning' => 'A warning regarding your RemoteArena account',
            'suspension' => 'Your RemoteArena account has been suspended',
            default => 'An update regarding your RemoteArena account',
        };

        $mail = (new MailMessage)->subject($subject)->greeting("Hello {$notifiable->name},");

        $mail->line(match ($this->action) {
            'warning' => 'Our moderation team has issued a warning on your account for activity that may breach our Community Guidelines.',
            'suspension' => 'Your account has been suspended following a review by our moderation team.',
            default => 'Your account status has been updated by our moderation team.',
        });

        if ($this->notes) {
            $mail->line('Details: '.$this->notes);
        }

        return $mail->line('If you believe this was a mistake, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'moderation_'.$this->action,
            'action' => $this->action,
            'notes' => $this->notes,
        ];
    }
}
