<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_returns_only_active_non_expired_jobs(): void
    {
        Job::factory()->active()->count(2)->create();
        Job::factory()->draft()->create();
        Job::factory()->closed()->create();
        Job::factory()->expired()->create();

        $this->getJson('/api/jobs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_listing_can_be_filtered_by_category_and_experience(): void
    {
        Job::factory()->active()->create(['category' => 'Engineering', 'experience_level' => 'senior']);
        Job::factory()->active()->create(['category' => 'Design', 'experience_level' => 'entry']);

        $this->getJson('/api/jobs?category=Engineering')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/jobs?experience_level=entry')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_listing_search_matches_title(): void
    {
        Job::factory()->active()->create(['title' => 'Rust Systems Engineer']);
        Job::factory()->active()->create(['title' => 'Marketing Lead']);

        $this->getJson('/api/jobs?q=Rust')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_listing_can_filter_by_skills(): void
    {
        $skill = Skill::factory()->create();
        $jobWith = Job::factory()->active()->create();
        $jobWith->skills()->attach($skill->id, ['is_required' => true]);
        Job::factory()->active()->create();

        $this->getJson("/api/jobs?skills={$skill->id}")->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_featured_jobs_are_listed_first(): void
    {
        Job::factory()->active()->create(['title' => 'Regular', 'is_featured' => false]);
        Job::factory()->active()->create(['title' => 'Promoted', 'is_featured' => true]);

        $this->getJson('/api/jobs')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Promoted');
    }

    public function test_show_returns_job_by_slug_and_increments_views(): void
    {
        $job = Job::factory()->active()->create(['views_count' => 0]);

        $this->getJson("/api/jobs/{$job->slug}")
            ->assertOk()
            ->assertJsonPath('job.id', $job->id);

        $this->assertEquals(1, $job->fresh()->views_count);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/jobs/does-not-exist')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_skills_endpoint_escapes_like_wildcards(): void
    {
        Skill::factory()->create(['name' => 'PHP']);

        // A raw % must not act as a wildcard that returns everything.
        $this->getJson('/api/skills?search=%25')->assertOk()->assertJsonCount(0, 'skills');
    }

    public function test_listing_returns_facet_counts(): void
    {
        Job::factory()->active()->count(2)->create(['category' => 'Engineering', 'employment_type' => 'full_time']);
        Job::factory()->active()->create(['category' => 'Design', 'employment_type' => 'contract']);

        $res = $this->getJson('/api/jobs')->assertOk();

        $this->assertEquals(2, $res->json('facets.category.Engineering'));
        $this->assertEquals(1, $res->json('facets.category.Design'));
        $this->assertEquals(1, $res->json('facets.employment_type.contract'));
    }

    public function test_facets_reflect_the_search_term(): void
    {
        Job::factory()->active()->create(['title' => 'Rust Systems Engineer', 'category' => 'Engineering']);
        Job::factory()->active()->create(['title' => 'Brand Designer', 'category' => 'Design']);

        $res = $this->getJson('/api/jobs?q=Rust')->assertOk();

        // Only the matching job's category should appear in the facets.
        $this->assertEquals(1, $res->json('facets.category.Engineering'));
        $this->assertArrayNotHasKey('Design', $res->json('facets.category'));
        $this->assertCount(1, $res->json('data'));
    }

    public function test_public_listing_is_rate_limited(): void
    {
        // Anonymous public reads are capped at 90/min/IP to deter scraping.
        foreach (range(1, 90) as $_) {
            $this->getJson('/api/jobs')->assertOk();
        }

        $this->getJson('/api/jobs')->assertStatus(429);
    }
}
