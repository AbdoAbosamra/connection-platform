<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_sends_reset_notification_for_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'reset@example.com'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_does_not_reveal_unknown_emails(): void
    {
        Notification::fake();

        // Returns the same generic 200 even though no account exists (anti-enumeration).
        $this->postJson('/api/auth/forgot-password', ['email' => 'ghost@example.com'])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertOk();

        // The new password authenticates.
        $this->postJson('/api/auth/login', [
            'email' => 'reset@example.com',
            'password' => 'brand-new-pass',
        ])->assertOk();
    }

    public function test_reset_fails_with_an_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $this->postJson('/api/auth/reset-password', [
            'token' => 'totally-invalid',
            'email' => 'reset@example.com',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_reset_revokes_existing_tokens(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $user->createToken('old')->plainTextToken;
        $token = Password::createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertOk();

        $this->assertEquals(0, $user->tokens()->count());
    }
}
