<?php

namespace Database\Seeders;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\Skill;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Platform Admin',
            'email' => 'admin@connextion.io',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Seed skills
        $skills = [
            ['name' => 'PHP',          'category' => 'Programming'],
            ['name' => 'Laravel',      'category' => 'Programming'],
            ['name' => 'JavaScript',   'category' => 'Programming'],
            ['name' => 'Vue.js',       'category' => 'Frontend'],
            ['name' => 'React',        'category' => 'Frontend'],
            ['name' => 'Node.js',      'category' => 'Backend'],
            ['name' => 'Python',       'category' => 'Programming'],
            ['name' => 'MySQL',        'category' => 'Database'],
            ['name' => 'PostgreSQL',   'category' => 'Database'],
            ['name' => 'AWS',          'category' => 'Cloud'],
            ['name' => 'Docker',       'category' => 'DevOps'],
            ['name' => 'TypeScript',   'category' => 'Programming'],
            ['name' => 'Tailwind CSS', 'category' => 'Frontend'],
            ['name' => 'GraphQL',      'category' => 'API'],
            ['name' => 'Redis',        'category' => 'Database'],
            ['name' => 'Ruby on Rails', 'category' => 'Programming'],
            ['name' => 'Kotlin',       'category' => 'Mobile'],
            ['name' => 'Swift',        'category' => 'Mobile'],
        ];

        foreach ($skills as $skill) {
            Skill::create($skill);
        }

        // ── US company: TechCorp ─────────────────────────────────
        $employer = User::create([
            'name' => 'TechCorp Recruiter',
            'email' => 'employer@demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $profile = EmployerProfile::create([
            'user_id' => $employer->id,
            'company_name' => 'TechCorp Inc',
            'description' => 'We build great software products used by millions worldwide.',
            'industry' => 'Software',
            'company_size' => '51-200',
            'website' => 'https://techcorp.example.com',
            'headquarters_city' => 'San Francisco',
            'headquarters_state' => 'CA',
            'is_verified' => true,
            'subscription_tier' => 'pro',
        ]);

        $job = $profile->jobs()->create([
            'title' => 'Senior Laravel Developer',
            'description' => 'We are looking for an experienced Laravel developer to join our distributed team. You will build APIs and maintain our core platform.',
            'requirements' => "5+ years PHP experience\nLaravel proficiency\nMySQL / PostgreSQL\nREST API design",
            'benefits' => "Competitive salary\nFlexible hours\nEquipment budget\nAnnual retreat",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 90000,
            'salary_max' => 130000,
            'experience_level' => 'senior',
            'status' => 'active',
            'expires_at' => now()->addDays(60),
        ]);
        $job->skills()->attach([1 => ['is_required' => true], 2 => ['is_required' => true], 8 => ['is_required' => false]]);

        // ── Canadian company: Shopify ────────────────────────────
        $shopifyUser = User::create([
            'name' => 'Shopify Talent',
            'email' => 'careers@shopify-demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $shopify = EmployerProfile::create([
            'user_id' => $shopifyUser->id,
            'company_name' => 'Shopify',
            'description' => 'Shopify is the leading global commerce company, providing trusted tools to start, grow, market, and manage a retail business of any size.',
            'industry' => 'E-commerce',
            'company_size' => '10000+',
            'website' => 'https://shopify.com',
            'headquarters_city' => 'Ottawa',
            'headquarters_state' => 'ON',
            'is_verified' => true,
            'subscription_tier' => 'enterprise',
        ]);

        $shopifyJob = $shopify->jobs()->create([
            'title' => 'Senior Frontend Engineer (Vue / React)',
            'description' => 'Join Shopify\'s merchant experience team to build the tools millions of entrepreneurs rely on every day. You\'ll work on complex UI challenges at global scale.',
            'requirements' => "4+ years frontend experience\nVue.js or React expertise\nTypeScript proficiency\nPerformance optimization skills",
            'benefits' => "Competitive compensation\nFlexible work-from-anywhere policy\nHealth & wellness budget\nProfessional development fund",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 100000,
            'salary_max' => 160000,
            'experience_level' => 'senior',
            'status' => 'active',
            'expires_at' => now()->addDays(45),
        ]);
        $shopifyJob->skills()->attach([4 => ['is_required' => true], 5 => ['is_required' => false], 12 => ['is_required' => true], 13 => ['is_required' => false]]);

        // ── Canadian company: Hootsuite ──────────────────────────
        $hootsuiteUser = User::create([
            'name' => 'Hootsuite Recruiting',
            'email' => 'careers@hootsuite-demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $hootsuite = EmployerProfile::create([
            'user_id' => $hootsuiteUser->id,
            'company_name' => 'Hootsuite',
            'description' => 'Hootsuite is the most widely used social media management platform, trusted by over 200,000 organisations worldwide.',
            'industry' => 'Social Media',
            'company_size' => '1000-5000',
            'website' => 'https://hootsuite.com',
            'headquarters_city' => 'Vancouver',
            'headquarters_state' => 'BC',
            'is_verified' => true,
            'subscription_tier' => 'pro',
        ]);

        $hootsuiteJob = $hootsuite->jobs()->create([
            'title' => 'Backend Engineer – Data Platform',
            'description' => 'Help us build the data infrastructure that powers social media insights for hundreds of thousands of businesses. You\'ll design scalable pipelines and APIs.',
            'requirements' => "3+ years backend development\nPython or Node.js\nPostgreSQL and Redis\nExperience with data pipelines",
            'benefits' => "Remote-first culture\nFlexible hours\nHome office stipend\n4 weeks PTO",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 85000,
            'salary_max' => 125000,
            'experience_level' => 'mid',
            'status' => 'active',
            'expires_at' => now()->addDays(50),
        ]);
        $hootsuiteJob->skills()->attach([7 => ['is_required' => true], 6 => ['is_required' => false], 9 => ['is_required' => true], 15 => ['is_required' => false]]);

        // ── Canadian company: Wealthsimple ───────────────────────
        $wealthsimpleUser = User::create([
            'name' => 'Wealthsimple People',
            'email' => 'careers@wealthsimple-demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $wealthsimple = EmployerProfile::create([
            'user_id' => $wealthsimpleUser->id,
            'company_name' => 'Wealthsimple',
            'description' => 'Wealthsimple is a financial technology company on a mission to help everyone achieve financial freedom by making smart financial decisions accessible to all.',
            'industry' => 'Fintech',
            'company_size' => '500-1000',
            'website' => 'https://wealthsimple.com',
            'headquarters_city' => 'Toronto',
            'headquarters_state' => 'ON',
            'is_verified' => true,
            'subscription_tier' => 'pro',
        ]);

        $wealthsimpleJob = $wealthsimple->jobs()->create([
            'title' => 'Full-Stack Engineer – Investing Platform',
            'description' => 'Build the investment products used by millions of Canadians. You\'ll own features end-to-end, working across our React frontend and Ruby on Rails API.',
            'requirements' => "3+ years full-stack experience\nReact and TypeScript\nRuby on Rails or similar backend\nFinancial domain knowledge a plus",
            'benefits' => "Equity participation\nFlexible remote work\nWellness account\nGenerous parental leave",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 95000,
            'salary_max' => 145000,
            'experience_level' => 'mid',
            'status' => 'active',
            'expires_at' => now()->addDays(40),
        ]);
        $wealthsimpleJob->skills()->attach([5 => ['is_required' => true], 12 => ['is_required' => true], 16 => ['is_required' => false], 9 => ['is_required' => false]]);

        // ── Canadian company: Lightspeed Commerce ───────────────
        $lightspeedUser = User::create([
            'name' => 'Lightspeed Hiring',
            'email' => 'careers@lightspeed-demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $lightspeed = EmployerProfile::create([
            'user_id' => $lightspeedUser->id,
            'company_name' => 'Lightspeed Commerce',
            'description' => 'Lightspeed is the unified POS and payments platform for ambitious retailers, restaurateurs, and golf course operators.',
            'industry' => 'Commerce Technology',
            'company_size' => '1000-5000',
            'website' => 'https://lightspeedhq.com',
            'headquarters_city' => 'Montréal',
            'headquarters_state' => 'QC',
            'is_verified' => true,
            'subscription_tier' => 'pro',
        ]);

        $lightspeedJob = $lightspeed->jobs()->create([
            'title' => 'Cloud Infrastructure Engineer',
            'description' => 'Join our platform engineering team to design and operate the cloud infrastructure supporting millions of merchant transactions daily. You will lead reliability and scaling initiatives.',
            'requirements' => "4+ years DevOps / cloud engineering\nAWS or GCP expertise\nTerraform and Kubernetes\nStrong observability practices",
            'benefits' => "Stock options\nFlexible work arrangement\nLearning & development budget\nHealth benefits from day one",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'experience_level' => 'senior',
            'status' => 'active',
            'expires_at' => now()->addDays(55),
        ]);
        $lightspeedJob->skills()->attach([10 => ['is_required' => true], 11 => ['is_required' => true], 10 => ['is_required' => true]]);

        // ── Canadian company: 1Password ──────────────────────────
        $onepassUser = User::create([
            'name' => '1Password Recruiting',
            'email' => 'careers@1password-demo.com',
            'password' => Hash::make('password'),
            'role' => 'employer',
            'email_verified_at' => now(),
        ]);

        $onepass = EmployerProfile::create([
            'user_id' => $onepassUser->id,
            'company_name' => '1Password',
            'description' => '1Password is the world\'s most loved password manager, trusted by more than 100,000 businesses and millions of individuals to keep their online information safe.',
            'industry' => 'Cybersecurity',
            'company_size' => '500-1000',
            'website' => 'https://1password.com',
            'headquarters_city' => 'Toronto',
            'headquarters_state' => 'ON',
            'is_verified' => true,
            'subscription_tier' => 'enterprise',
        ]);

        $onepassJob = $onepass->jobs()->create([
            'title' => 'Senior Security Engineer',
            'description' => 'Help protect millions of users and their most sensitive data. You will work on cryptographic systems, threat modelling, and hardening our product and infrastructure against attacks.',
            'requirements' => "5+ years security engineering\nCryptography fundamentals\nPenetration testing experience\nRust or Go preferred",
            'benefits' => "Fully remote-first\nCompetitive equity\nUnlimited PTO\nTop-tier health benefits",
            'category' => 'Engineering',
            'employment_type' => 'full_time',
            'location_type' => 'remote',
            'salary_min' => 120000,
            'salary_max' => 180000,
            'experience_level' => 'senior',
            'status' => 'active',
            'expires_at' => now()->addDays(60),
        ]);
        $onepassJob->skills()->attach([10 => ['is_required' => true], 11 => ['is_required' => false]]);

        // ── Demo seeker ──────────────────────────────────────────
        $seeker = User::create([
            'name' => 'Maria Santos',
            'email' => 'seeker@demo.com',
            'password' => Hash::make('password'),
            'role' => 'job_seeker',
            'email_verified_at' => now(),
            'country' => 'Philippines',
        ]);

        $seekerProfile = JobSeekerProfile::create([
            'user_id' => $seeker->id,
            'headline' => 'Senior PHP / Laravel Developer',
            'bio' => 'Passionate developer with 7 years of experience building scalable web apps.',
            'current_city' => 'Manila',
            'current_country' => 'Philippines',
            'nationality' => 'Filipino',
            'experience_level' => 'senior',
            'years_of_experience' => 7,
            'desired_salary_min' => 80000,
            'desired_salary_max' => 120000,
            'availability' => 'two_weeks',
            'profile_complete' => true,
        ]);

        $seekerProfile->skills()->attach([
            1 => ['proficiency' => 'expert'],
            2 => ['proficiency' => 'expert'],
            3 => ['proficiency' => 'advanced'],
            8 => ['proficiency' => 'intermediate'],
        ]);

        $this->seedSubscriptionPlans();
    }

    /**
     * Seed the public pricing tiers. Prices are stored in cents.
     * job_posts_limit = 0 means unlimited.
     */
    private function seedSubscriptionPlans(): void
    {
        $plans = [
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'For small teams getting started with remote hiring.',
                'price_monthly' => 4900,
                'price_annual' => 49000,
                'job_posts_limit' => 5,
                'featured_listings' => false,
                'candidate_search' => true,
                'analytics' => false,
                'priority_support' => false,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing companies hiring at scale.',
                'price_monthly' => 14900,
                'price_annual' => 149000,
                'job_posts_limit' => 25,
                'featured_listings' => true,
                'candidate_search' => true,
                'analytics' => true,
                'priority_support' => false,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited hiring power with premium support.',
                'price_monthly' => 39900,
                'price_annual' => 399000,
                'job_posts_limit' => 0,
                'featured_listings' => true,
                'candidate_search' => true,
                'analytics' => true,
                'priority_support' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
