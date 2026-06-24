<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function __construct(private BillingService $billing) {}

    /**
     * GET /employer/subscription — the employer's current subscription (or null).
     */
    public function show(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        return response()->json([
            'subscription' => $employer?->subscription()->with('plan')->first(),
            'tier' => $employer?->subscription_tier,
            'job_post_credits' => $employer?->job_post_credits,
        ]);
    }

    /**
     * POST /employer/subscription — subscribe to a plan.
     * With the mock gateway the subscription activates immediately; with Stripe a
     * checkout_url is returned for the client to redirect to.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'billing_period' => ['required', 'in:monthly,annual'],
        ]);

        $employer = $request->user()->employerProfile;
        abort_unless($employer, 403, 'Employer profile not found.');

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        if (!$plan->is_active) {
            throw ValidationException::withMessages(['plan_id' => ['This plan is not available.']]);
        }

        $result = $this->billing->subscribe($employer, $plan, $data['billing_period']);

        return response()->json([
            'subscription' => $result['subscription']->load('plan'),
            'checkout_url' => $result['checkout_url'],
        ], 201);
    }

    /**
     * DELETE /employer/subscription — cancel the active subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $subscription = $employer?->subscription()->first();

        if (!$subscription || $subscription->status === 'cancelled') {
            throw ValidationException::withMessages([
                'subscription' => ['You have no active subscription to cancel.'],
            ]);
        }

        return response()->json([
            'subscription' => $this->billing->cancel($subscription),
            'message' => 'Your subscription has been cancelled.',
        ]);
    }
}
