<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Services\Verification\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(private VerificationService $verification) {}

    /**
     * GET /employer/verification — current status + which methods are available.
     */
    public function show(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        return response()->json([
            'is_verified' => (bool) $employer?->is_verified,
            'verified_at' => $employer?->verified_at,
            'method' => $employer?->verification_method,
            'methods_available' => [
                'linkedin' => $this->verification->linkedInEnabled(),
                'payment' => true, // always available (mock locally, Stripe in prod)
            ],
        ]);
    }

    /**
     * POST /employer/verification/payment — verify via a small payment authorization.
     * Mock gateway verifies instantly; Stripe returns a checkout URL.
     */
    public function payment(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        abort_unless($employer, 403, 'Employer profile not found.');

        if ($employer->is_verified) {
            return response()->json(['is_verified' => true, 'message' => 'Already verified.']);
        }

        $result = $this->verification->verifyByPayment($employer);

        return response()->json([
            'is_verified' => $result['verified'],
            'checkout_url' => $result['checkout_url'],
            'message' => $result['verified']
                ? 'Your company is now verified.'
                : 'Complete the payment to finish verification.',
        ]);
    }
}
