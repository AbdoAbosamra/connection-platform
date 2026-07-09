<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        // Single aggregate query for application stats instead of 3 separate counts.
        $agg = JobApplication::whereHas(
            'job', fn ($j) => $j->where('employer_profile_id', $employer->id)
        )
            // Date-range comparison instead of MONTH()/YEAR() so the query is
            // portable across MySQL (production) and SQLite (tests).
            ->selectRaw("
                count(*) as total,
                sum(status = 'shortlisted') as shortlisted,
                sum(status = 'hired' and updated_at >= ?) as hired_month
            ", [now()->startOfMonth()->toDateTimeString()])
            ->first();

        // Application breakdown by status — powers the Analytics module.
        $byStatus = JobApplication::whereHas(
            'job', fn ($j) => $j->where('employer_profile_id', $employer->id)
        )
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($n) => (int) $n)
            ->all();

        // Jobs grouped by hiring mode, and whether any international role exists —
        // the latter gates the (otherwise hidden) compliance tools in the UI.
        $byHiringMode = $employer->jobs()
            ->selectRaw('hiring_mode, count(*) as total')
            ->groupBy('hiring_mode')
            ->pluck('total', 'hiring_mode')
            ->map(fn ($n) => (int) $n)
            ->all();

        return response()->json([
            'active_jobs' => $employer->jobs()->where('status', 'active')->count(),
            'total_jobs' => $employer->jobs()->count(),
            'total_applications' => (int) $agg->total,
            'shortlisted' => (int) $agg->shortlisted,
            'hired_this_month' => (int) $agg->hired_month,
            'total_views' => (int) $employer->jobs()->sum('views_count'),
            'applications_by_status' => $byStatus,
            'jobs_by_hiring_mode' => $byHiringMode,
            'has_international_jobs' => ($byHiringMode['international_remote'] ?? 0) > 0,
        ]);
    }
}
