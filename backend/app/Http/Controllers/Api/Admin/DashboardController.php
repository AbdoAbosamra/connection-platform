<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = [
            'users' => [
                'total'      => User::count(),
                'employers'  => User::where('role', 'employer')->count(),
                'seekers'    => User::where('role', 'job_seeker')->count(),
                'this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
            'jobs' => [
                'total'  => Job::count(),
                'active' => Job::where('status', 'active')->count(),
                'closed' => Job::whereIn('status', ['closed', 'expired'])->count(),
            ],
            'applications' => [
                'total'       => JobApplication::count(),
                'this_month'  => JobApplication::whereMonth('created_at', now()->month)->count(),
                'by_status'   => JobApplication::select('status', DB::raw('count(*) as count'))
                                               ->groupBy('status')->pluck('count', 'status'),
            ],
            'recent_signups' => User::with('employerProfile:id,user_id,company_name', 'jobSeekerProfile:id,user_id,headline')
                                    ->latest()->take(10)->get(),
        ];

        return response()->json(['stats' => $stats]);
    }

    public function userGrowth(): JsonResponse
    {
        $growth = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json(['growth' => $growth]);
    }
}
