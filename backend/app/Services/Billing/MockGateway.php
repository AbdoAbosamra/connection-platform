<?php

namespace App\Services\Billing;

use App\Models\EmployerProfile;
use App\Models\SubscriptionPlan;

/**
 * Zero-dependency gateway that activates the subscription immediately with no
 * payment step. Used for local development, automated tests, and demos.
 */
class MockGateway extends AbstractGateway
{
    public function subscribe(EmployerProfile $employer, SubscriptionPlan $plan, string $period): array
    {
        $subscription = $this->activate($employer, $plan, $period, ['status' => 'active']);

        return ['subscription' => $subscription, 'checkout_url' => null];
    }
}
