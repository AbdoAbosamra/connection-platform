<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\StoreApplicationRequest;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private ApplicationService $applications) {}

    public function index(Request $request): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;

        $apps = JobApplication::where('job_seeker_profile_id', $seeker->id)
            // Filter out applications for soft-deleted jobs — the job relation
            // would be null and cause frontend null-pointer errors on render.
            ->whereHas('job', fn ($q) => $q->withoutTrashed())
            ->with([
                'job:id,title,slug,location_type,employment_type,employer_profile_id',
                'job.employer:id,user_id,company_name,logo',
                'jobSeeker:id,user_id',
                'latestInterview',
            ])
            ->latest()
            ->paginate(15);

        return response()->json($apps);
    }

    public function store(StoreApplicationRequest $request, Job $job): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;
        $application = $this->applications->apply($job, $seeker, $request->validated());

        return response()->json(['application' => $application], 201);
    }

    public function show(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('view', $application);

        return response()->json([
            'application' => $application->load('job.employer', 'latestInterview'),
        ]);
    }

    public function withdraw(Request $request, JobApplication $application): JsonResponse
    {
        $this->authorize('update', $application);
        $this->applications->withdraw($application);

        return response()->json(['message' => 'Application withdrawn.']);
    }
}
