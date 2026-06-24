<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'format' => ['required', 'in:video,phone,in_person'],
            // A meeting link is required for video interviews; a physical location
            // is required for in-person ones. Phone interviews need neither.
            'meeting_link' => ['nullable', 'url', 'max:500', Rule::requiredIf($this->input('format') === 'video')],
            'location' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->input('format') === 'in_person')],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
