<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Employer verification
    |--------------------------------------------------------------------------
    |
    | Corporate-domain employers are auto-verified at registration. Employers on
    | free/personal email must verify via LinkedIn OAuth or a small payment
    | authorization before they can post jobs.
    */

    // When true, the domain classifier also confirms the domain publishes MX
    // records before treating it as corporate. Disabled by default (and in tests)
    // because it makes a live DNS query.
    'check_mx' => env('VERIFICATION_CHECK_MX', false),

    // Nominal amount (in cents) authorized to prove a real payment method during
    // payment-based verification.
    'payment_amount' => env('VERIFICATION_PAYMENT_AMOUNT', 100),

    // LinkedIn OAuth (OpenID Connect). Leave the client id/secret empty to
    // disable LinkedIn verification — the endpoints then return 503 and the UI
    // hides the option, while payment verification still works.
    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT_URI', env('APP_URL').'/api/auth/linkedin/callback'),
    ],
];
