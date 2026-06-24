<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\Job;
use App\Models\Report;
use App\Models\User;
use App\Notifications\NewReportSubmitted;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Lets any authenticated user flag a job or another user for admin review.
 */
class ReportController extends Controller
{
    private const TYPE_MAP = [
        'job' => Job::class,
        'user' => User::class,
    ];

    public function store(StoreReportRequest $request): JsonResponse
    {
        $data = $request->validated();
        $modelClass = self::TYPE_MAP[$data['type']];

        // Verify the target actually exists (FK-style guard for a polymorphic ref).
        $target = $modelClass::find($data['id']);
        if (!$target) {
            throw ValidationException::withMessages(['id' => ['The reported item does not exist.']]);
        }

        // Users cannot report themselves.
        if ($modelClass === User::class && (int) $data['id'] === $request->user()->id) {
            throw ValidationException::withMessages(['id' => ['You cannot report yourself.']]);
        }

        // Prevent duplicate open reports from the same reporter on the same target.
        $alreadyReported = Report::where('reporter_id', $request->user()->id)
            ->where('reportable_type', $modelClass)
            ->where('reportable_id', $data['id'])
            ->where('status', 'open')
            ->exists();

        if ($alreadyReported) {
            throw ValidationException::withMessages([
                'id' => ['You have already reported this item; it is under review.'],
            ]);
        }

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $modelClass,
            'reportable_id' => $data['id'],
            'reason' => $data['reason'],
            'details' => $data['details'] ?? null,
            'status' => 'open',
        ]);

        // Auto-prioritise by reason and escalate repeat-offender targets.
        app(ModerationService::class)->prioritiseNewFlag($report);

        // Notify all admins via their in-app feed (outside any transaction).
        Notification::send(User::where('role', 'admin')->get(), new NewReportSubmitted($report->fresh()));

        return response()->json([
            'message' => 'Report submitted. Our team will review it shortly.',
            'report' => $report,
        ], 201);
    }
}
