<?php

namespace Tests\Feature\Employer;

use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployerJobTest extends TestCase
{
    use RefreshDatabase;

    private function employer(array $profile = []): User
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->create(array_merge(['user_id' => $user->id], $profile));

        return $user;
    }

    private function validJobPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Senior Laravel Engineer',
            'description' => str_repeat('We are hiring a great engineer to build wonderful things. ', 5),
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'experience_level' => 'senior',
            'status' => 'active',
        ], $overrides);
    }

    public function test_employer_can_post_a_job_and_a_credit_is_consumed(): void
    {
        $user = $this->employer(['subscription_tier' => 'free', 'job_post_credits' => 3]);
        Sanctum::actingAs($user);

        $this->postJson('/api/employer/jobs', $this->validJobPayload())
            ->assertCreated()
            ->assertJsonPath('job.title', 'Senior Laravel Engineer')
            ->assertJsonPath('job.location_type', 'remote');

        $this->assertEquals(2, $user->employerProfile->fresh()->job_post_credits);
    }

    public function test_employer_without_credits_cannot_post(): void
    {
        $user = $this->employer(['subscription_tier' => 'free', 'job_post_credits' => 0]);
        Sanctum::actingAs($user);

        $this->postJson('/api/employer/jobs', $this->validJobPayload())
            ->assertStatus(422)
            ->assertJsonValidationErrors('credits');

        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_job_creation_validates_minimum_description_length(): void
    {
        Sanctum::actingAs($this->employer());

        $this->postJson('/api/employer/jobs', $this->validJobPayload(['description' => 'too short']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('description');
    }

    public function test_job_post_syncs_skills(): void
    {
        $user = $this->employer();
        Sanctum::actingAs($user);
        $skill = Skill::factory()->create();

        $this->postJson('/api/employer/jobs', $this->validJobPayload([
            'skills' => [['id' => $skill->id, 'is_required' => true]],
        ]))->assertCreated()
            ->assertJsonPath('job.skills.0.id', $skill->id);
    }

    public function test_employer_only_sees_own_jobs(): void
    {
        $mine = $this->employer();
        $other = $this->employer();
        Job::factory()->count(2)->create(['employer_profile_id' => $mine->employerProfile->id]);
        Job::factory()->count(3)->create(['employer_profile_id' => $other->employerProfile->id]);

        Sanctum::actingAs($mine);
        $this->getJson('/api/employer/jobs')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_employer_cannot_view_or_update_another_employers_job(): void
    {
        $owner = $this->employer();
        $attacker = $this->employer();
        $job = Job::factory()->create(['employer_profile_id' => $owner->employerProfile->id]);

        Sanctum::actingAs($attacker);
        $this->getJson("/api/employer/jobs/{$job->id}")->assertForbidden();
        $this->putJson("/api/employer/jobs/{$job->id}", ['title' => 'Hijacked'])->assertForbidden();
        $this->deleteJson("/api/employer/jobs/{$job->id}")->assertForbidden();
    }

    public function test_employer_can_toggle_job_status(): void
    {
        $user = $this->employer();
        $job = Job::factory()->active()->create(['employer_profile_id' => $user->employerProfile->id]);

        Sanctum::actingAs($user);
        $this->patchJson("/api/employer/jobs/{$job->id}/toggle-status")
            ->assertOk()
            ->assertJsonPath('status', 'paused');
    }

    public function test_employer_can_soft_delete_own_job(): void
    {
        $user = $this->employer();
        $job = Job::factory()->create(['employer_profile_id' => $user->employerProfile->id]);

        Sanctum::actingAs($user);
        $this->deleteJson("/api/employer/jobs/{$job->id}")->assertOk();
        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    public function test_job_seeker_cannot_access_employer_routes(): void
    {
        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/employer/jobs', $this->validJobPayload())->assertForbidden();
        $this->getJson('/api/employer/jobs')->assertForbidden();
    }
}
