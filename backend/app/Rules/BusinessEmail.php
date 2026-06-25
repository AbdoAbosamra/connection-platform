<?php

namespace App\Rules;

use App\Services\Verification\EmailDomainClassifier;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects free / personal / disposable email providers — used to require that a
 * company (employer) account registers with a real business email address.
 *
 * It deliberately checks only the curated provider lists (no DNS/MX lookup) so
 * validation stays fast, deterministic, and offline-test-friendly. The optional
 * MX confirmation lives in EmailDomainClassifier::isCorporate() and is used by
 * the verification flow, not here.
 */
class BusinessEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Let the 'required' / 'email' rules report empty or malformed values.
        if (!is_string($value) || trim($value) === '') {
            return;
        }

        $classifier = app(EmailDomainClassifier::class);

        if ($classifier->isFreeProvider($value) || $classifier->isDisposable($value)) {
            $fail('Please use your company email address. Free or personal providers (Gmail, Yahoo, Outlook, etc.) are not accepted for company accounts.');
        }
    }
}
