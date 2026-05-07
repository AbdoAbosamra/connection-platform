<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\StoreJobRequest;
use App\Http\Requests\Employer\UpdateJobRequest;
use App\Models\Job;
use App\Services\JobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobController extends Controller
{
    public function __construct(private JobService $jobs) {}

    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        $jobs = $employer->jobs()
            ->with('skills:id,name')
            ->withCount('applications')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json($jobs);
    }

    public function store(StoreJobRequest $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer->hasCredits()) {
            throw ValidationException::withMessages([
                'credits' => ['You have no job post credits. Please upgrade your plan.'],
            ]);
        }

        $job = $this->jobs->createJob($employer, $request->validated());

        return response()->json(['job' => $job], 201);
    }

    public function show(Request $request, Job $job): JsonResponse
    {
        $this->authorize('view', $job);
        return response()->json(['job' => $job->load('skills')]);
    }

    public function update(UpdateJobRequest $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);
        $job = $this->jobs->updateJob($job, $request->validated());
        return response()->json(['job' => $job]);
    }

    public function destroy(Request $request, Job $job): JsonResponse
    {
        $this->authorize('delete', $job);
        $job->delete();
        return response()->json(['message' => 'Job deleted successfully.']);
    }

    public function toggleStatus(Request $request, Job $job): JsonResponse
    {
        $this->authorize('update', $job);

        $newStatus = $job->status === 'active' ? 'paused' : 'active';
        $job->update(['status' => $newStatus]);

        return response()->json(['job' => $job->fresh(), 'status' => $newStatus]);
    }
}
