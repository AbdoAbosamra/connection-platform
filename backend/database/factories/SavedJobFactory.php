<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\SavedJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedJob>
 */
class SavedJobFactory extends Factory
{
    protected $model = SavedJob::class;

    public function definition(): array
    {
        return [
            'job_id' => Job::factory(),
            'job_seeker_profile_id' => JobSeekerProfile::factory(),
        ];
    }
}
