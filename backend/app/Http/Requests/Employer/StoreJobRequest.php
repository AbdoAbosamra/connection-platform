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

            // Hiring mode — local by default, remote/international are opt-in.
            'hiring_mode' => ['nullable', 'in:local,national_remote,international_remote'],
            'location_type' => ['nullable', 'in:remote,hybrid,on_site'],
            'location_city' => ['nullable', 'string', 'max:100'],
            'location_state' => ['nullable', 'string', 'max:100'],
            'location_country' => ['nullable', 'string', 'max:100'],

            // International Remote only — required/validated when that mode is chosen.
            // Note: intentionally NO visa-sponsorship or work-authorization fields;
            // international candidates stay in their own country and work remotely.
            'accepted_countries' => [Rule::requiredIf($this->input('hiring_mode') === 'international_remote'), 'nullable', 'array'],
            'accepted_countries.*' => ['string', 'max:100'],
            'time_zones' => ['nullable', 'array'],
            'time_zones.*' => ['string', 'max:100'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:100'],
            'contract_type' => ['nullable', 'in:contractor,remote_employee'],
            'working_hours' => ['nullable', 'string', 'max:200'],
            'currency_preference' => ['nullable', 'string', 'size:3'],
            'payroll_preference' => ['nullable', 'string', 'max:200'],
            'collaboration_preferences' => ['nullable', 'string', 'max:2000'],
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
