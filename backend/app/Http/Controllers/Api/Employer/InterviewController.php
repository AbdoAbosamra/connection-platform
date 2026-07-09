<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\StoreInterviewRequest;
use App\Models\InterviewSchedule;
use App\Models\JobApplication;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function __construct(private InterviewService $interviews) {}

    /**
     * GET /employer/interviews — all interviews across the employer's jobs.
     */
    public function index(Request $request): JsonResponse
    {
        $employerId = $request->user()->employerProfile?->id;

        $interviews = InterviewSchedule::whereHas('application.job', fn ($q) => $q
            ->where('employer_profile_id', $employerId)
        )
            ->with([
                'application:id,job_id,job_seeker_profile_id',
                'application.job:id,title,slug',
                'application.jobSeeker:id,user_id',
                'application.jobSeeker.user:id,name',
            ])
            ->orderBy('scheduled_at')
            ->paginate(20);

        return response()->json($interviews);
    }

    /**
     * POST /employer/applications/{application}/interviews
     */
    public function store(StoreInterviewRequest $request, JobApplication $application): JsonResponse
    {
        // Reuse the JobApplication policy: employer must own the application's job.
        $this->authorize('update', $application);

        $interview = $this->interviews->schedule($application, $request->user(), $request->validated());

        return response()->json(['interview' => $interview], 201);
    }

    /**
     * PATCH /employer/interviews/{interview}
     */
    public function update(StoreInterviewRequest $request, InterviewSchedule $interview): JsonResponse
    {
        $this->authorize('update', $interview->application);

        $interview = $this->interviews->reschedule($interview, $request->validated());

        return response()->json(['interview' => $interview]);
    }

    /**
     * DELETE /employer/interviews/{interview}
     */
    public function destroy(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $this->authorize('update', $interview->application);

        $interview = $this->interviews->cancel($interview);

        return response()->json(['interview' => $interview]);
    }
}
