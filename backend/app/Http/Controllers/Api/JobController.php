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
        $jobs = $this->jobs->search($request->only([
            'q', 'category', 'location_type', 'experience_level',
            'employment_type', 'salary_min', 'salary_max',
            'visa_sponsorship', 'skills',
        ]));

        return response()->json($jobs);
    }

    public function show(string $slug): JsonResponse
    {
        $job = Job::where('slug', $slug)->firstOrFail();
        $job = $this->jobs->getJobWithDetails($job);

        return response()->json(['job' => $job]);
    }
}
