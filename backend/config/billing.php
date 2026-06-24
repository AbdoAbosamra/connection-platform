<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Billing Driver
    |--------------------------------------------------------------------------
    |
    | "mock"   — activates subscriptions instantly with no external call.
    |            Ideal for local development, tests, and demos.
    | "stripe" — creates a Stripe Checkout session; the subscription is activated
    |            by the webhook once payment succeeds. Requires STRIPE_SECRET.
    |
    | If "stripe" is selected but no secret key is configured, the service safely
    | falls back to "mock" so the platform is always runnable.
    |
    */
    'driver' => env('BILLING_DRIVER', 'mock'),

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Where Stripe Checkout redirects the user after success/cancel.
    'success_url' => env('BILLING_SUCCESS_URL', env('FRONTEND_URL', 'http://localhost:5173').'/employer/billing?status=success'),
    'cancel_url' => env('BILLING_CANCEL_URL', env('FRONTEND_URL', 'http://localhost:5173').'/employer/billing?status=cancelled'),
];
