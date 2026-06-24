<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_report_a_job_and_admins_are_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $job = Job::factory()->create();

        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/reports', [
            'type' => 'job',
            'id' => $job->id,
            'reason' => 'scam',
            'details' => 'This looks fraudulent.',
        ])->assertCreated();

        $this->assertDatabaseHas('reports', [
            'reportable_type' => Job::class,
            'reportable_id' => $job->id,
            'reason' => 'scam',
            'status' => 'open',
        ]);
        Notification::assertSentTo($admin, NewReportSubmitted::class);
    }

    public function test_user_cannot_report_themselves(): void
    {
        $user = User::factory()->jobSeeker()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/reports', [
            'type' => 'user',
            'id' => $user->id,
            'reason' => 'spam',
        ])->assertStatus(422)->assertJsonValidationErrors('id');
    }

    public function test_duplicate_open_report_is_rejected(): void
    {
        $reporter = User::factory()->jobSeeker()->create();
        $job = Job::factory()->create();
        Report::factory()->create([
            'reporter_id' => $reporter->id,
            'reportable_type' => Job::class,
            'reportable_id' => $job->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($reporter);
        $this->postJson('/api/reports', ['type' => 'job', 'id' => $job->id, 'reason' => 'spam'])
            ->assertStatus(422);
    }

    public function test_reporting_a_nonexistent_target_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/reports', ['type' => 'job', 'id' => 99999, 'reason' => 'spam'])
            ->assertStatus(422)->assertJsonValidationErrors('id');
    }

    public function test_invalid_reason_or_type_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/reports', ['type' => 'planet', 'id' => 1, 'reason' => 'nope'])
            ->assertStatus(422)->assertJsonValidationErrors(['type', 'reason']);
    }

    public function test_guests_cannot_submit_reports(): void
    {
        $this->postJson('/api/reports', ['type' => 'job', 'id' => 1, 'reason' => 'spam'])
            ->assertUnauthorized();
    }

    public function test_community_guideline_reasons_are_accepted(): void
    {
        $job = Job::factory()->create();
        Sanctum::actingAs(User::factory()->jobSeeker()->create());

        // A Fraud-Prevention category reason from the Community Guidelines.
        $this->postJson('/api/reports', [
            'type' => 'job',
            'id' => $job->id,
            'reason' => 'application_fee',
        ])->assertCreated();

        $this->assertDatabaseHas('reports', ['reportable_id' => $job->id, 'reason' => 'application_fee']);
    }
}
