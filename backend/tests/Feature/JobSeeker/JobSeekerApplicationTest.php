<?php

namespace Tests\Feature\JobSeeker;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobSeekerApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function seeker(): User
    {
        $user = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    private function coverLetter(): string
    {
        return str_repeat('I am genuinely excited about this remote opportunity and a strong fit. ', 3);
    }

    public function test_seeker_can_apply_to_an_active_job(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->active()->create();
        Sanctum::actingAs($seeker);

        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", [
            'cover_letter' => $this->coverLetter(),
            'expected_salary' => 90000,
        ])->assertCreated();

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->jobSeekerProfile->id,
            'status' => 'submitted',
        ]);
        $this->assertEquals(1, $job->fresh()->applications_count);
    }

    public function test_seeker_cannot_apply_twice_to_same_job(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->active()->create();
        Sanctum::actingAs($seeker);

        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", ['cover_letter' => $this->coverLetter()])
            ->assertCreated();

        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", ['cover_letter' => $this->coverLetter()])
            ->assertStatus(422)
            ->assertJsonValidationErrors('job_id');

        $this->assertEquals(1, $job->fresh()->applications_count);
    }

    public function test_seeker_cannot_apply_to_a_closed_job(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->closed()->create();
        Sanctum::actingAs($seeker);

        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", ['cover_letter' => $this->coverLetter()])
            ->assertStatus(422)
            ->assertJsonValidationErrors('job_id');
    }

    public function test_cover_letter_below_minimum_is_rejected(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->active()->create();
        Sanctum::actingAs($seeker);

        $this->postJson("/api/job-seeker/jobs/{$job->id}/apply", ['cover_letter' => 'too short'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cover_letter');
    }

    public function test_seeker_can_withdraw_an_open_application_and_count_decrements(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->active()->create(['applications_count' => 1]);
        $app = JobApplication::factory()->create([
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->jobSeekerProfile->id,
            'status' => 'submitted',
        ]);
        Sanctum::actingAs($seeker);

        $this->patchJson("/api/job-seeker/applications/{$app->id}/withdraw")->assertOk();

        $this->assertEquals('withdrawn', $app->fresh()->status);
        $this->assertEquals(0, $job->fresh()->applications_count);
    }

    public function test_seeker_cannot_withdraw_a_closed_application(): void
    {
        $seeker = $this->seeker();
        $app = JobApplication::factory()->hired()->create([
            'job_seeker_profile_id' => $seeker->jobSeekerProfile->id,
        ]);
        Sanctum::actingAs($seeker);

        $this->patchJson("/api/job-seeker/applications/{$app->id}/withdraw")
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_seeker_cannot_view_or_withdraw_another_seekers_application(): void
    {
        $owner = $this->seeker();
        $attacker = $this->seeker();
        $app = JobApplication::factory()->create([
            'job_seeker_profile_id' => $owner->jobSeekerProfile->id,
        ]);

        Sanctum::actingAs($attacker);
        $this->getJson("/api/job-seeker/applications/{$app->id}")->assertForbidden();
        $this->patchJson("/api/job-seeker/applications/{$app->id}/withdraw")->assertForbidden();
    }

    public function test_seeker_only_lists_own_applications(): void
    {
        $owner = $this->seeker();
        $other = $this->seeker();
        JobApplication::factory()->count(2)->create(['job_seeker_profile_id' => $owner->jobSeekerProfile->id]);
        JobApplication::factory()->count(3)->create(['job_seeker_profile_id' => $other->jobSeekerProfile->id]);

        Sanctum::actingAs($owner);
        $this->getJson('/api/job-seeker/applications')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_seeker_can_save_and_unsave_a_job(): void
    {
        $seeker = $this->seeker();
        $job = Job::factory()->active()->create();
        Sanctum::actingAs($seeker);

        $this->postJson("/api/job-seeker/jobs/{$job->id}/save")->assertOk();
        $this->assertDatabaseHas('saved_jobs', [
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->jobSeekerProfile->id,
        ]);

        // Saving again is idempotent (firstOrCreate) — no duplicate row.
        $this->postJson("/api/job-seeker/jobs/{$job->id}/save")->assertOk();
        $this->assertDatabaseCount('saved_jobs', 1);

        $this->deleteJson("/api/job-seeker/jobs/{$job->id}/save")->assertOk();
        $this->assertDatabaseCount('saved_jobs', 0);
    }

    public function test_employer_cannot_access_job_seeker_routes(): void
    {
        Sanctum::actingAs(User::factory()->employer()->create());
        $this->getJson('/api/job-seeker/applications')->assertForbidden();
    }
}
