<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $seeker = $request->user()->jobSeekerProfile;

        // Single aggregate query instead of 4 separate counts.
        $agg = JobApplication::where('job_seeker_profile_id', $seeker->id)
            ->selectRaw("
                count(*) as total,
                sum(status = 'interview_scheduled') as interviews,
                sum(status in ('offer_extended','hired')) as offers
            ")
            ->first();

        return response()->json([
            'total_applications' => (int) $agg->total,
            'saved_jobs' => $seeker->savedJobs()->count(),
            'interviews_scheduled' => (int) $agg->interviews,
            'offers' => (int) $agg->offers,
        ]);
    }
}
