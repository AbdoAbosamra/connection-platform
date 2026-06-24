<?php

namespace Database\Factories;

use App\Models\ModerationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModerationLog>
 */
class ModerationLogFactory extends Factory
{
    protected $model = ModerationLog::class;

    public function definition(): array
    {
        return [
            'moderator_id' => User::factory()->admin(),
            'user_id' => User::factory(),
            'report_id' => null,
            'action' => fake()->randomElement(ModerationLog::ACTIONS),
            'notes' => fake()->optional()->sentence(),
            'metadata' => null,
        ];
    }
}
