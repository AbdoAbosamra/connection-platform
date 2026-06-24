<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_with_token_passes_active_middleware(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk();
    }

    public function test_suspended_user_with_valid_token_is_blocked_by_active_middleware(): void
    {
        // The account holds a valid token (issued before suspension) but is then
        // suspended. EnsureActive must reject the request on the next call.
        // (We suspend before the first authenticated request so the Sanctum guard
        // resolves the already-suspended user freshly — the guard caches the user
        // within a single test process, which would otherwise mask the change.)
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['is_active' => false]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertStatus(403)
            ->assertJsonStructure(['message']);
    }

    public function test_role_middleware_blocks_wrong_role(): void
    {
        $seeker = User::factory()->jobSeeker()->create();
        $token = $seeker->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_unauthenticated_requests_to_protected_routes_are_rejected(): void
    {
        $this->getJson('/api/employer/jobs')->assertUnauthorized();
        $this->getJson('/api/job-seeker/applications')->assertUnauthorized();
        $this->getJson('/api/admin/users')->assertUnauthorized();
    }
}
