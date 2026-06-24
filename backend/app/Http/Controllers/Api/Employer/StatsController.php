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
            ->selectRaw("
                count(*) as total,
                sum(status = 'shortlisted') as shortlisted,
                sum(status = 'hired' and month(updated_at) = ? and year(updated_at) = ?) as hired_month
            ", [now()->month, now()->year])
            ->first();

        return response()->json([
            'active_jobs' => $employer->jobs()->where('status', 'active')->count(),
            'total_applications' => (int) $agg->total,
            'shortlisted' => (int) $agg->shortlisted,
            'hired_this_month' => (int) $agg->hired_month,
        ]);
    }
}
