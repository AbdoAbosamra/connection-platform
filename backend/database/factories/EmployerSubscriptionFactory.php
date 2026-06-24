<?php

namespace Database\Factories;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployerSubscription>
 */
class EmployerSubscriptionFactory extends Factory
{
    protected $model = EmployerSubscription::class;

    public function definition(): array
    {
        return [
            'employer_profile_id' => EmployerProfile::factory(),
            'subscription_plan_id' => SubscriptionPlan::factory(),
            'billing_period' => 'monthly',
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    public function trialing(): static
    {
        return $this->state(fn () => ['status' => 'trialing', 'trial_ends_at' => now()->addDays(14)]);
    }
}
