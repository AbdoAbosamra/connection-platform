<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to every admin when a user submits a content report.
 * Database channel only — admins see it in their in-app feed, no email spam.
 */
class NewReportSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Report $report) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_submitted',
            'report_id' => $this->report->id,
            'reason' => $this->report->reason,
            'reportable_type' => class_basename($this->report->reportable_type),
            'reportable_id' => $this->report->reportable_id,
        ];
    }
}
