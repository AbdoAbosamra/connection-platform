<?php

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function employer(array $profile = []): User
    {
        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->create(array_merge(['user_id' => $user->id], $profile));

        return $user;
    }

    public function test_public_can_list_active_plans_only(): void
    {
        SubscriptionPlan::factory()->count(2)->create(['is_active' => true]);
        SubscriptionPlan::factory()->inactive()->create();

        $this->getJson('/api/subscription-plans')->assertOk()->assertJsonCount(2, 'plans');
    }

    public function test_employer_can_subscribe_via_mock_gateway_and_tier_is_lifted(): void
    {
        config(['billing.driver' => 'mock']);
        $user = $this->employer(['subscription_tier' => 'free', 'job_post_credits' => 0]);
        $plan = SubscriptionPlan::factory()->create(['price_monthly' => 14900, 'job_posts_limit' => 25]);

        Sanctum::actingAs($user);
        $this->postJson('/api/employer/subscription', [
            'plan_id' => $plan->id,
            'billing_period' => 'monthly',
        ])->assertCreated()
            ->assertJsonPath('checkout_url', null)
            ->assertJsonPath('subscription.status', 'active');

        $profile = $user->employerProfile->fresh();
        $this->assertNotEquals('free', $profile->subscription_tier);
        $this->assertTrue($profile->hasCredits());
        $this->assertDatabaseHas('employer_subscriptions', [
            'employer_profile_id' => $profile->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_subscribing_lets_a_previously_credit_less_employer_post_jobs(): void
    {
        config(['billing.driver' => 'mock']);
        $user = $this->employer(['subscription_tier' => 'free', 'job_post_credits' => 0]);
        $plan = SubscriptionPlan::factory()->create(['job_posts_limit' => 0]);
        Sanctum::actingAs($user);

        // Before subscribing: no credits → cannot post.
        $payload = [
            'title' => 'Remote Engineer',
            'description' => str_repeat('A wonderful remote role for a talented engineer. ', 5),
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'experience_level' => 'mid',
            'status' => 'active',
        ];
        $this->postJson('/api/employer/jobs', $payload)->assertStatus(422);

        $this->postJson('/api/employer/subscription', ['plan_id' => $plan->id, 'billing_period' => 'monthly'])
            ->assertCreated();

        // After subscribing: unlimited posting.
        $this->postJson('/api/employer/jobs', $payload)->assertCreated();
    }

    public function test_employer_can_cancel_subscription_and_drops_to_free(): void
    {
        config(['billing.driver' => 'mock']);
        $user = $this->employer();
        $plan = SubscriptionPlan::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/employer/subscription', ['plan_id' => $plan->id, 'billing_period' => 'monthly'])
            ->assertCreated();

        $this->deleteJson('/api/employer/subscription')->assertOk();

        $this->assertEquals('free', $user->employerProfile->fresh()->subscription_tier);
        $this->assertDatabaseHas('employer_subscriptions', [
            'employer_profile_id' => $user->employerProfile->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancelling_without_subscription_is_rejected(): void
    {
        Sanctum::actingAs($this->employer());
        $this->deleteJson('/api/employer/subscription')->assertStatus(422);
    }

    public function test_show_returns_current_subscription_state(): void
    {
        $user = $this->employer(['subscription_tier' => 'free', 'job_post_credits' => 2]);
        Sanctum::actingAs($user);

        $this->getJson('/api/employer/subscription')
            ->assertOk()
            ->assertJsonPath('tier', 'free')
            ->assertJsonPath('job_post_credits', 2);
    }

    public function test_non_employer_cannot_subscribe(): void
    {
        Sanctum::actingAs(User::factory()->jobSeeker()->create());
        $this->postJson('/api/employer/subscription', ['plan_id' => 1, 'billing_period' => 'monthly'])
            ->assertForbidden();
    }

    public function test_stripe_webhook_activates_a_pending_subscription(): void
    {
        $employer = EmployerProfile::factory()->create(['subscription_tier' => 'free', 'job_post_credits' => 0]);
        $plan = SubscriptionPlan::factory()->create(['job_posts_limit' => 25]);
        $subscription = EmployerSubscription::factory()->create([
            'employer_profile_id' => $employer->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'past_due',
        ]);

        // No webhook secret configured in the test env → signature check is skipped.
        $this->postJson('/api/billing/webhook', [
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'client_reference_id' => (string) $subscription->id,
                'subscription' => 'sub_test_123',
                'customer' => 'cus_test_123',
            ]],
        ])->assertOk()->assertJsonPath('handled', true);

        $this->assertEquals('active', $subscription->fresh()->status);
        $this->assertNotEquals('free', $employer->fresh()->subscription_tier);
    }

    public function test_irrelevant_webhook_events_are_ignored(): void
    {
        $this->postJson('/api/billing/webhook', ['type' => 'invoice.created'])
            ->assertOk()
            ->assertJsonPath('handled', false);
    }
}
