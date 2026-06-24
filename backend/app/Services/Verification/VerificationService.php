<?php

namespace App\Services\Verification;

use App\Models\EmployerProfile;
use App\Services\Billing\BillingService;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class VerificationService
{
    public function __construct(
        private EmailDomainClassifier $classifier,
        private BillingService $billing,
    ) {}

    // ── Automatic (domain) ──────────────────────────────────────────────────

    /**
     * Apply automatic verification at registration time. Corporate-domain
     * employers are verified instantly with method 'domain'; everyone else is
     * left unverified and must complete a manual step.
     */
    public function autoVerify(EmployerProfile $employer, string $email): bool
    {
        if ($this->classifier->isCorporate($email)) {
            $employer->markVerified('domain');

            return true;
        }

        return false;
    }

    public function isDisposable(string $email): bool
    {
        return $this->classifier->isDisposable($email);
    }

    // ── LinkedIn OAuth ──────────────────────────────────────────────────────

    public function linkedInEnabled(): bool
    {
        return !empty(config('verification.linkedin.client_id'))
            && !empty(config('verification.linkedin.client_secret'));
    }

    /**
     * Build the LinkedIn authorization URL. `state` ties the callback back to the
     * initiating employer and is signed/verified by the caller.
     */
    public function linkedInAuthUrl(string $state): string
    {
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => config('verification.linkedin.client_id'),
            'redirect_uri' => config('verification.linkedin.redirect'),
            'state' => $state,
            'scope' => 'openid profile email',
        ]);

        return 'https://www.linkedin.com/oauth/v2/authorization?'.$params;
    }

    /**
     * Exchange the OAuth code for the LinkedIn profile and verify the employer.
     * Returns the LinkedIn subject id.
     */
    public function completeLinkedIn(EmployerProfile $employer, string $code): string
    {
        $token = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => config('verification.linkedin.redirect'),
            'client_id' => config('verification.linkedin.client_id'),
            'client_secret' => config('verification.linkedin.client_secret'),
        ]);

        if ($token->failed()) {
            throw new RuntimeException('LinkedIn token exchange failed.');
        }

        $profile = Http::withToken($token->json('access_token'))
            ->get('https://api.linkedin.com/v2/userinfo');

        if ($profile->failed()) {
            throw new RuntimeException('Failed to load LinkedIn profile.');
        }

        $sub = $profile->json('sub');
        $employer->markVerified('linkedin', $sub);

        return $sub;
    }

    // ── Payment authorization ───────────────────────────────────────────────

    /**
     * Verify via a small payment authorization. With the mock billing gateway
     * this succeeds instantly; with Stripe it returns a checkout URL the client
     * redirects to (the webhook / return then confirms — here we optimistically
     * verify on a non-redirect gateway).
     *
     * @return array{verified: bool, checkout_url: ?string}
     */
    public function verifyByPayment(EmployerProfile $employer): array
    {
        // Reuse the billing gateway selection. The mock gateway has no external
        // dependency, so a successful "authorization" verifies immediately.
        if (config('billing.driver', 'mock') !== 'stripe' || empty(config('billing.stripe.secret'))) {
            $employer->markVerified('payment');

            return ['verified' => true, 'checkout_url' => null];
        }

        $url = $this->createStripeAuthorization($employer);

        return ['verified' => false, 'checkout_url' => $url];
    }

    private function createStripeAuthorization(EmployerProfile $employer): string
    {
        $response = Http::withToken(config('billing.stripe.secret'))
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode' => 'payment',
                'success_url' => config('billing.success_url'),
                'cancel_url' => config('billing.cancel_url'),
                'client_reference_id' => 'verify_'.$employer->id,
                'line_items[0][quantity]' => 1,
                'line_items[0][price_data][currency]' => 'usd',
                'line_items[0][price_data][unit_amount]' => config('verification.payment_amount', 100),
                'line_items[0][price_data][product_data][name]' => 'Employer verification',
                'metadata[employer_profile_id]' => $employer->id,
                'metadata[purpose]' => 'verification',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Stripe verification session failed: '.$response->body());
        }

        return $response->json('url');
    }
}
