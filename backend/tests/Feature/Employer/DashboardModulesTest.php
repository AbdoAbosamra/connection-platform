<?php

namespace Tests\Feature\Employer;

use App\Models\EmployerProfile;
use App\Models\InterviewSchedule;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardModulesTest extends TestCase
{
    use RefreshDatabase;

    private function employer(): User
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function test_employer_interviews_index_lists_only_own_interviews(): void
    {
        $me = $this->employer();
        $other = $this->employer();

        $myJob = Job::factory()->create(['employer_profile_id' => $me->employerProfile->id]);
        $myApp = JobApplication::factory()->create(['job_id' => $myJob->id]);
        InterviewSchedule::factory()->create(['job_application_id' => $myApp->id]);

        $otherJob = Job::factory()->create(['employer_profile_id' => $other->employerProfile->id]);
        $otherApp = JobApplication::factory()->create(['job_id' => $otherJob->id]);
        InterviewSchedule::factory()->create(['job_application_id' => $otherApp->id]);

        Sanctum::actingAs($me);

        $this->getJson('/api/employer/interviews')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.application.job.title', $myJob->title);
    }

    public function test_stats_report_international_jobs_flag(): void
    {
        $me = $this->employer();
        Job::factory()->create(['employer_profile_id' => $me->employerProfile->id, 'hiring_mode' => 'local']);
        Sanctum::actingAs($me);

        $this->getJson('/api/employer/stats')
            ->assertOk()
            ->assertJsonPath('has_international_jobs', false);

        Job::factory()->create(['employer_profile_id' => $me->employerProfile->id, 'hiring_mode' => 'international_remote']);

        $this->getJson('/api/employer/stats')
            ->assertOk()
            ->assertJsonPath('has_international_jobs', true);
    }

    public function test_user_can_update_account_name_and_email(): void
    {
        $user = $this->employer();
        Sanctum::actingAs($user);

        $this->patchJson('/api/auth/account', ['name' => 'New Name', 'email' => 'new@corp.com'])
            ->assertOk()
            ->assertJsonPath('user.name', 'New Name')
            ->assertJsonPath('user.email', 'new@corp.com');
    }

    public function test_account_email_must_be_unique(): void
    {
        $taken = $this->employer();
        $user = $this->employer();
        Sanctum::actingAs($user);

        $this->patchJson('/api/auth/account', ['name' => 'X', 'email' => $taken->email])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->employer()->create(['password' => Hash::make('oldpassword')]);
        EmployerProfile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        // Wrong current password → rejected
        $this->patchJson('/api/auth/password', [
            'current_password' => 'wrong',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ])->assertStatus(422)->assertJsonValidationErrors('current_password');

        // Correct current password → succeeds
        $this->patchJson('/api/auth/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ])->assertOk();

        $this->assertTrue(Hash::check('newpassword1', $user->fresh()->password));
    }
}
