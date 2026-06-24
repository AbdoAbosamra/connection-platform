<?php

namespace Tests\Feature\Admin;

use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ModerationActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function flagJob(Job $job, string $reason = 'spam'): Report
    {
        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/reports', ['type' => 'job', 'id' => $job->id, 'reason' => $reason])->assertCreated();

        return Report::where('reportable_id', $job->id)->latest()->first();
    }

    // ── Auto-prioritisation ──────────────────────────────────────────────────

    public function test_high_harm_reason_is_auto_prioritised_high(): void
    {
        $job = Job::factory()->create();
        $report = $this->flagJob($job, 'scam');

        $this->assertEquals('high', $report->priority);
    }

    public function test_target_is_auto_escalated_to_critical_after_three_open_flags(): void
    {
        $job = Job::factory()->create();
        // Three distinct reporters flag the same job.
        foreach (range(1, 3) as $_) {
            $this->flagJob($job, 'spam');
        }

        $this->assertEquals(3, Report::where('reportable_id', $job->id)->where('priority', 'critical')->count());
    }

    // ── Admin actions + audit log ────────────────────────────────────────────

    public function test_suspension_action_deactivates_user_logs_and_notifies(): void
    {
        Notification::fake();
        $badUser = User::factory()->jobSeeker()->create(['is_active' => true]);
        $report = Report::factory()->forUser($badUser)->create();

        Sanctum::actingAs($this->admin());
        $this->postJson("/api/admin/reports/{$report->id}/action", [
            'action' => 'suspension',
            'notes' => 'Confirmed scam account.',
        ])->assertCreated();

        $this->assertFalse($badUser->fresh()->is_active);
        $this->assertEquals('resolved', $report->fresh()->status);
        $this->assertDatabaseHas('moderation_logs', [
            'user_id' => $badUser->id, 'report_id' => $report->id, 'action' => 'suspension',
        ]);
        Notification::assertSentTo($badUser, ModerationActionNotification::class);
    }

    public function test_content_removed_action_soft_deletes_the_job(): void
    {
        $employer = EmployerProfile::factory()->create();
        $job = Job::factory()->create(['employer_profile_id' => $employer->id]);
        $report = Report::factory()->create([
            'reportable_type' => Job::class, 'reportable_id' => $job->id, 'status' => 'open',
        ]);

        Sanctum::actingAs($this->admin());
        $this->postJson("/api/admin/reports/{$report->id}/action", ['action' => 'content_removed'])
            ->assertCreated();

        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
        $this->assertEquals('resolved', $report->fresh()->status);
        $this->assertDatabaseHas('moderation_logs', ['report_id' => $report->id, 'action' => 'content_removed']);
    }

    public function test_dismissal_action_marks_report_dismissed(): void
    {
        $report = Report::factory()->create(['status' => 'open']);

        Sanctum::actingAs($this->admin());
        $this->postJson("/api/admin/reports/{$report->id}/action", ['action' => 'dismissal'])
            ->assertCreated();

        $this->assertEquals('dismissed', $report->fresh()->status);
    }

    public function test_invalid_action_is_rejected(): void
    {
        $report = Report::factory()->create();
        Sanctum::actingAs($this->admin());

        $this->postJson("/api/admin/reports/{$report->id}/action", ['action' => 'nuke'])
            ->assertStatus(422)->assertJsonValidationErrors('action');
    }

    // ── Dashboard queries ────────────────────────────────────────────────────

    public function test_most_flagged_returns_jobs_ordered_by_flag_count(): void
    {
        $hot = Job::factory()->create(['title' => 'Hot Scam']);
        $mild = Job::factory()->create(['title' => 'Mild']);
        Report::factory()->count(4)->create(['reportable_type' => Job::class, 'reportable_id' => $hot->id, 'status' => 'open']);
        Report::factory()->count(1)->create(['reportable_type' => Job::class, 'reportable_id' => $mild->id, 'status' => 'open']);

        Sanctum::actingAs($this->admin());
        $res = $this->getJson('/api/admin/moderation/most-flagged?type=job')->assertOk();

        $res->assertJsonPath('results.0.id', $hot->id)
            ->assertJsonPath('results.0.flags', 4)
            ->assertJsonPath('results.0.label', 'Hot Scam');
    }

    public function test_moderation_logs_are_listed_for_accountability(): void
    {
        ModerationLog::factory()->count(3)->create();

        Sanctum::actingAs($this->admin());
        $this->getJson('/api/admin/moderation/logs')->assertOk()->assertJsonCount(3, 'data');
    }

    // ── Authorisation ────────────────────────────────────────────────────────

    public function test_non_admin_cannot_use_moderation_endpoints(): void
    {
        $report = Report::factory()->create();
        Sanctum::actingAs(User::factory()->employer()->create());

        $this->postJson("/api/admin/reports/{$report->id}/action", ['action' => 'dismissal'])->assertForbidden();
        $this->getJson('/api/admin/moderation/most-flagged')->assertForbidden();
        $this->getJson('/api/admin/moderation/logs')->assertForbidden();
    }
}
