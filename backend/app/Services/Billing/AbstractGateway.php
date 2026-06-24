<?php

namespace App\Services\Billing;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

abstract class AbstractGateway implements PaymentGateway
{
    /**
     * Map a plan to one of the employer_profiles.subscription_tier enum values.
     * Paid tiers make hasCredits() return true (unlimited posting); job_post_credits
     * is still recorded for reporting / future metered billing.
     */
    protected function tierForPlan(SubscriptionPlan $plan): string
    {
        return match (true) {
            $plan->job_posts_limit === 0 => 'enterprise',
            $plan->price_monthly >= 10000 => 'pro',
            default => 'basic',
        };
    }

    /**
     * Activate (or renew) a subscription and lift the employer to the paid tier.
     * Idempotent per (employer, plan): an existing active row is updated rather
     * than duplicated. Runs in a transaction so the subscription row and the
     * employer tier change commit together.
     *
     * @param  array{stripe_subscription_id?: string, stripe_customer_id?: string, status?: string}  $meta
     */
    protected function activate(
        EmployerProfile $employer,
        SubscriptionPlan $plan,
        string $period,
        array $meta = []
    ): EmployerSubscription {
        return DB::transaction(function () use ($employer, $plan, $period, $meta) {
            $periodEnd = $period === 'annual' ? now()->addYear() : now()->addMonth();

            $subscription = EmployerSubscription::updateOrCreate(
                [
                    'employer_profile_id' => $employer->id,
                    'subscription_plan_id' => $plan->id,
                ],
                [
                    'billing_period' => $period,
                    'status' => $meta['status'] ?? 'active',
                    'stripe_subscription_id' => $meta['stripe_subscription_id'] ?? null,
                    'stripe_customer_id' => $meta['stripe_customer_id'] ?? null,
                    'current_period_start' => now(),
                    'current_period_end' => $periodEnd,
                    'cancelled_at' => null,
                ]
            );

            // Only lift the tier once payment is confirmed (status active/trialing).
            if (in_array($subscription->status, ['active', 'trialing'])) {
                $employer->update([
                    'subscription_tier' => $this->tierForPlan($plan),
                    // 0 = unlimited; store the configured limit for the period.
                    'job_post_credits' => $plan->job_posts_limit,
                ]);
            }

            return $subscription;
        });
    }

    public function cancel(EmployerSubscription $subscription): EmployerSubscription
    {
        return DB::transaction(function () use ($subscription) {
            $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            // Drop the employer back to the free tier with no remaining credits.
            $subscription->employerProfile?->update([
                'subscription_tier' => 'free',
                'job_post_credits' => 0,
            ]);

            return $subscription->fresh();
        });
    }
}
