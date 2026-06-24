<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

/**
 * Public pricing endpoint — lists the active subscription plans.
 */
class SubscriptionPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price_monthly')
            ->get();

        return response()->json(['plans' => $plans]);
    }
}
