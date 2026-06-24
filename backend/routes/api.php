<?php

use App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingWebhookController;
use App\Http\Controllers\Api\Employer;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\JobSeeker;
use App\Http\Controllers\Api\LinkedInAuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfessionalController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SubscriptionPlanController;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// ──────────────────────────────────────────────────────────────────────────────
// Public
// ──────────────────────────────────────────────────────────────────────────────

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Password reset (tight throttle to deter abuse / enumeration attempts)
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);
});

// Public read endpoints — throttled to deter scraping / abuse by anonymous
// clients while staying generous for genuine browsing (90 req/min per IP).
Route::middleware('throttle:90,1')->group(function () {
    // Job listings
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{slug}', [JobController::class, 'show']);

    // Pricing
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index']);

    // Professional (job-seeker) profiles — only profile_complete=true are surfaced
    Route::get('/professionals', [ProfessionalController::class, 'index']);
    Route::get('/professionals/{profile}', [ProfessionalController::class, 'show']);

    // Skills list (used in profile builder)
    Route::get('/skills', function (Request $request) {
        $search = $request->filled('search')
            ? str_replace(['%', '_'], ['\\%', '\\_'], $request->search)
            : null;

        $skills = Skill::when(
            $search,
            fn ($q) => $q->where('name', 'like', "%{$search}%")
        )->orderBy('name')->limit(50)->get(['id', 'name', 'category']);

        return response()->json(['skills' => $skills]);
    });
});

// Stripe webhook (server-to-server; verified via signing secret, not auth).
// Intentionally unthrottled so legitimate event bursts are never dropped.
Route::post('/billing/webhook', [BillingWebhookController::class, 'handle']);

// LinkedIn OAuth callback (browser redirect from LinkedIn — unauthenticated,
// the employer is carried in a signed state parameter)
Route::get('/auth/linkedin/callback', [LinkedInAuthController::class, 'callback']);

// ──────────────────────────────────────────────────────────────────────────────
// Authenticated (any role)
// ──────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'active', 'throttle:60,1'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Broadcasting (WebSocket) channel authorization for the SPA's bearer token.
    // Echo posts {channel_name, socket_id} here; channel callbacks live in
    // routes/channels.php. Higher throttle — Echo re-auths on every subscribe.
    Route::post('/broadcasting/auth', fn (Request $request) => Broadcast::auth($request))
        ->middleware('throttle:120,1');

    // In-app notifications (poll endpoints get a higher budget like messaging)
    Route::middleware('throttle:120,1')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    });
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Content reports (report a job or a user) — tight throttle to curb abuse
    Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:20,1');
});

// ──────────────────────────────────────────────────────────────────────────────
// Employer routes
// ──────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'active', 'role:employer', 'throttle:60,1'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {

        // Stats
        Route::get('/stats', [Employer\StatsController::class, 'index']);

        // Profile
        Route::get('/profile', [Employer\ProfileController::class, 'show']);
        Route::put('/profile', [Employer\ProfileController::class, 'update']);

        // Verification (corporate-domain employers are auto-verified at signup)
        Route::get('/verification', [Employer\VerificationController::class, 'show']);
        Route::post('/verification/payment', [Employer\VerificationController::class, 'payment']);
        Route::get('/verification/linkedin/redirect', [LinkedInAuthController::class, 'redirect']);

        // Jobs — posting requires a verified employer
        Route::get('/jobs', [Employer\JobController::class, 'index']);
        Route::post('/jobs', [Employer\JobController::class, 'store'])->middleware('verified.employer');
        Route::get('/jobs/{job}', [Employer\JobController::class, 'show']);
        Route::put('/jobs/{job}', [Employer\JobController::class, 'update']);
        Route::delete('/jobs/{job}', [Employer\JobController::class, 'destroy']);
        Route::patch('/jobs/{job}/toggle-status', [Employer\JobController::class, 'toggleStatus']);

        // Applications
        Route::get('/applications', [Employer\ApplicationController::class, 'index']);
        Route::get('/applications/{application}', [Employer\ApplicationController::class, 'show']);
        Route::patch('/applications/{application}/status', [Employer\ApplicationController::class, 'updateStatus']);

        // Interview scheduling
        Route::post('/applications/{application}/interviews', [Employer\InterviewController::class, 'store']);
        Route::patch('/interviews/{interview}', [Employer\InterviewController::class, 'update']);
        Route::delete('/interviews/{interview}', [Employer\InterviewController::class, 'destroy']);

        // Subscription / billing
        Route::get('/subscription', [Employer\SubscriptionController::class, 'show']);
        Route::post('/subscription', [Employer\SubscriptionController::class, 'store']);
        Route::delete('/subscription', [Employer\SubscriptionController::class, 'destroy']);

        // Messaging — read/poll endpoints get a higher throttle budget (120/min)
        // because the frontend polls every 15 s (4 req/min × 2 endpoints = 8 req/min).
        // Send is throttled tightly (30/min) to prevent spam.
        Route::middleware('throttle:120,1')->group(function () {
            Route::get('/conversations', [Employer\MessageController::class, 'conversations']);
            Route::get('/conversations/unread-count', [Employer\MessageController::class, 'unreadCount']);
            Route::get('/conversations/{conversation}/messages', [Employer\MessageController::class, 'messages']);
            Route::post('/conversations/{conversation}/read', [Employer\MessageController::class, 'markRead']);
        });
        Route::post('/conversations', [Employer\MessageController::class, 'initiate']);
        Route::post('/conversations/{conversation}/messages', [Employer\MessageController::class, 'send'])->middleware('throttle:30,1');
    });

// ──────────────────────────────────────────────────────────────────────────────
// Job Seeker routes
// ──────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'active', 'role:job_seeker', 'throttle:60,1'])
    ->prefix('job-seeker')
    ->name('job_seeker.')
    ->group(function () {

        // Stats
        Route::get('/stats', [JobSeeker\StatsController::class, 'index']);

        // Profile
        Route::get('/profile', [JobSeeker\ProfileController::class, 'show']);
        Route::put('/profile', [JobSeeker\ProfileController::class, 'update']);
        Route::get('/profile/resume', [JobSeeker\ProfileController::class, 'resume']);

        // Job discovery
        Route::get('/jobs/recommended', [JobSeeker\JobController::class, 'recommended']);
        Route::get('/jobs/saved', [JobSeeker\JobController::class, 'saved']);
        Route::post('/jobs/{job}/save', [JobSeeker\JobController::class, 'save']);
        Route::delete('/jobs/{job}/save', [JobSeeker\JobController::class, 'unsave']);

        // Applications
        Route::get('/applications', [JobSeeker\ApplicationController::class, 'index']);
        Route::post('/jobs/{job}/apply', [JobSeeker\ApplicationController::class, 'store']);
        Route::get('/applications/{application}', [JobSeeker\ApplicationController::class, 'show']);
        Route::patch('/applications/{application}/withdraw', [JobSeeker\ApplicationController::class, 'withdraw']);

        // Interviews (view, confirm, decline)
        Route::get('/interviews', [JobSeeker\InterviewController::class, 'index']);
        Route::patch('/interviews/{interview}/confirm', [JobSeeker\InterviewController::class, 'confirm']);
        Route::patch('/interviews/{interview}/cancel', [JobSeeker\InterviewController::class, 'cancel']);

        // Messaging — same higher throttle budget for polling endpoints
        Route::middleware('throttle:120,1')->group(function () {
            Route::get('/conversations', [JobSeeker\MessageController::class, 'conversations']);
            Route::get('/conversations/unread-count', [JobSeeker\MessageController::class, 'unreadCount']);
            Route::get('/conversations/{conversation}/messages', [JobSeeker\MessageController::class, 'messages']);
            Route::post('/conversations/{conversation}/read', [JobSeeker\MessageController::class, 'markRead']);
        });
        Route::post('/conversations/{conversation}/messages', [JobSeeker\MessageController::class, 'send'])->middleware('throttle:30,1');
    });

// ──────────────────────────────────────────────────────────────────────────────
// Admin routes
// ──────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth:sanctum', 'active', 'role:admin', 'throttle:60,1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [Admin\DashboardController::class, 'stats']);
        Route::get('/dashboard/growth', [Admin\DashboardController::class, 'userGrowth']);

        Route::get('/users', [Admin\UserController::class, 'index']);
        Route::get('/users/{user}', [Admin\UserController::class, 'show']);
        Route::patch('/users/{user}/toggle-active', [Admin\UserController::class, 'toggleActive']);
        Route::delete('/users/{user}', [Admin\UserController::class, 'destroy']);
        // GDPR "Right to be Forgotten" — irreversible PII erasure.
        Route::delete('/users/{user}/forget', [Admin\UserController::class, 'forget']);

        Route::get('/jobs', [Admin\JobController::class, 'index']);
        Route::patch('/jobs/{job}/feature', [Admin\JobController::class, 'feature']);
        Route::delete('/jobs/{job}', [Admin\JobController::class, 'destroy']);
        Route::post('/jobs/{id}/restore', [Admin\JobController::class, 'restore']);

        Route::get('/reports', [Admin\ReportController::class, 'index']);
        Route::patch('/reports/{report}/resolve', [Admin\ReportController::class, 'resolve']);

        // Moderation layer
        Route::post('/reports/{report}/action', [Admin\ModerationController::class, 'act']);
        Route::get('/moderation/most-flagged', [Admin\ModerationController::class, 'mostFlagged']);
        Route::get('/moderation/logs', [Admin\ModerationController::class, 'logs']);
    });
