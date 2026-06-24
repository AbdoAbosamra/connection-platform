<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Basic', 'Pro', 'Enterprise', 'Starter', 'Growth']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->sentence(),
            'price_monthly' => fake()->numberBetween(1900, 29900),
            'price_annual' => fake()->numberBetween(19000, 299000),
            'job_posts_limit' => fake()->randomElement([5, 10, 25, 0]),
            'featured_listings' => true,
            'candidate_search' => true,
            'analytics' => true,
            'priority_support' => false,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
