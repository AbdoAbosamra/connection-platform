<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\SavedJob;
use App\Services\JobMatchingService;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        private JobService $jobs,
        private JobMatchingService $matching
    ) {}

    public function recommended(Request $request): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile()->with('skills')->firstOrFail();
        $recommended = $this->matching->recommendJobsFor($seeker);

        return response()->json(['jobs' => $recommended]);
    }

    public function saved(Request $request): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;

        $saved = SavedJob::where('job_seeker_profile_id', $seeker->id)
            ->with(['job.employer:id,company_name,logo', 'job.skills:id,name'])
            ->latest()
            ->paginate(15);

        return response()->json($saved);
    }

    public function save(Request $request, Job $job): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;

        SavedJob::firstOrCreate([
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->id,
        ]);

        return response()->json(['message' => 'Job saved.']);
    }

    public function unsave(Request $request, Job $job): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;

        SavedJob::where([
            'job_id' => $job->id,
            'job_seeker_profile_id' => $seeker->id,
        ])->delete();

        return response()->json(['message' => 'Job removed from saved.']);
    }
}
