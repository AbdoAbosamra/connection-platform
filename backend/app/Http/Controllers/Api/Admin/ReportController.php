<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reports = Report::with('reporter:id,name,email', 'resolvedBy:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($reports);
    }

    public function resolve(Request $request, Report $report): JsonResponse
    {
        $request->validate([
            'status'          => ['required', 'in:resolved,dismissed'],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status'          => $request->status,
            'resolved_by'     => $request->user()->id,
            'resolution_note' => $request->resolution_note,
            'resolved_at'     => now(),
        ]);

        return response()->json(['report' => $report->fresh()]);
    }
}
