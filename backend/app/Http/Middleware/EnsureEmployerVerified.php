<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks unverified employers from privileged actions (posting jobs).
 * Corporate-domain employers are auto-verified at registration; everyone else
 * must verify via LinkedIn or a payment authorization first.
 *
 * Returns a 403 with `verification_required: true` so the SPA can route the
 * employer straight to the verification screen.
 */
class EnsureEmployerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $employer = $request->user()?->employerProfile;

        if ($employer && !$employer->is_verified) {
            return response()->json([
                'message' => 'Please verify your company before posting jobs.',
                'verification_required' => true,
            ], 403);
        }

        return $next($request);
    }
}
