<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_responds(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_public_jobs_endpoint_responds(): void
    {
        $this->getJson('/api/jobs')->assertOk();
    }

    public function test_database_migrates_and_factories_work(): void
    {
        $user = User::factory()->employer()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'employer']);

        $job = Job::factory()->create();
        $this->assertDatabaseHas('jobs', ['id' => $job->id]);
    }
}
