<?php

namespace App\Services\Billing;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;

/**
 * Entry point for all billing operations. Resolves the configured gateway and
 * delegates to it, so controllers never depend on a concrete payment provider.
 */
class BillingService extends AbstractGateway
{
    private PaymentGateway $gateway;

    public function __construct()
    {
        $this->gateway = $this->resolveGateway();
    }

    /**
     * Choose the gateway from config. Falls back to the mock gateway whenever
     * Stripe is requested without a secret key, so the app is always runnable.
     */
    private function resolveGateway(): PaymentGateway
    {
        $driver = config('billing.driver', 'mock');
        $secret = config('billing.stripe.secret');

        if ($driver === 'stripe' && !empty($secret)) {
            return new StripeGateway($secret);
        }

        return new MockGateway;
    }

    public function subscribe(EmployerProfile $employer, SubscriptionPlan $plan, string $period): array
    {
        return $this->gateway->subscribe($employer, $plan, $period);
    }

    public function cancel(EmployerSubscription $subscription): EmployerSubscription
    {
        return $this->gateway->cancel($subscription);
    }

    /**
     * Handle a Stripe webhook event. Activates the matching subscription when the
     * checkout completes. Returns true if the event was handled.
     *
     * @param  array<string, mixed>  $payload  Decoded Stripe event body.
     */
    public function handleWebhook(array $payload): bool
    {
        $type = $payload['type'] ?? null;

        if ($type !== 'checkout.session.completed') {
            return false;
        }

        $session = $payload['data']['object'] ?? [];
        $reference = $session['client_reference_id'] ?? null;

        // Employer-verification checkout uses a "verify_{employerId}" reference.
        if (is_string($reference) && str_starts_with($reference, 'verify_')) {
            $employer = EmployerProfile::find((int) substr($reference, 7));
            $employer?->markVerified('payment');

            return (bool) $employer;
        }

        $subscription = $reference ? EmployerSubscription::find($reference) : null;
        if (!$subscription) {
            return false;
        }

        // Reuse the shared activation logic to lift the employer to the paid tier.
        $this->activate(
            $subscription->employerProfile,
            $subscription->plan,
            $subscription->billing_period,
            [
                'status' => 'active',
                'stripe_subscription_id' => $session['subscription'] ?? null,
                'stripe_customer_id' => $session['customer'] ?? null,
            ]
        );

        return true;
    }
}
