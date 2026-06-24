<?php

namespace App\Services\Billing;

use App\Models\EmployerProfile;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Stripe Checkout integration implemented directly over the REST API (via the
 * Laravel HTTP client) so the platform needs no extra Composer dependency.
 *
 * Flow:
 *   1. subscribe() creates a pending EmployerSubscription (status = past_due)
 *      and a Stripe Checkout Session, returning its hosted URL.
 *   2. The user pays on Stripe's hosted page.
 *   3. Stripe calls the webhook → BillingService::handleWebhook() → activate().
 */
class StripeGateway extends AbstractGateway
{
    private const API_BASE = 'https://api.stripe.com/v1';

    public function __construct(private string $secret) {}

    public function subscribe(EmployerProfile $employer, SubscriptionPlan $plan, string $period): array
    {
        $amount = $period === 'annual' ? $plan->price_annual : $plan->price_monthly;

        // Create the local record in a not-yet-active state.
        $subscription = $this->activate($employer, $plan, $period, ['status' => 'past_due']);

        $response = Http::withToken($this->secret)
            ->asForm()
            ->post(self::API_BASE.'/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => config('billing.success_url'),
                'cancel_url' => config('billing.cancel_url'),
                'client_reference_id' => $subscription->id,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][unit_amount]' => $amount,
                'line_items[0][price_data][product_data][name]' => $plan->name.' ('.$period.')',
                'metadata[employer_profile_id]' => $employer->id,
                'metadata[subscription_plan_id]' => $plan->id,
                'metadata[billing_period]' => $period,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Stripe checkout session creation failed: '.$response->body());
        }

        return [
            'subscription' => $subscription,
            'checkout_url' => $response->json('url'),
        ];
    }
}
