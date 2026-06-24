<?php

namespace App\Services\Billing;

use App\Models\EmployerProfile;
use App\Models\EmployerSubscription;
use App\Models\SubscriptionPlan;

interface PaymentGateway
{
    /**
     * Begin a subscription for an employer on a given plan.
     *
     * @return array{subscription: EmployerSubscription, checkout_url: ?string}
     *                                                                          checkout_url is null for gateways that activate immediately (mock);
     *                                                                          for hosted-checkout gateways (Stripe) it is the URL to redirect to.
     */
    public function subscribe(EmployerProfile $employer, SubscriptionPlan $plan, string $period): array;

    /**
     * Cancel an active subscription (effective immediately in this implementation).
     */
    public function cancel(EmployerSubscription $subscription): EmployerSubscription;
}
