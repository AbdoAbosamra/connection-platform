<?php

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmployerVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function registerEmployer(string $email): TestResponse
    {
        return $this->postJson('/api/auth/register', [
            'name' => 'Acme HR',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'employer',
            'company_name' => 'Acme Inc',
        ]);
    }

    private function jobPayload(): array
    {
        return [
            'title' => 'Remote Engineer',
            'description' => str_repeat('A wonderful remote role for a talented engineer. ', 5),
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'experience_level' => 'mid',
            'status' => 'active',
        ];
    }

    public function test_corporate_domain_employer_is_auto_verified(): void
    {
        $res = $this->registerEmployer('hiring@acme-corp.com')->assertCreated();

        $this->assertDatabaseHas('employer_profiles', [
            'user_id' => $res->json('user.id'),
            'is_verified' => true,
            'verification_method' => 'domain',
        ]);
    }

    public function test_personal_email_employer_is_rejected_at_registration(): void
    {
        // Company accounts must use a business email — free providers are blocked.
        $this->registerEmployer('jane.doe@gmail.com')
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'jane.doe@gmail.com']);
    }

    public function test_disposable_email_employer_is_rejected_at_registration(): void
    {
        $this->registerEmployer('throwaway@mailinator.com')
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_job_seeker_may_register_with_a_personal_email(): void
    {
        // The business-email rule applies to companies only, never to job seekers.
        $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@gmail.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'job_seeker',
        ])->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'jane.doe@gmail.com', 'role' => 'job_seeker']);
    }

    public function test_unverified_employer_cannot_post_jobs(): void
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->unverified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/employer/jobs', $this->jobPayload())
            ->assertStatus(403)
            ->assertJsonPath('verification_required', true);

        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_verified_employer_can_post_jobs(): void
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->verified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson('/api/employer/jobs', $this->jobPayload())->assertCreated();
    }

    public function test_payment_verification_via_mock_gateway_unlocks_posting(): void
    {
        config(['billing.driver' => 'mock']);
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->unverified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        // Blocked before verifying.
        $this->postJson('/api/employer/jobs', $this->jobPayload())->assertStatus(403);

        // Verify by payment (mock → instant).
        $this->postJson('/api/employer/verification/payment')
            ->assertOk()
            ->assertJsonPath('is_verified', true);

        $this->assertDatabaseHas('employer_profiles', [
            'user_id' => $user->id,
            'is_verified' => true,
            'verification_method' => 'payment',
        ]);

        // Now posting works.
        $this->postJson('/api/employer/jobs', $this->jobPayload())->assertCreated();
    }

    public function test_verification_status_endpoint_reports_state(): void
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->unverified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/employer/verification')
            ->assertOk()
            ->assertJsonPath('is_verified', false)
            ->assertJsonPath('methods_available.payment', true)
            ->assertJsonPath('methods_available.linkedin', false); // not configured in tests
    }

    public function test_linkedin_redirect_is_unavailable_when_not_configured(): void
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->unverified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/employer/verification/linkedin/redirect')->assertStatus(503);
    }

    public function test_linkedin_redirect_returns_auth_url_when_configured(): void
    {
        config([
            'verification.linkedin.client_id' => 'test-client',
            'verification.linkedin.client_secret' => 'test-secret',
            'verification.linkedin.redirect' => 'http://localhost/api/auth/linkedin/callback',
        ]);
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->unverified()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $url = $this->getJson('/api/employer/verification/linkedin/redirect')
            ->assertOk()->json('url');

        $this->assertStringContainsString('linkedin.com/oauth/v2/authorization', $url);
        $this->assertStringContainsString('client_id=test-client', $url);
    }

    public function test_stripe_verification_webhook_marks_employer_verified(): void
    {
        $employer = EmployerProfile::factory()->unverified()->create();

        $this->postJson('/api/billing/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['client_reference_id' => 'verify_'.$employer->id]],
        ])->assertOk()->assertJsonPath('handled', true);

        $this->assertTrue($employer->fresh()->is_verified);
        $this->assertEquals('payment', $employer->fresh()->verification_method);
    }

    public function test_subscription_webhook_still_works_alongside_verification(): void
    {
        $plan = SubscriptionPlan::factory()->create();
        $employer = EmployerProfile::factory()->create();
        $subscription = EmployerSubscription::factory()->create([
            'employer_profile_id' => $employer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
        ]);

        $this->postJson('/api/billing/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['client_reference_id' => (string) $subscription->id]],
        ])->assertOk()->assertJsonPath('handled', true);

        $this->assertEquals('active', $subscription->fresh()->status);
    }
}
