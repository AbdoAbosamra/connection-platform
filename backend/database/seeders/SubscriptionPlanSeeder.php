<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Free / Starter / Growth / Scale pricing.
 *
 * Idempotent (updateOrCreate by slug) so it can be run safely on production to
 * refresh the live plans without touching demo data:
 *   php artisan db:seed --class=Database\\Seeders\\SubscriptionPlanSeeder --force
 *
 * Prices are in cents. Annual = 10× monthly (two months free, ~17% off).
 * job_posts_limit: 0 = unlimited.
 *
 * NOTE: this is the *display* layer only. Plan feature flags describe what each
 * tier includes; enforcing them (gating search/analytics/international) and real
 * Stripe billing are a separate, later step.
 */
class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Founding employer — try the platform.',
                'price_monthly' => 0,
                'price_annual' => 0,
                'job_posts_limit' => 1,
                'candidate_search' => false,
                'advanced_search' => false,
                'analytics' => false,
                'featured_listings' => false,
                'international_remote' => false,
                'verification_discount' => false,
                'priority_support' => false,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small businesses hiring occasionally.',
                'price_monthly' => 2900,
                'price_annual' => 29000,
                'job_posts_limit' => 3,
                'candidate_search' => true,
                'advanced_search' => false,
                'analytics' => false,
                'featured_listings' => false,
                'international_remote' => false,
                'verification_discount' => false,
                'priority_support' => false,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For businesses hiring regularly.',
                'price_monthly' => 7900,
                'price_annual' => 79000,
                'job_posts_limit' => 10,
                'candidate_search' => true,
                'advanced_search' => true,
                'analytics' => true,
                'featured_listings' => true,
                'international_remote' => true,
                'verification_discount' => true,
                'priority_support' => true,
            ],
            [
                'name' => 'Scale',
                'slug' => 'scale',
                'description' => 'For recruiting teams hiring at scale.',
                'price_monthly' => 19900,
                'price_annual' => 199000,
                'job_posts_limit' => 0,
                'candidate_search' => true,
                'advanced_search' => true,
                'analytics' => true,
                'featured_listings' => true,
                'international_remote' => true,
                'verification_discount' => true,
                'priority_support' => true,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true]),
            );
        }

        // Retire the legacy plans so they drop off the pricing page. They are kept
        // (not deleted) so any historical subscription rows keep their FK.
        SubscriptionPlan::whereIn('slug', ['basic', 'pro', 'enterprise'])
            ->update(['is_active' => false]);
    }
}
