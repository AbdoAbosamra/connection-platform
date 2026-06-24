<?php

namespace Database\Factories;

use App\Models\JobSeekerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobSeekerProfile>
 */
class JobSeekerProfileFactory extends Factory
{
    protected $model = JobSeekerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->jobSeeker(),
            'headline' => fake()->jobTitle(),
            'bio' => fake()->paragraph(),
            'portfolio_url' => fake()->optional()->url(),
            'linkedin_url' => fake()->optional()->url(),
            'github_url' => fake()->optional()->url(),
            'current_city' => fake()->city(),
            'current_country' => fake()->countryCode(),
            'nationality' => fake()->countryCode(),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead', 'executive']),
            'years_of_experience' => fake()->numberBetween(0, 20),
            'current_job_title' => fake()->jobTitle(),
            'desired_job_title' => fake()->jobTitle(),
            'desired_salary_min' => fake()->numberBetween(40000, 90000),
            'desired_salary_max' => fake()->numberBetween(90000, 200000),
            'currency' => 'USD',
            'availability' => fake()->randomElement(['immediately', 'two_weeks', 'one_month', 'negotiable']),
            'profile_complete' => true,
            'is_featured' => false,
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn () => [
            'profile_complete' => false,
            'headline' => null,
            'bio' => null,
            'resume' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true, 'profile_complete' => true]);
    }
}
