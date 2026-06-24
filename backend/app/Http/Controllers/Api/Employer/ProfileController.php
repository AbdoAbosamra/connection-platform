<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employer\UpdateProfileRequest;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private FileUploadService $files) {}

    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->employerProfile()->with('subscription')->firstOrFail();

        return response()->json(['profile' => $profile]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();

        // company_name is NOT NULL at the DB level, so it must be supplied when the
        // profile is created lazily. In the normal flow the profile already exists
        // (created at registration); this fallback only matters for the edge case of
        // an employer whose profile is somehow missing. Without it, firstOrCreate
        // would attempt an INSERT with a null company_name and hit a constraint error.
        $profile = $request->user()->employerProfile()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['company_name' => $data['company_name'] ?? $request->user()->name."'s Company"]
        );

        if ($request->hasFile('logo')) {
            $this->files->delete($profile->logo);
            $data['logo'] = $this->files->uploadImage($request->file('logo'), 'logos');
        }

        $profile->update($data);

        return response()->json(['profile' => $profile->fresh()]);
    }
}
