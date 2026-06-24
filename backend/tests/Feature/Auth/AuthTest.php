<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_seeker_can_register_and_receives_token_and_profile(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Seeker',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'job_seeker',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'email', 'role']]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'job_seeker']);
        $this->assertDatabaseHas('job_seeker_profiles', ['user_id' => $response->json('user.id')]);
    }

    public function test_employer_registration_requires_company_name_and_creates_profile_with_credits(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Acme HR',
            'email' => 'hr@acme.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employer',
        ])->assertStatus(422)->assertJsonValidationErrors('company_name');

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Acme HR',
            'email' => 'hr@acme.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employer',
            'company_name' => 'Acme Inc',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('employer_profiles', [
            'user_id' => $response->json('user.id'),
            'company_name' => 'Acme Inc',
            'job_post_credits' => 10,
        ]);
    }

    public function test_registration_rejects_duplicate_email_and_weak_password(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'Dup',
            'email' => 'taken@example.com',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
            'role' => 'job_seeker',
        ])->assertStatus(422)->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_admin_role_cannot_be_self_assigned_at_registration(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])->assertStatus(422)->assertJsonValidationErrors('role');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'login@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_suspended_user_cannot_login(): void
    {
        User::factory()->suspended()->create(['email' => 'suspended@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'suspended@example.com',
            'password' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_login_revokes_previous_tokens(): void
    {
        $user = User::factory()->create(['email' => 'rotate@example.com']);
        $oldToken = $user->createToken('auth_token')->plainTextToken;

        $this->postJson('/api/auth/login', [
            'email' => 'rotate@example.com',
            'password' => 'password',
        ])->assertOk();

        // The old token should no longer authenticate.
        $this->withHeader('Authorization', "Bearer {$oldToken}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_self_and_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk();

        // The current token row must be deleted server-side. (We assert at the DB
        // layer rather than firing another request, because the Sanctum guard
        // caches the resolved user within a single test process, which would mask
        // the revocation — the real client always sends a fresh request.)
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }
}
