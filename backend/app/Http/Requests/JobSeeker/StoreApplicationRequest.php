<?php

namespace App\Http\Requests\JobSeeker;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'cover_letter'    => ['nullable', 'string', 'min:100', 'max:5000'],
            'resume'          => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'expected_salary' => ['nullable', 'integer', 'min:0'],
            'currency'        => ['nullable', 'string', 'size:3'],
        ];
    }
}
