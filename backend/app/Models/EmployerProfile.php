<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'company_name', 'company_slug', 'description', 'industry',
        'company_size', 'website', 'logo', 'headquarters_city', 'headquarters_state',
        'headquarters_country', 'linkedin_url', 'twitter_url', 'founded_year',
        'is_verified', 'verified_at', 'verification_method', 'linkedin_id',
        'is_featured', 'subscription_tier', 'job_post_credits',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_featured' => 'boolean',
        'founded_year' => 'integer',
        'job_post_credits' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function activeJobs()
    {
        return $this->hasMany(Job::class)->where('status', 'active');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function subscription()
    {
        return $this->hasOne(EmployerSubscription::class)->latestOfMany();
    }

    // ── Lifecycle ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $profile) {
            if (empty($profile->company_slug)) {
                // Generate a candidate slug. We do NOT loop here because that
                // approach is racy under concurrent registrations. Instead we
                // seed a random suffix up front and rely on the DB unique
                // constraint as the single source of truth. The registration
                // service catches QueryException (error 1062) and retries once.
                $base = Str::slug($profile->company_name);
                $profile->company_slug = static::where('company_slug', $base)->exists()
                    ? $base.'-'.Str::random(6)
                    : $base;
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function hasCredits(): bool
    {
        return $this->subscription_tier !== 'free' || $this->job_post_credits > 0;
    }

    /**
     * Mark this employer as verified via a given method (domain|linkedin|payment).
     * Idempotent — re-verifying just refreshes the timestamp/method.
     */
    public function markVerified(string $method, ?string $linkedinId = null): void
    {
        $this->forceFill([
            'is_verified' => true,
            'verified_at' => now(),
            'verification_method' => $method,
            'linkedin_id' => $linkedinId ?? $this->linkedin_id,
        ])->save();
    }

    public function decrementCredits(): void
    {
        if ($this->subscription_tier === 'free') {
            $affected = DB::table('employer_profiles')
                ->where('id', $this->id)
                ->where('job_post_credits', '>', 0)
                ->decrement('job_post_credits');

            if ($affected === 0) {
                throw new \RuntimeException('Insufficient job post credits.');
            }

            $this->job_post_credits = max(0, $this->job_post_credits - 1);
        }
    }
}
