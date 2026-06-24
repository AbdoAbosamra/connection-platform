<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'employer_profile_id' => EmployerProfile::factory(),
            'job_seeker_profile_id' => JobSeekerProfile::factory(),
            'job_id' => null,
            'last_message_at' => null,
        ];
    }
}
