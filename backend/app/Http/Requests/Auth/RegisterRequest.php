<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:employer,job_seeker'],
            'country' => ['nullable', 'string', 'max:100'],
            'company_name' => ['required_if:role,employer', 'nullable', 'string', 'max:150'],
        ];
    }
}
