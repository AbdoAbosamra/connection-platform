<?php

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\InterviewSchedule;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Notifications\InterviewScheduled;
use App\Notifications\InterviewStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InterviewTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(): array
    {
        $employerUser = User::factory()->employer()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $employerUser->id]);
        $job = Job::factory()->active()->create(['employer_profile_id' => $employer->id]);

        $seekerUser = User::factory()->jobSeeker()->create();
        $seeker = JobSeekerProfile::factory()->create(['user_id' => $seekerUser->id]);
        $application = JobApplication::factory()->create([
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        return compact('employerUser', 'seekerUser', 'application');
    }

    public function test_employer_can_schedule_an_interview_and_seeker_is_notified(): void
    {
        Notification::fake();
        ['employerUser' => $eu, 'seekerUser' => $su, 'application' => $app] = $this->scenario();

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/applications/{$app->id}/interviews", [
            'scheduled_at' => now()->addDays(2)->toDateTimeString(),
            'format' => 'video',
            'meeting_link' => 'https://meet.example.com/abc',
        ])->assertCreated()->assertJsonPath('interview.status', 'pending');

        $this->assertEquals('interview_scheduled', $app->fresh()->status);
        Notification::assertSentTo($su, InterviewScheduled::class);
    }

    public function test_video_interview_requires_a_meeting_link(): void
    {
        ['employerUser' => $eu, 'application' => $app] = $this->scenario();

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/applications/{$app->id}/interviews", [
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'format' => 'video',
        ])->assertStatus(422)->assertJsonValidationErrors('meeting_link');
    }

    public function test_scheduled_time_must_be_in_the_future(): void
    {
        ['employerUser' => $eu, 'application' => $app] = $this->scenario();

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/applications/{$app->id}/interviews", [
            'scheduled_at' => now()->subDay()->toDateTimeString(),
            'format' => 'phone',
        ])->assertStatus(422)->assertJsonValidationErrors('scheduled_at');
    }

    public function test_employer_cannot_schedule_interview_for_foreign_application(): void
    {
        ['application' => $app] = $this->scenario();
        $intruder = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $intruder->id]);

        Sanctum::actingAs($intruder);
        $this->postJson("/api/employer/applications/{$app->id}/interviews", [
            'scheduled_at' => now()->addDay()->toDateTimeString(),
            'format' => 'phone',
        ])->assertForbidden();
    }

    public function test_seeker_can_confirm_interview_and_employer_is_notified(): void
    {
        Notification::fake();
        ['employerUser' => $eu, 'seekerUser' => $su, 'application' => $app] = $this->scenario();
        $interview = InterviewSchedule::factory()->create([
            'job_application_id' => $app->id,
            'scheduled_by' => $eu->id,
        ]);

        Sanctum::actingAs($su);
        $this->patchJson("/api/job-seeker/interviews/{$interview->id}/confirm")
            ->assertOk()
            ->assertJsonPath('interview.status', 'confirmed');

        Notification::assertSentTo($eu, InterviewStatusChanged::class);
    }

    public function test_seeker_can_list_their_interviews(): void
    {
        ['seekerUser' => $su, 'application' => $app] = $this->scenario();
        InterviewSchedule::factory()->create(['job_application_id' => $app->id]);

        Sanctum::actingAs($su);
        $this->getJson('/api/job-seeker/interviews')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_seeker_cannot_confirm_another_seekers_interview(): void
    {
        ['application' => $app] = $this->scenario();
        $interview = InterviewSchedule::factory()->create(['job_application_id' => $app->id]);

        $stranger = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $stranger->id]);

        Sanctum::actingAs($stranger);
        $this->patchJson("/api/job-seeker/interviews/{$interview->id}/confirm")->assertForbidden();
    }
}
