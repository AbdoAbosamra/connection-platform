<?php

namespace Tests\Feature;

use App\Models\EmployerProfile;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PricingPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_publishes_the_four_tiers_with_correct_pricing(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);

        $this->getJson('/api/subscription-plans')
            ->assertOk()
            ->assertJsonCount(4, 'plans')
            // ordered by price_monthly asc → Free, Starter, Growth, Scale
            ->assertJsonPath('plans.0.slug', 'free')
            ->assertJsonPath('plans.0.price_monthly', 0)
            ->assertJsonPath('plans.1.slug', 'starter')
            ->assertJsonPath('plans.1.price_monthly', 2900)
            ->assertJsonPath('plans.2.slug', 'growth')
            ->assertJsonPath('plans.2.price_monthly', 7900)
            ->assertJsonPath('plans.3.slug', 'scale')
            ->assertJsonPath('plans.3.price_monthly', 19900);
    }

    public function test_growth_includes_international_but_starter_does_not(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);

        $growth = SubscriptionPlan::where('slug', 'growth')->first();
        $starter = SubscriptionPlan::where('slug', 'starter')->first();

        $this->assertTrue($growth->international_remote);
        $this->assertTrue($growth->advanced_search);
        $this->assertFalse($starter->international_remote);
        $this->assertFalse($starter->advanced_search);
        $this->assertTrue($starter->candidate_search);
    }

    public function test_legacy_plans_are_retired(): void
    {
        SubscriptionPlan::factory()->create(['slug' => 'pro', 'is_active' => true]);

        $this->seed(SubscriptionPlanSeeder::class);

        $this->assertDatabaseHas('subscription_plans', ['slug' => 'pro', 'is_active' => false]);
    }

    public function test_growth_plan_lifts_employer_to_pro_tier(): void
    {
        config(['billing.driver' => 'mock']);
        $this->seed(SubscriptionPlanSeeder::class);

        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $user->id, 'subscription_tier' => 'free']);
        $growth = SubscriptionPlan::where('slug', 'growth')->first();

        Sanctum::actingAs($user);
        $this->postJson('/api/employer/subscription', [
            'plan_id' => $growth->id,
            'billing_period' => 'monthly',
        ])->assertCreated();

        $this->assertEquals('pro', $user->employerProfile->fresh()->subscription_tier);
    }

    public function test_scale_plan_lifts_employer_to_enterprise_tier(): void
    {
        config(['billing.driver' => 'mock']);
        $this->seed(SubscriptionPlanSeeder::class);

        $user = User::factory()->employer()->create();
        EmployerProfile::factory()->create(['user_id' => $user->id, 'subscription_tier' => 'free']);
        $scale = SubscriptionPlan::where('slug', 'scale')->first();

        Sanctum::actingAs($user);
        $this->postJson('/api/employer/subscription', [
            'plan_id' => $scale->id,
            'billing_period' => 'monthly',
        ])->assertCreated();

        $this->assertEquals('enterprise', $user->employerProfile->fresh()->subscription_tier);
    }
}
