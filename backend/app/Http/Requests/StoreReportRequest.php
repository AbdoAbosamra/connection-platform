<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Allowed report reasons, aligned with the Community Guidelines.
     * The reports.reason column is a free string, so this list is the single
     * source of truth for what the API accepts.
     */
    public const REASONS = [
        // Professional Conduct
        'harassment', 'hate_speech', 'threats',
        // Honest Representation
        'fake_profile', 'resume_fraud', 'fake_listing', 'misleading_compensation',
        // Fraud Prevention
        'application_fee', 'job_selling', 'pyramid_scheme', 'phishing', 'money_laundering',
        // General
        'spam', 'scam', 'duplicate', 'inappropriate', 'other',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Public-facing alias for the polymorphic target. We map 'job'/'user'
            // to the fully-qualified model class in the controller so the API never
            // exposes internal namespaces and clients cannot inject arbitrary types.
            'type' => ['required', 'in:job,user'],
            'id' => ['required', 'integer', 'min:1'],
            // Reasons map to the RemoteArena Community Guidelines categories:
            // Professional Conduct, Honest Representation, and Fraud Prevention.
            'reason' => ['required', 'string', 'in:'.implode(',', self::REASONS)],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
