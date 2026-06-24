<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:3000'],
            'industry' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'headquarters_city' => ['nullable', 'string', 'max:100'],
            'headquarters_state' => ['nullable', 'string', 'max:100'],
            'headquarters_country' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'founded_year' => ['nullable', 'integer', 'min:1800', 'max:'.date('Y')],
        ];
    }
}
