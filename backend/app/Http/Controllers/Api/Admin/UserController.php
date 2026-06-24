<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Services\UserErasureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $allowedRoles = ['admin', 'employer', 'job_seeker'];
        $users = User::with('employerProfile:id,user_id,company_name', 'jobSeekerProfile:id,user_id,headline')
            ->when(
                $request->filled('role') && in_array($request->role, $allowedRoles),
                fn ($q) => $q->where('role', $request->role)
            )
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
            ))
            ->latest()
            ->paginate(20);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('employerProfile', 'jobSeekerProfile.skills'),
        ]);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json(['user' => $user->fresh(), 'is_active' => $user->is_active]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot delete your own account.'], 403);
        }

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot delete admin accounts.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    /**
     * GDPR Article 17 erasure — permanently anonymise a user's personal data
     * across the whole platform. Distinct from destroy() (a reversible soft
     * delete): this scrubs PII and uploaded files and cannot be undone.
     */
    public function forget(Request $request, User $user, UserErasureService $erasure): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Cannot erase your own account.'], 403);
        }

        if ($user->role === 'admin') {
            return response()->json(['message' => 'Cannot erase admin accounts.'], 403);
        }

        $erasure->forget($user);

        return response()->json(['message' => 'User personal data has been erased.']);
    }
}
