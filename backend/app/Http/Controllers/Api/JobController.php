<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public-facing job listing endpoints (no auth required).
 */
class JobController extends Controller
{
    public function __construct(private JobService $jobs) {}

    public function index(Request $request): JsonResponse
    {
        // Remote-only platform: location_type and visa_sponsorship filters removed.
        // All jobs on this platform are remote; visa sponsorship column was dropped.
        $filters = $request->only([
            'q', 'category', 'experience_level',
            'employment_type', 'salary_min', 'salary_max', 'skills',
        ]);

        $jobs = $this->jobs->search($filters);

        // Merge facet counts into the paginator payload without changing its
        // shape (the SPA still reads data/current_page/… at the top level).
        return response()->json(array_merge($jobs->toArray(), [
            'facets' => $this->jobs->facets($filters),
        ]));
    }

    public function show(string $slug): JsonResponse
    {
        $job = Job::where('slug', $slug)->firstOrFail();
        $job = $this->jobs->getJobWithDetails($job);

        return response()->json(['job' => $job]);
    }

    /**
     * Real active-job counts per category, e.g. {"Engineering": 3, "Design": 1}.
     * Used by the homepage so the category tiles never show inflated numbers.
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'counts' => $this->jobs->facets([])['category'],
        ]);
    }
}
