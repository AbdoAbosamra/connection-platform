<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileUploadService
{
    private const ALLOWED_RESUME_TYPES = ['application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    private const MAX_RESUME_SIZE_KB = 5120;   // 5 MB

    private const MAX_IMAGE_SIZE_KB = 2048;   // 2 MB

    public function uploadResume(UploadedFile $file, string $folder = 'resumes'): string
    {
        $this->validateMime($file, self::ALLOWED_RESUME_TYPES);
        $this->validateSize($file, self::MAX_RESUME_SIZE_KB);

        return $this->store($file, $folder);
    }

    public function uploadImage(UploadedFile $file, string $folder = 'images'): string
    {
        $this->validateMime($file, self::ALLOWED_IMAGE_TYPES);
        $this->validateSize($file, self::MAX_IMAGE_SIZE_KB);

        return $this->store($file, $folder);
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function store(UploadedFile $file, string $folder): string
    {
        $name = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs($folder, $name, 'public');
    }

    private function validateMime(UploadedFile $file, array $allowed): void
    {
        if (!in_array($file->getMimeType(), $allowed)) {
            // Throw 422 ValidationException so the frontend receives a readable
            // error message instead of a generic 500. InvalidArgumentException
            // would bubble up as a 500 through the catch-all exception handler.
            throw ValidationException::withMessages([
                'file' => ['File type not allowed. Accepted types: '.implode(', ', $allowed)],
            ]);
        }
    }

    private function validateSize(UploadedFile $file, int $maxKb): void
    {
        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => ['File exceeds the maximum allowed size of '.($maxKb / 1024).' MB.'],
            ]);
        }
    }
}
