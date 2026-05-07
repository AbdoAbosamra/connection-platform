<?php

namespace App\Http\Controllers\Api\JobSeeker;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobSeeker\UpdateProfileRequest;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private FileUploadService $files) {}

    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile()->with('skills')->firstOrFail();
        $profile->completion = $profile->completionPercentage();

        return response()->json(['profile' => $profile]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;
        $data    = $request->validated();

        if ($request->hasFile('resume')) {
            $this->files->delete($profile->resume);
            $data['resume'] = $this->files->uploadResume($request->file('resume'));
        }

        if ($request->hasFile('avatar')) {
            $user = $request->user();
            $this->files->delete($user->avatar);
            $user->update(['avatar' => $this->files->uploadImage($request->file('avatar'), 'avatars')]);
        }

        // Handle skill sync
        if (array_key_exists('skills', $data)) {
            $skillData = collect($data['skills'])->mapWithKeys(fn ($s) => [
                $s['id'] => ['proficiency' => $s['proficiency'] ?? 'intermediate'],
            ]);
            $profile->skills()->sync($skillData);
            unset($data['skills']);
        }

        $profile->update($data);
        $profile->update(['profile_complete' => $profile->completionPercentage() >= 70]);

        return response()->json([
            'profile'    => $profile->fresh()->load('skills'),
            'completion' => $profile->completionPercentage(),
        ]);
    }

    public function resume(Request $request): JsonResponse
    {
        $profile = $request->user()->jobSeekerProfile;

        if (!$profile->resume) {
            return response()->json(['message' => 'No resume uploaded.'], 404);
        }

        return response()->json([
            'resume_url' => asset('storage/' . $profile->resume),
        ]);
    }
}
