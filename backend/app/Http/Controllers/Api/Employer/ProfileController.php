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
        $profile = $request->user()->employerProfile;
        $data    = $request->validated();

        if ($request->hasFile('logo')) {
            $this->files->delete($profile->logo);
            $data['logo'] = $this->files->uploadImage($request->file('logo'), 'logos');
        }

        $profile->update($data);

        return response()->json(['profile' => $profile->fresh()]);
    }
}
