<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use App\Models\Message;
use App\Models\SavedJob;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserErasureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    // ── Guards ───────────────────────────────────────────────────────────────

    public function test_admin_cannot_erase_themselves(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/admin/users/{$admin->id}/forget")->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_admin_cannot_erase_another_admin(): void
    {
        Sanctum::actingAs($this->admin());
        $other = User::factory()->admin()->create();

        $this->deleteJson("/api/admin/users/{$other->id}/forget")->assertForbidden();
    }

    public function test_non_admin_cannot_call_the_erasure_endpoint(): void
    {
        Sanctum::actingAs(User::factory()->employer()->create());
        $victim = User::factory()->jobSeeker()->create();

        $this->deleteJson("/api/admin/users/{$victim->id}/forget")->assertForbidden();
    }

    // ── Job seeker erasure ───────────────────────────────────────────────────

    public function test_erasing_a_job_seeker_anonymises_pii_and_scrubs_content(): void
    {
        $seekerUser = User::factory()->jobSeeker()->create(['name' => 'Jane Real', 'email' => 'jane@gmail.com', 'phone' => '+155500001']);
        $profile = JobSeekerProfile::factory()->create([
            'user_id' => $seekerUser->id,
            'headline' => 'Senior Dev',
            'bio' => 'My private bio',
            'resume' => 'resumes/jane.pdf',
            'profile_complete' => true,
        ]);
        $profile->skills()->attach(Skill::factory()->create()->id);
        $job = Job::factory()->active()->create();
        $application = JobApplication::factory()->create([
            'job_id' => $job->id,
            'job_seeker_profile_id' => $profile->id,
            'cover_letter' => 'Please hire me, here is personal info',
            'resume_snapshot' => 'applications/jane.pdf',
        ]);
        SavedJob::factory()->create(['job_seeker_profile_id' => $profile->id]);
        $seekerUser->createToken('t');

        Sanctum::actingAs($this->admin());
        $this->deleteJson("/api/admin/users/{$seekerUser->id}/forget")->assertOk();

        // User row: pseudonymised + soft-deleted.
        $this->assertSoftDeleted('users', ['id' => $seekerUser->id]);
        $fresh = User::withTrashed()->find($seekerUser->id);
        $this->assertEquals('Deleted User', $fresh->name);
        $this->assertEquals("deleted_{$seekerUser->id}@anonymized.invalid", $fresh->email);
        $this->assertNull($fresh->phone);
        $this->assertFalse($fresh->is_active);

        // Profile scrubbed + hidden from the directory.
        $this->assertDatabaseHas('job_seeker_profiles', [
            'id' => $profile->id, 'headline' => null, 'bio' => null, 'resume' => null, 'profile_complete' => false,
        ]);
        // Skills detached, saved jobs removed, tokens revoked.
        $this->assertEquals(0, $profile->fresh()->skills()->count());
        $this->assertDatabaseCount('saved_jobs', 0);
        $this->assertEquals(0, $fresh->tokens()->count());

        // Application row KEPT (employer's record) but personal content scrubbed.
        $this->assertDatabaseHas('job_applications', [
            'id' => $application->id, 'cover_letter' => null, 'resume_snapshot' => null,
        ]);
    }

    public function test_erased_user_can_no_longer_log_in(): void
    {
        $seeker = User::factory()->jobSeeker()->create(['email' => 'gone@gmail.com', 'password' => bcrypt('password')]);
        JobSeekerProfile::factory()->create(['user_id' => $seeker->id]);

        Sanctum::actingAs($this->admin());
        $this->deleteJson("/api/admin/users/{$seeker->id}/forget")->assertOk();

        // Original credentials no longer resolve to an account.
        $this->postJson('/api/auth/login', ['email' => 'gone@gmail.com', 'password' => 'password'])
            ->assertStatus(422);
    }

    public function test_erasing_a_seeker_deletes_their_notifications(): void
    {
        $seeker = User::factory()->jobSeeker()->create();
        JobSeekerProfile::factory()->create(['user_id' => $seeker->id]);
        $seeker->notify(new class extends Notification
        {
            public function via($n): array
            {
                return ['database'];
            }

            public function toArray($n): array
            {
                return ['x' => 1];
            }
        });
        $this->assertEquals(1, $seeker->notifications()->count());

        Sanctum::actingAs($this->admin());
        $this->deleteJson("/api/admin/users/{$seeker->id}/forget")->assertOk();

        $this->assertDatabaseCount('notifications', 0);
    }

    // ── Employer erasure ─────────────────────────────────────────────────────

    public function test_erasing_an_employer_anonymises_company_and_closes_jobs(): void
    {
        $employerUser = User::factory()->employer()->create();
        $profile = EmployerProfile::factory()->create(['user_id' => $employerUser->id, 'company_name' => 'Acme Real']);
        $job = Job::factory()->active()->create(['employer_profile_id' => $profile->id]);
        // An innocent seeker applied to this job — their record must survive.
        $application = JobApplication::factory()->create(['job_id' => $job->id]);

        Sanctum::actingAs($this->admin());
        $this->deleteJson("/api/admin/users/{$employerUser->id}/forget")->assertOk();

        $this->assertDatabaseHas('employer_profiles', ['id' => $profile->id, 'company_name' => 'Deleted Company']);
        // Jobs kept but closed.
        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'status' => 'closed']);
        // Innocent applicant's record preserved.
        $this->assertDatabaseHas('job_applications', ['id' => $application->id]);
    }

    // ── Messages ─────────────────────────────────────────────────────────────

    public function test_messages_authored_by_the_user_are_anonymised_others_preserved(): void
    {
        $employerUser = User::factory()->employer()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $employerUser->id]);
        $seekerUser = User::factory()->jobSeeker()->create();
        $seeker = JobSeekerProfile::factory()->create(['user_id' => $seekerUser->id]);
        $conversation = Conversation::factory()->create([
            'employer_profile_id' => $employer->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);
        $mine = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_id' => $seekerUser->id, 'body' => 'My secret message']);
        $theirs = Message::factory()->create(['conversation_id' => $conversation->id, 'sender_id' => $employerUser->id, 'body' => 'Employer reply']);

        Sanctum::actingAs($this->admin());
        $this->deleteJson("/api/admin/users/{$seekerUser->id}/forget")->assertOk();

        // The erased user's message is scrubbed; the conversation row survives.
        $this->assertDatabaseHas('messages', ['id' => $mine->id, 'body' => '[User Account Deleted]']);
        // The other participant's message is untouched.
        $this->assertDatabaseHas('messages', ['id' => $theirs->id, 'body' => 'Employer reply']);
    }
}
