<?php

namespace Database\Factories;

use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EmployerProfile>
 */
class EmployerProfileFactory extends Factory
{
    protected $model = EmployerProfile::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'user_id' => User::factory()->employer(),
            'company_name' => $name,
            'company_slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->paragraph(),
            'industry' => fake()->randomElement(['Software', 'Finance', 'Healthcare', 'Education', 'Retail']),
            'company_size' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'website' => fake()->url(),
            'headquarters_city' => fake()->city(),
            'headquarters_state' => fake()->stateAbbr(),
            'headquarters_country' => 'US',
            'founded_year' => fake()->numberBetween(1990, 2024),
            // Factory employers default to verified (an established company) so
            // posting tests work out of the box; use ->unverified() to exercise
            // the verification gate.
            'is_verified' => true,
            'verified_at' => now(),
            'verification_method' => 'domain',
            'is_featured' => false,
            'subscription_tier' => 'free',
            'job_post_credits' => 3,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'is_verified' => true,
            'verified_at' => now(),
            'verification_method' => 'domain',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'is_verified' => false,
            'verified_at' => null,
            'verification_method' => null,
        ]);
    }

    public function noCredits(): static
    {
        return $this->state(fn () => ['subscription_tier' => 'free', 'job_post_credits' => 0]);
    }

    public function pro(): static
    {
        return $this->state(fn () => ['subscription_tier' => 'pro', 'job_post_credits' => 100]);
    }
}
