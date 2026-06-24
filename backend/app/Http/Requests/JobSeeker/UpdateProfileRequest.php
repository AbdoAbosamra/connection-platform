<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'headline' => ['nullable', 'string', 'max:200'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'resume' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],  // 10 MB — matches PHP upload_max_filesize in .user.ini
            'avatar' => ['nullable', 'image', 'max:2048'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'current_city' => ['nullable', 'string', 'max:100'],
            'current_country' => ['nullable', 'string', 'max:100'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'experience_level' => ['nullable', 'in:entry,mid,senior,lead,executive'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:50'],
            'current_job_title' => ['nullable', 'string', 'max:150'],
            'desired_job_title' => ['nullable', 'string', 'max:150'],
            'desired_salary_min' => ['nullable', 'integer', 'min:0'],
            // gte only fires when BOTH fields are present and non-empty.
            // An empty/absent desired_salary_max means "no upper bound" — never compare against min.
            'desired_salary_max' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(
                    $this->filled('desired_salary_min') && $this->filled('desired_salary_max'),
                    ['gte:desired_salary_min']
                ),
            ],
            'currency' => ['nullable', 'string', 'size:3'],
            'availability' => ['nullable', 'in:immediately,two_weeks,one_month,negotiable'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.proficiency' => ['nullable', 'in:beginner,intermediate,advanced,expert'],
        ];
    }
}
