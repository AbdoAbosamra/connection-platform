<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeekerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobApplication>
 */
class JobApplicationFactory extends Factory
{
    protected $model = JobApplication::class;

    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'job_seeker_profile_id' => JobSeekerProfile::factory(),
            'cover_letter' => fake()->paragraph(),
            'resume_snapshot' => 'resumes/'.fake()->uuid().'.pdf',
            'expected_salary' => fake()->numberBetween(50000, 150000),
            'currency' => 'USD',
            'status' => 'submitted',
            'employer_notes' => null,
            'viewed_at' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function withdrawn(): static
    {
        return $this->state(fn () => ['status' => 'withdrawn']);
    }

    public function hired(): static
    {
        return $this->state(fn () => ['status' => 'hired']);
    }
}
