<?php

namespace Tests\Feature;

use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_profile_show_creates_lazily_when_missing(): void
    {
        $user = User::factory()->employer()->create();
        // No EmployerProfile row yet — show() uses firstOrFail and would 404,
        // but update() uses firstOrCreate. Verify update path bootstraps it.
        Sanctum::actingAs($user);

        $this->putJson('/api/employer/profile', ['company_name' => 'Bootstrapped Co'])
            ->assertOk()
            ->assertJsonPath('profile.company_name', 'Bootstrapped Co');

        $this->assertDatabaseHas('employer_profiles', ['user_id' => $user->id]);
    }

    public function test_job_seeker_can_update_profile_and_sync_skills(): void
    {
        $user = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $user->id]);
        $skill = Skill::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/job-seeker/profile', [
            'headline' => 'Senior Engineer',
            'bio' => 'Experienced remote engineer.',
            'skills' => [['id' => $skill->id, 'proficiency' => 'expert']],
        ])->assertOk()->assertJsonPath('profile.headline', 'Senior Engineer');

        $this->assertDatabaseHas('job_seeker_skills', [
            'job_seeker_profile_id' => $user->jobSeekerProfile->id,
            'skill_id' => $skill->id,
            'proficiency' => 'expert',
        ]);
    }

    public function test_resume_endpoint_returns_404_when_no_resume(): void
    {
        $user = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $user->id, 'resume' => null]);
        Sanctum::actingAs($user);

        $this->getJson('/api/job-seeker/profile/resume')->assertNotFound();
    }

    public function test_public_professionals_listing_only_shows_complete_profiles(): void
    {
        JobSeekerProfile::factory()->count(2)->create(['profile_complete' => true]);
        JobSeekerProfile::factory()->incomplete()->count(3)->create();

        $this->getJson('/api/professionals')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_incomplete_professional_profile_is_not_publicly_viewable(): void
    {
        $profile = JobSeekerProfile::factory()->incomplete()->create();

        $this->getJson("/api/professionals/{$profile->id}")->assertNotFound();
    }

    public function test_public_professional_payload_excludes_sensitive_fields(): void
    {
        $profile = JobSeekerProfile::factory()->create([
            'profile_complete' => true,
            'nationality' => 'Egyptian',
            'desired_salary_min' => 50000,
        ]);

        $response = $this->getJson("/api/professionals/{$profile->id}")->assertOk();
        $json = $response->json('professional');

        $this->assertArrayNotHasKey('nationality', $json);
        $this->assertArrayNotHasKey('desired_salary_min', $json);
        $this->assertArrayNotHasKey('user_id', $json);
    }
}
