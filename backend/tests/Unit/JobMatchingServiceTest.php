<?php

namespace Tests\Unit;

use App\Models\Job;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Services\JobMatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobs_sharing_required_skills_are_recommended(): void
    {
        $php = Skill::factory()->create(['name' => 'PHP']);
        $laravel = Skill::factory()->create(['name' => 'Laravel']);
        $cobol = Skill::factory()->create(['name' => 'COBOL']);

        $seeker = JobSeekerProfile::factory()->create(['experience_level' => 'senior']);
        $seeker->skills()->attach([$php->id, $laravel->id]);

        $match = Job::factory()->active()->create(['experience_level' => 'senior']);
        $match->skills()->attach($php->id, ['is_required' => true]);

        $noMatch = Job::factory()->active()->create();
        $noMatch->skills()->attach($cobol->id, ['is_required' => true]);

        $recommended = app(JobMatchingService::class)->recommendJobsFor($seeker->fresh());

        $ids = $recommended->pluck('id');
        $this->assertTrue($ids->contains($match->id));
        $this->assertFalse($ids->contains($noMatch->id));
    }

    public function test_recommendations_are_limited(): void
    {
        $php = Skill::factory()->create(['name' => 'PHP']);
        $seeker = JobSeekerProfile::factory()->create();
        $seeker->skills()->attach($php->id);

        foreach (range(1, 15) as $_) {
            Job::factory()->active()->create()->skills()->attach($php->id, ['is_required' => true]);
        }

        $recommended = app(JobMatchingService::class)->recommendJobsFor($seeker->fresh(), 10);
        $this->assertLessThanOrEqual(10, $recommended->count());
    }

    public function test_seeker_without_skills_still_gets_a_fallback_list(): void
    {
        $seeker = JobSeekerProfile::factory()->create();
        Job::factory()->active()->count(3)->create();

        $recommended = app(JobMatchingService::class)->recommendJobsFor($seeker->fresh());
        // Fallback returns recent active jobs (scored by level/salary), never an error.
        $this->assertIsIterable($recommended);
    }
}
