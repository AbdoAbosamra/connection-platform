<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reportable_type' => Job::class,
            'reportable_id' => Job::factory(),
            'reason' => fake()->randomElement(['spam', 'scam', 'fake_listing', 'phishing', 'harassment', 'inappropriate', 'other']),
            'details' => fake()->sentence(),
            'status' => 'open',
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => 'resolved',
            'resolved_by' => User::factory()->admin(),
            'resolution_note' => fake()->sentence(),
            'resolved_at' => now(),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'reportable_type' => User::class,
            'reportable_id' => $user->id,
        ]);
    }
}
