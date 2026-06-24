<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\ModerationLog;
use App\Models\Report;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends AdminController
{
    public function __construct(private ModerationService $moderation) {}

    /**
     * POST /admin/reports/{report}/action
     * Apply a moderation action to a flag (warn / suspend / dismiss / remove / …).
     */
    public function act(Request $request, Report $report): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:'.implode(',', ModerationLog::ACTIONS)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $log = $this->moderation->act($request->user(), $report, $data['action'], $data['notes'] ?? null);

        return response()->json([
            'message' => 'Action recorded.',
            'log' => $log,
            'report' => $report->fresh(),
        ], 201);
    }

    /**
     * GET /admin/moderation/most-flagged?type=job|user
     * The dashboard's "most frequently flagged" leaderboard.
     */
    public function mostFlagged(Request $request): JsonResponse
    {
        $type = $request->input('type', 'job') === 'user' ? 'user' : 'job';
        $openOnly = $request->boolean('open_only', true);

        return response()->json([
            'type' => $type,
            'results' => $this->moderation->mostFlagged($type, 20, $openOnly),
        ]);
    }

    /**
     * GET /admin/moderation/logs
     * Accountability trail of every admin action.
     */
    public function logs(Request $request): JsonResponse
    {
        $logs = ModerationLog::with([
            'moderator:id,name',
            'user:id,name,email',
        ])
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->action))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->user_id))
            ->latest()
            ->paginate(25);

        return response()->json($logs);
    }
}
