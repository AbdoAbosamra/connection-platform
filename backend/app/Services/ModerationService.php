<?php

namespace App\Services;

use App\Models\Job;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ModerationActionNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModerationService
{
    /**
     * Auto-prioritise a freshly created flag and escalate the whole target to
     * 'critical' once it crosses the repeat-offender threshold. This is what
     * makes bad actors surface automatically on the dashboard.
     */
    public function prioritiseNewFlag(Report $report): void
    {
        $report->update(['priority' => Report::priorityForReason($report->reason)]);

        $openCount = Report::open()
            ->where('reportable_type', $report->reportable_type)
            ->where('reportable_id', $report->reportable_id)
            ->count();

        if ($openCount >= Report::AUTO_ESCALATE_THRESHOLD) {
            Report::open()
                ->where('reportable_type', $report->reportable_type)
                ->where('reportable_id', $report->reportable_id)
                ->update(['priority' => 'critical']);
        }
    }

    /**
     * Apply an admin moderation action, record it in the audit trail, and notify
     * the affected user. Runs in a transaction so the state change and the log
     * entry commit together.
     */
    public function act(User $admin, Report $report, string $action, ?string $notes = null): ModerationLog
    {
        return DB::transaction(function () use ($admin, $report, $action, $notes) {
            $target = $report->reportable; // morphTo: User or Job
            $affectedUser = $this->affectedUser($target);

            match ($action) {
                'warning' => $this->resolveReport($report, $admin, 'resolved', $notes),
                'suspension' => $this->suspend($affectedUser, $report, $admin, $notes),
                'reinstatement' => $this->reinstate($affectedUser),
                'dismissal' => $this->resolveReport($report, $admin, 'dismissed', $notes),
                'content_removed' => $this->removeContent($target, $report, $admin, $notes),
                'note' => null, // internal note only — no state change
                default => null,
            };

            $log = ModerationLog::create([
                'moderator_id' => $admin->id,
                'user_id' => $affectedUser?->id,
                'report_id' => $report->id,
                'action' => $action,
                'notes' => $notes,
                'metadata' => [
                    'reason' => $report->reason,
                    'target_type' => class_basename($report->reportable_type),
                    'target_id' => $report->reportable_id,
                ],
            ]);

            // Notify the affected user of account-level actions (after commit-safe
            // state changes above). Sync queue runs inline; DB channel records it.
            if ($affectedUser && in_array($action, ['warning', 'suspension', 'reinstatement'], true)) {
                $affectedUser->notify(new ModerationActionNotification($action, $notes));
            }

            return $log;
        });
    }

    /**
     * The "most frequently flagged" content/users — the core dashboard query.
     * Index-only on reports_target_status (reportable_type, reportable_id, status).
     *
     * @return Collection<int, object>
     */
    public function mostFlagged(string $type = 'job', int $limit = 20, bool $openOnly = true)
    {
        $class = $type === 'user' ? User::class : Job::class;

        $rows = Report::query()
            ->selectRaw('reportable_id, COUNT(*) AS flags, MAX(created_at) AS last_flagged')
            ->selectRaw("SUM(status = 'open') AS open_flags")
            ->where('reportable_type', $class)
            ->when($openOnly, fn ($q) => $q->where('status', 'open'))
            ->groupBy('reportable_id')
            ->orderByDesc('flags')
            ->limit($limit)
            ->get();

        // Hydrate the target so the dashboard can show a title/name in one pass.
        $targets = $class::whereIn('id', $rows->pluck('reportable_id'))->get()->keyBy('id');

        return $rows->map(function ($row) use ($targets, $type) {
            $target = $targets->get($row->reportable_id);

            return [
                'id' => $row->reportable_id,
                'type' => $type,
                'label' => $type === 'user' ? $target?->name : $target?->title,
                'flags' => (int) $row->flags,
                'open_flags' => (int) $row->open_flags,
                'last_flagged' => $row->last_flagged,
            ];
        });
    }

    // ── internal effects ─────────────────────────────────────────────────────

    private function affectedUser(mixed $target): ?User
    {
        if ($target instanceof User) {
            return $target;
        }
        if ($target instanceof Job) {
            return $target->employer?->user;
        }

        return null;
    }

    private function resolveReport(Report $report, User $admin, string $status, ?string $notes): void
    {
        $report->update([
            'status' => $status,
            'resolved_by' => $admin->id,
            'resolution_note' => $notes,
            'resolved_at' => now(),
        ]);
    }

    private function suspend(?User $user, Report $report, User $admin, ?string $notes): void
    {
        $user?->update(['is_active' => false]);
        $this->resolveReport($report, $admin, 'resolved', $notes);
    }

    private function reinstate(?User $user): void
    {
        $user?->update(['is_active' => true]);
    }

    private function removeContent(mixed $target, Report $report, User $admin, ?string $notes): void
    {
        if ($target instanceof Job) {
            $target->delete(); // soft delete — admins can restore later
        }
        $this->resolveReport($report, $admin, 'resolved', $notes);
    }
}
