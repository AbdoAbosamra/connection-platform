<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stripe webhook receiver. Unauthenticated (Stripe calls it server-to-server)
 * but verified via the signing secret when configured.
 */
class BillingWebhookController extends Controller
{
    public function __construct(private BillingService $billing) {}

    public function handle(Request $request): JsonResponse
    {
        $secret = config('billing.stripe.webhook_secret');

        // Verify the Stripe signature when a webhook secret is configured.
        if (!empty($secret) && !$this->signatureValid($request, $secret)) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $handled = $this->billing->handleWebhook($request->all());

        return response()->json(['handled' => $handled]);
    }

    /**
     * Verify Stripe's "Stripe-Signature" header (HMAC-SHA256 of "{t}.{payload}").
     */
    private function signatureValid(Request $request, string $secret): bool
    {
        $header = $request->header('Stripe-Signature', '');
        if (!preg_match('/t=(\d+),v1=([a-f0-9]+)/', $header, $m)) {
            return false;
        }

        [, $timestamp, $signature] = $m;
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
