<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function __construct(private InterviewService $interviews) {}

    /**
     * GET /job-seeker/interviews — upcoming interviews for the current seeker.
     */
    public function index(Request $request): JsonResponse
    {
        $seekerId = $request->user()->jobSeekerProfile?->id;

        $interviews = InterviewSchedule::whereHas('application', fn ($q) => $q
            ->where('job_seeker_profile_id', $seekerId)
        )
            ->with(['application.job:id,title,slug,employer_profile_id', 'application.job.employer:id,company_name,logo'])
            ->orderBy('scheduled_at')
            ->paginate(20);

        return response()->json($interviews);
    }

    /**
     * PATCH /job-seeker/interviews/{interview}/confirm
     */
    public function confirm(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $this->authorizeSeeker($request, $interview);

        return response()->json(['interview' => $this->interviews->confirm($interview)]);
    }

    /**
     * PATCH /job-seeker/interviews/{interview}/cancel
     */
    public function cancel(Request $request, InterviewSchedule $interview): JsonResponse
    {
        $this->authorizeSeeker($request, $interview);

        return response()->json(['interview' => $this->interviews->cancel($interview)]);
    }

    /**
     * Ensure the interview belongs to the authenticated seeker's application.
     */
    private function authorizeSeeker(Request $request, InterviewSchedule $interview): void
    {
        abort_unless(
            $interview->application?->jobSeeker?->user_id === $request->user()->id,
            403,
            'This interview does not belong to you.'
        );
    }
}
