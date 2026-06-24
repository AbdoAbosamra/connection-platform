<?php

namespace Tests\Feature\Admin;

use App\Models\Job;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        Sanctum::actingAs(User::factory()->employer()->create());
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->getJson('/api/admin/jobs')->assertForbidden();
    }

    public function test_admin_can_list_and_filter_users(): void
    {
        Sanctum::actingAs($this->admin());
        User::factory()->count(3)->employer()->create();
        User::factory()->count(2)->jobSeeker()->create();

        $this->getJson('/api/admin/users?role=employer')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_suspend_and_reactivate_a_user(): void
    {
        Sanctum::actingAs($this->admin());
        $user = User::factory()->create(['is_active' => true]);

        $this->patchJson("/api/admin/users/{$user->id}/toggle-active")
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/admin/users/{$admin->id}")->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_admin_cannot_delete_another_admin(): void
    {
        Sanctum::actingAs($this->admin());
        $other = User::factory()->admin()->create();

        $this->deleteJson("/api/admin/users/{$other->id}")->assertForbidden();
    }

    public function test_admin_can_delete_a_regular_user(): void
    {
        Sanctum::actingAs($this->admin());
        $seeker = User::factory()->jobSeeker()->create();

        $this->deleteJson("/api/admin/users/{$seeker->id}")->assertOk();
        $this->assertSoftDeleted('users', ['id' => $seeker->id]);
    }

    public function test_admin_jobs_listing_includes_soft_deleted_and_supports_restore(): void
    {
        Sanctum::actingAs($this->admin());
        $job = Job::factory()->create();
        $job->delete();

        // Admin listing uses withTrashed — the deleted job is still visible.
        $this->getJson('/api/admin/jobs')->assertOk()->assertJsonCount(1, 'data');

        $this->postJson("/api/admin/jobs/{$job->id}/restore")->assertOk();
        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'deleted_at' => null]);
    }

    public function test_admin_delete_job_is_soft_delete(): void
    {
        Sanctum::actingAs($this->admin());
        $job = Job::factory()->create();

        $this->deleteJson("/api/admin/jobs/{$job->id}")->assertOk();
        $this->assertSoftDeleted('jobs', ['id' => $job->id]);
    }

    public function test_admin_can_toggle_featured_flag(): void
    {
        Sanctum::actingAs($this->admin());
        $job = Job::factory()->create(['is_featured' => false]);

        $this->patchJson("/api/admin/jobs/{$job->id}/feature")
            ->assertOk()
            ->assertJsonPath('job.is_featured', true);
    }

    public function test_resolving_a_report_is_idempotent(): void
    {
        Sanctum::actingAs($this->admin());
        $report = Report::factory()->create(['status' => 'open']);

        $this->patchJson("/api/admin/reports/{$report->id}/resolve", ['status' => 'resolved'])
            ->assertOk()
            ->assertJsonPath('report.status', 'resolved');

        // Second attempt must be rejected — the report is already closed.
        $this->patchJson("/api/admin/reports/{$report->id}/resolve", ['status' => 'dismissed'])
            ->assertStatus(422);
    }
}
