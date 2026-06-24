<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $reports = Report::with('reporter:id,name,email', 'resolvedBy:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            // Surface the most urgent flags first: critical → high → normal → low,
            // then most recent. Repeat-offender targets bubble straight to the top.
            // (CASE is portable across MySQL and the SQLite test database.)
            ->orderByRaw("CASE priority WHEN 'critical' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->latest()
            ->paginate(20);

        return response()->json($reports);
    }

    public function resolve(Request $request, Report $report): JsonResponse
    {
        if ($report->status !== 'open') {
            return response()->json(['message' => 'Report already closed.'], 422);
        }

        $request->validate([
            'status' => ['required', 'in:resolved,dismissed'],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => $request->status,
            'resolved_by' => $request->user()->id,
            'resolution_note' => $request->resolution_note,
            'resolved_at' => now(),
        ]);

        return response()->json(['report' => $report->fresh()]);
    }
}
