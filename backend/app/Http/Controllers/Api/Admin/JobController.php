<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = Job::withTrashed()
            ->with('employer:id,company_name,logo')
            ->withCount('applications')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->latest()
            ->paginate(20);

        return response()->json($jobs);
    }

    public function feature(Job $job): JsonResponse
    {
        $job->update(['is_featured' => !$job->is_featured]);
        return response()->json(['job' => $job->fresh()]);
    }

    public function destroy(Job $job): JsonResponse
    {
        $job->forceDelete();
        return response()->json(['message' => 'Job permanently deleted.']);
    }

    public function restore(int $id): JsonResponse
    {
        $job = Job::withTrashed()->findOrFail($id);
        $job->restore();
        return response()->json(['job' => $job]);
    }
}
