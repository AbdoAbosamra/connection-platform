<?php

namespace Tests\Unit;

use App\Models\EmployerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_tier_has_credits_only_when_positive(): void
    {
        $profile = EmployerProfile::factory()->make(['subscription_tier' => 'free', 'job_post_credits' => 1]);
        $this->assertTrue($profile->hasCredits());

        $profile->job_post_credits = 0;
        $this->assertFalse($profile->hasCredits());
    }

    public function test_paid_tier_always_has_credits(): void
    {
        $profile = EmployerProfile::factory()->make(['subscription_tier' => 'pro', 'job_post_credits' => 0]);
        $this->assertTrue($profile->hasCredits());
    }

    public function test_decrement_credits_reduces_free_tier_count(): void
    {
        $profile = EmployerProfile::factory()->create(['subscription_tier' => 'free', 'job_post_credits' => 2]);
        $profile->decrementCredits();
        $this->assertEquals(1, $profile->fresh()->job_post_credits);
    }

    public function test_decrement_credits_throws_when_exhausted(): void
    {
        $profile = EmployerProfile::factory()->create(['subscription_tier' => 'free', 'job_post_credits' => 0]);
        $this->expectException(\RuntimeException::class);
        $profile->decrementCredits();
    }

    public function test_decrement_credits_is_noop_for_paid_tier(): void
    {
        $profile = EmployerProfile::factory()->create(['subscription_tier' => 'pro', 'job_post_credits' => 5]);
        $profile->decrementCredits();
        $this->assertEquals(5, $profile->fresh()->job_post_credits);
    }

    public function test_company_slug_is_generated_uniquely(): void
    {
        $a = EmployerProfile::factory()->create(['company_name' => 'Same Co', 'company_slug' => null]);
        $b = EmployerProfile::factory()->create(['company_name' => 'Same Co', 'company_slug' => null]);

        $this->assertNotEquals($a->company_slug, $b->company_slug);
        $this->assertStringStartsWith('same-co', $a->company_slug);
    }
}
