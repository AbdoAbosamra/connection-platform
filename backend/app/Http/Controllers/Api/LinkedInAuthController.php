<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Verification\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * LinkedIn OAuth (OpenID Connect) for employer verification.
 *
 * The browser-side callback is unauthenticated, so the initiating employer is
 * carried through OAuth in a signed, time-boxed `state` value (encrypted user id).
 */
class LinkedInAuthController extends Controller
{
    public function __construct(private VerificationService $verification) {}

    /**
     * GET /employer/verification/linkedin/redirect — authenticated.
     * Returns the LinkedIn authorization URL for the SPA to open.
     */
    public function redirect(Request $request): JsonResponse
    {
        abort_unless($this->verification->linkedInEnabled(), 503, 'LinkedIn verification is not configured.');

        $state = Crypt::encryptString(json_encode([
            'uid' => $request->user()->id,
            'exp' => now()->addMinutes(15)->timestamp,
        ]));

        return response()->json(['url' => $this->verification->linkedInAuthUrl($state)]);
    }

    /**
     * GET /auth/linkedin/callback — public (LinkedIn redirects the browser here).
     * Validates state, completes the OAuth exchange, verifies the employer, and
     * redirects back into the SPA.
     */
    public function callback(Request $request): RedirectResponse
    {
        $frontend = explode(',', (string) env('FRONTEND_URL', config('app.url')))[0];
        $base = rtrim($frontend, '/').'/employer/verification';

        if ($request->filled('error') || !$request->filled('code') || !$request->filled('state')) {
            return redirect()->away($base.'?status=cancelled');
        }

        try {
            $payload = json_decode(Crypt::decryptString($request->input('state')), true);
            abort_if(!$payload || ($payload['exp'] ?? 0) < now()->timestamp, 400);

            $employer = User::findOrFail($payload['uid'])->employerProfile;
            abort_unless($employer, 404);

            $this->verification->completeLinkedIn($employer, $request->input('code'));
        } catch (\Throwable) {
            return redirect()->away($base.'?status=failed');
        }

        return redirect()->away($base.'?status=verified');
    }
}
