<?php

namespace Database\Factories;

use App\Models\EmployerProfile;
use App\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        $title = fake()->jobTitle();
        $min = fake()->numberBetween(50000, 100000);

        return [
            'employer_profile_id' => EmployerProfile::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraph(),
            'benefits' => fake()->paragraph(),
            'category' => fake()->randomElement(['Engineering', 'Design', 'Marketing', 'Sales', 'Support']),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contract', 'freelance', 'internship']),
            'location_type' => 'remote',
            'location_country' => 'US',
            'salary_min' => $min,
            'salary_max' => $min + fake()->numberBetween(10000, 60000),
            'currency' => 'USD',
            'salary_period' => 'annual',
            'salary_visible' => true,
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead', 'executive']),
            'status' => 'active',
            'is_featured' => false,
            'expires_at' => now()->addMonth(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active', 'expires_at' => now()->addMonth()]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => 'active', 'expires_at' => now()->subDay()]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
