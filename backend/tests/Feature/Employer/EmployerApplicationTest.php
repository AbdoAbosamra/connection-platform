<?php

namespace Tests\Feature\Employer;

use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\ApplicationReceived;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployerApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function employerWithJob(): array
    {
        $user = User::factory()->employer()->create();
        $profile = EmployerProfile::factory()->create(['user_id' => $user->id]);
        $job = Job::factory()->active()->create(['employer_profile_id' => $profile->id]);

        return [$user, $job];
    }

    public function test_employer_sees_only_applications_to_own_jobs(): void
    {
        [$owner, $ownJob] = $this->employerWithJob();
        [, $otherJob] = $this->employerWithJob();

        JobApplication::factory()->count(2)->create(['job_id' => $ownJob->id]);
        JobApplication::factory()->count(3)->create(['job_id' => $otherJob->id]);

        Sanctum::actingAs($owner);
        $this->getJson('/api/employer/applications')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_viewing_an_application_marks_it_viewed(): void
    {
        [$owner, $job] = $this->employerWithJob();
        $app = JobApplication::factory()->create(['job_id' => $job->id, 'status' => 'submitted']);

        Sanctum::actingAs($owner);
        $this->getJson("/api/employer/applications/{$app->id}")->assertOk();

        $app->refresh();
        $this->assertNotNull($app->viewed_at);
        $this->assertEquals('viewed', $app->status);
    }

    public function test_employer_can_update_application_status_and_seeker_is_notified(): void
    {
        Notification::fake();
        [$owner, $job] = $this->employerWithJob();
        $app = JobApplication::factory()->create(['job_id' => $job->id, 'status' => 'submitted']);

        Sanctum::actingAs($owner);
        $this->patchJson("/api/employer/applications/{$app->id}/status", [
            'status' => 'shortlisted',
            'notes' => 'Strong candidate.',
        ])->assertOk()->assertJsonPath('application.status', 'shortlisted');

        Notification::assertSentTo($app->jobSeeker->user, ApplicationStatusUpdated::class);
    }

    public function test_invalid_status_is_rejected(): void
    {
        [$owner, $job] = $this->employerWithJob();
        $app = JobApplication::factory()->create(['job_id' => $job->id]);

        Sanctum::actingAs($owner);
        $this->patchJson("/api/employer/applications/{$app->id}/status", ['status' => 'bogus'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_employer_cannot_update_application_for_another_employer(): void
    {
        [, $job] = $this->employerWithJob();
        $app = JobApplication::factory()->create(['job_id' => $job->id]);

        $attacker = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $attacker->id]);

        Sanctum::actingAs($attacker);
        $this->patchJson("/api/employer/applications/{$app->id}/status", ['status' => 'shortlisted'])
            ->assertForbidden();
    }

    public function test_applying_creates_a_database_notification_for_the_employer(): void
    {
        [$owner, $job] = $this->employerWithJob();
        $seekerUser = User::factory()->jobSeeker()->create();
        $seeker = JobSeekerProfile::factory()->create(['user_id' => $seekerUser->id]);

        Sanctum::actingAs($seekerUser);
        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", [
            'cover_letter' => str_repeat('I am a great and enthusiastic fit for this remote role. ', 3),
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $owner->id,
            'notifiable_type' => User::class,
            'type' => ApplicationReceived::class,
        ]);
    }
}
