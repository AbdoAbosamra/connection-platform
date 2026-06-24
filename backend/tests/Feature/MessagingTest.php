<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private function pair(): array
    {
        $employerUser = User::factory()->employer()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $employerUser->id]);
        $seekerUser = User::factory()->jobSeeker()->create();
        $seeker = JobSeekerProfile::factory()->create(['user_id' => $seekerUser->id]);

        return compact('employerUser', 'employer', 'seekerUser', 'seeker');
    }

    public function test_employer_can_initiate_a_conversation_with_a_seeker(): void
    {
        ['employerUser' => $eu, 'seeker' => $seeker] = $this->pair();
        Sanctum::actingAs($eu);

        $this->postJson('/api/employer/conversations', [
            'job_seeker_profile_id' => $seeker->id,
        ])->assertOk()->assertJsonStructure(['conversation' => ['id']]);

        $this->assertDatabaseHas('conversations', ['job_seeker_profile_id' => $seeker->id]);
    }

    public function test_initiating_is_idempotent_for_same_pair_without_job(): void
    {
        ['employerUser' => $eu, 'seeker' => $seeker] = $this->pair();
        Sanctum::actingAs($eu);

        $this->postJson('/api/employer/conversations', ['job_seeker_profile_id' => $seeker->id])->assertOk();
        $this->postJson('/api/employer/conversations', ['job_seeker_profile_id' => $seeker->id])->assertOk();

        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_employer_cannot_open_thread_for_another_employers_job(): void
    {
        ['employerUser' => $eu, 'seeker' => $seeker] = $this->pair();
        $foreignJob = Job::factory()->create(); // belongs to a different employer
        Sanctum::actingAs($eu);

        $this->postJson('/api/employer/conversations', [
            'job_seeker_profile_id' => $seeker->id,
            'job_id' => $foreignJob->id,
        ])->assertForbidden();
    }

    public function test_both_participants_can_send_and_read_messages(): void
    {
        ['employerUser' => $eu, 'employer' => $employer, 'seekerUser' => $su, 'seeker' => $seeker] = $this->pair();
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/conversations/{$conversation->id}/messages", [
            'body' => 'Hello, are you available for a chat?',
        ])->assertCreated();

        Sanctum::actingAs($su);
        $this->postJson("/api/job-seeker/conversations/{$conversation->id}/messages", [
            'body' => 'Yes, I am interested!',
        ])->assertCreated();

        $this->assertDatabaseCount('messages', 2);
    }

    public function test_attachment_only_message_is_accepted(): void
    {
        Storage::fake('public');
        ['employerUser' => $eu, 'employer' => $employer, 'seeker' => $seeker] = $this->pair();
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        Sanctum::actingAs($eu);
        // No body — just a file. Should succeed (body coerced to empty string).
        $this->postJson("/api/employer/conversations/{$conversation->id}/messages", [
            'attachment' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertCreated();

        $this->assertDatabaseHas('messages', ['conversation_id' => $conversation->id, 'body' => '']);
    }

    public function test_empty_message_with_no_attachment_is_rejected(): void
    {
        ['employerUser' => $eu, 'employer' => $employer, 'seeker' => $seeker] = $this->pair();
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        Sanctum::actingAs($eu);
        $this->postJson("/api/employer/conversations/{$conversation->id}/messages", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('body');
    }

    public function test_outsider_cannot_view_or_send_in_a_conversation(): void
    {
        ['employer' => $employer, 'seeker' => $seeker] = $this->pair();
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        $intruderUser = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $intruderUser->id]);
        Sanctum::actingAs($intruderUser);

        $this->getJson("/api/employer/conversations/{$conversation->id}/messages")->assertForbidden();
        $this->postJson("/api/employer/conversations/{$conversation->id}/messages", ['body' => 'intrusion'])
            ->assertForbidden();
    }

    public function test_unread_count_reflects_messages_from_other_party(): void
    {
        ['employerUser' => $eu, 'employer' => $employer, 'seekerUser' => $su, 'seeker' => $seeker] = $this->pair();
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);
        // Seeker sends two messages; employer should see 2 unread.
        Message::factory()->count(2)->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $su->id,
        ]);

        Sanctum::actingAs($eu);
        $this->getJson('/api/employer/conversations/unread-count')
            ->assertOk()
            ->assertJsonPath('unread', 2);

        // After reading, the count drops to zero.
        $this->getJson("/api/employer/conversations/{$conversation->id}/messages")->assertOk();
        $this->getJson('/api/employer/conversations/unread-count')->assertJsonPath('unread', 0);
    }
}
