<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'min:100'],
            'requirements' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,freelance,internship'],
            'location_type' => ['nullable', 'in:remote'],
            'location_city' => ['nullable', 'string', 'max:100'],
            'location_state' => ['nullable', 'string', 'max:100'],
            'location_country' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => [
                'nullable',
                'integer',
                'min:0',
                Rule::when(
                    $this->filled('salary_min') && $this->filled('salary_max'),
                    ['gte:salary_min']
                ),
            ],
            'currency' => ['nullable', 'string', 'size:3'],
            'salary_period' => ['nullable', 'in:hourly,monthly,annual'],
            'salary_visible' => ['boolean'],
            'experience_level' => ['required', 'in:entry,mid,senior,lead,executive'],
            'status' => ['nullable', 'in:draft,active'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'skills' => ['nullable', 'array'],
            'skills.*.id' => ['required', 'exists:skills,id'],
            'skills.*.is_required' => ['boolean'],
        ];
    }
}
