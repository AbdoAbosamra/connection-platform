<?php

use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\EnsureEmployerVerified;
use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureRole::class,
            'active' => EnsureActive::class,
            'verified.employer' => EnsureEmployerVerified::class,
        ]);

        // statefulApi() is intentionally NOT called here.
        // This API uses Bearer token auth (Authorization: Bearer {token}).
        // statefulApi() adds EnsureFrontendRequestsAreStateful which enforces
        // CSRF verification for cookie-based SPA sessions — but Bearer tokens
        // are not vulnerable to CSRF, so that middleware would only cause 419 errors.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always return JSON for API routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Resource not found.'], 404);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        // Ensure all unhandled exceptions on API routes return JSON, never HTML.
        // This prevents the frontend from receiving an HTML error page that it
        // can't parse — the generic fallback would show instead of the real message.
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = app()->hasDebugModeEnabled()
                    ? $e->getMessage()
                    : 'An unexpected error occurred. Please try again.';

                return response()->json(['message' => $message], $status);
            }
        });
    })->create();
