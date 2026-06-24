<?php

namespace Database\Factories;

use App\Models\InterviewSchedule;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InterviewSchedule>
 */
class InterviewScheduleFactory extends Factory
{
    protected $model = InterviewSchedule::class;

    public function definition(): array
    {
        return [
            'job_application_id' => JobApplication::factory(),
            'scheduled_by' => User::factory()->employer(),
            'scheduled_at' => now()->addDays(3),
            'duration_minutes' => 60,
            'format' => 'video',
            'meeting_link' => fake()->url(),
            'location' => null,
            'notes' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
