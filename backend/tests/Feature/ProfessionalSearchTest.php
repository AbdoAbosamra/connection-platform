<?php

namespace Tests\Feature;

use App\Models\JobSeekerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessionalSearchTest extends TestCase
{
    use RefreshDatabase;

    private function profile(array $attrs = []): JobSeekerProfile
    {
        return JobSeekerProfile::factory()->create(array_merge(['profile_complete' => true], $attrs));
    }

    public function test_basic_industry_filter_narrows_results(): void
    {
        $this->profile(['industry' => 'Fintech']);
        $this->profile(['industry' => 'Healthcare']);

        $this->getJson('/api/professionals?industry=Fintech')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.industry', 'Fintech');
    }

    public function test_remote_experience_minimum_filter(): void
    {
        $this->profile(['remote_experience_years' => 5]);
        $this->profile(['remote_experience_years' => 1]);

        $this->getJson('/api/professionals?remote_experience_min=3')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_advanced_country_and_language_filters(): void
    {
        $this->profile(['current_country' => 'Egypt', 'languages' => ['English', 'Arabic']]);
        $this->profile(['current_country' => 'Egypt', 'languages' => ['Arabic']]);
        $this->profile(['current_country' => 'Brazil', 'languages' => ['English']]);

        // Egypt + speaks English → only the first profile.
        $this->getJson('/api/professionals?country=Egypt&languages[]=English')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.current_country', 'Egypt');
    }

    public function test_contract_type_matches_preference_or_either(): void
    {
        $this->profile(['contract_preference' => 'contractor']);
        $this->profile(['contract_preference' => 'either']);
        $this->profile(['contract_preference' => 'employee']);

        // Employer wants a contractor → contractor + either, but not employee.
        $this->getJson('/api/professionals?contract_type=contractor')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_boolean_flag_filters(): void
    {
        $this->profile(['has_security_clearance' => true, 'certifications' => 'AWS SA', 'portfolio_url' => 'https://x.io']);
        $this->profile(['has_security_clearance' => false, 'certifications' => null, 'portfolio_url' => null]);

        $this->getJson('/api/professionals?has_security_clearance=1&has_certifications=1&has_portfolio=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
