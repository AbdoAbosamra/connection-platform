<?php

namespace App\Services\Verification;

/**
 * Decides whether an email belongs to a real *company* domain (corporate) or a
 * free / personal / disposable provider. Corporate-domain employers are
 * auto-verified at registration; everyone else must complete a verification step.
 *
 * The check is layered:
 *   1. Reject obviously-personal providers (curated free-mail list).
 *   2. Reject disposable / throwaway providers (curated list).
 *   3. Optionally confirm the domain can actually receive mail (MX records) —
 *      gated by config('verification.check_mx') so the test suite stays offline.
 */
class EmailDomainClassifier
{
    /** Free consumer mailbox providers — never treated as corporate. */
    public const FREE_PROVIDERS = [
        'gmail.com', 'googlemail.com', 'yahoo.com', 'yahoo.co.uk', 'yahoo.co.in',
        'ymail.com', 'rocketmail.com', 'hotmail.com', 'hotmail.co.uk', 'outlook.com',
        'live.com', 'msn.com', 'aol.com', 'icloud.com', 'me.com', 'mac.com',
        'protonmail.com', 'proton.me', 'pm.me', 'gmx.com', 'gmx.de', 'mail.com',
        'yandex.com', 'yandex.ru', 'zoho.com', 'zohomail.com', 'fastmail.com',
        'hey.com', 'tutanota.com', 'tuta.io', 'hushmail.com', 'qq.com', '163.com',
        '126.com', 'sina.com', 'naver.com', 'daum.net', 'web.de', 'comcast.net',
        'verizon.net', 'att.net', 'sbcglobal.net', 'cox.net', 'btinternet.com',
    ];

    /** Disposable / throwaway providers — explicitly blocked even from verifying. */
    public const DISPOSABLE_PROVIDERS = [
        'mailinator.com', 'guerrillamail.com', 'guerrillamail.info', '10minutemail.com',
        'temp-mail.org', 'tempmail.com', 'throwawaymail.com', 'yopmail.com', 'getnada.com',
        'trashmail.com', 'sharklasers.com', 'dispostable.com', 'maildrop.cc', 'mintemail.com',
        'fakeinbox.com', 'mailnesia.com', 'mohmal.com', 'tempinbox.com', 'spamgourmet.com',
        'discard.email', 'emailondeck.com', 'mailcatch.com', 'tempr.email', 'moakt.com',
    ];

    public function domainOf(string $email): string
    {
        return strtolower(trim(substr(strrchr($email, '@') ?: '', 1)));
    }

    public function isFreeProvider(string $email): bool
    {
        return in_array($this->domainOf($email), self::FREE_PROVIDERS, true);
    }

    public function isDisposable(string $email): bool
    {
        return in_array($this->domainOf($email), self::DISPOSABLE_PROVIDERS, true);
    }

    /**
     * A corporate email is one that is NOT a free or disposable provider and
     * (when enabled) resolves to a mail-capable domain.
     */
    public function isCorporate(string $email): bool
    {
        $domain = $this->domainOf($email);

        if ($domain === '' || $this->isFreeProvider($email) || $this->isDisposable($email)) {
            return false;
        }

        if (config('verification.check_mx', false) && !$this->domainHasMx($domain)) {
            return false;
        }

        return true;
    }

    /**
     * Confirms the domain publishes MX (or at least A) records, i.e. it is a real
     * mail destination rather than a typo / vanity domain. Network failures are
     * treated as "cannot confirm" → not corporate (fail safe toward verification).
     */
    public function domainHasMx(string $domain): bool
    {
        if (!function_exists('checkdnsrr')) {
            return true; // can't check → don't block
        }

        return @checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A');
    }
}
