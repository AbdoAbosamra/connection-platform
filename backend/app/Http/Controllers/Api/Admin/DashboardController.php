<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends AdminController
{
    public function stats(): JsonResponse
    {
        // Collapse 9 separate count() round-trips into 2 aggregate queries:
        // one for users (all breakdowns), one for jobs (all breakdowns).
        // Remaining two (applications) are kept as 2 queries for readability —
        // groupBy status is already a single query.

        $userAgg = User::selectRaw(
            'count(*) as total,
             sum(role = ?) as employers,
             sum(role = ?) as seekers,
             sum(month(created_at) = ? and year(created_at) = ?) as this_month',
            ['employer', 'job_seeker', now()->month, now()->year]
        )->first();

        $jobAgg = Job::selectRaw(
            'count(*) as total,
             sum(status = ?) as active,
             sum(status in (?,?)) as closed',
            ['active', 'closed', 'expired']
        )->first();

        $stats = [
            'users' => [
                'total' => (int) $userAgg->total,
                'employers' => (int) $userAgg->employers,
                'seekers' => (int) $userAgg->seekers,
                'this_month' => (int) $userAgg->this_month,
            ],
            'jobs' => [
                'total' => (int) $jobAgg->total,
                'active' => (int) $jobAgg->active,
                'closed' => (int) $jobAgg->closed,
            ],
            'applications' => [
                'total' => JobApplication::count(),
                'this_month' => JobApplication::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'by_status' => JobApplication::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ],
            'recent_signups' => User::with(
                'employerProfile:id,user_id,company_name',
                'jobSeekerProfile:id,user_id,headline'
            )->latest()->take(10)->get(),
        ];

        return response()->json(['stats' => $stats]);
    }

    public function userGrowth(): JsonResponse
    {
        // DATE_FORMAT is MySQL-specific. For portability we keep it behind a
        // DB-driver check so the code still works in SQLite (tests/CI).
        $isMySQL = DB::connection()->getDriverName() === 'mysql';

        $dateExpr = $isMySQL
            ? DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            : DB::raw("strftime('%Y-%m', created_at) as month");

        $growth = User::select($dateExpr, DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json(['growth' => $growth]);
    }
}
