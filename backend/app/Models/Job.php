<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employer_profile_id', 'title', 'slug', 'description', 'requirements',
        'benefits', 'category', 'employment_type', 'location_type',
        'location_city', 'location_state', 'location_country',
        'salary_min', 'salary_max', 'currency', 'salary_period', 'salary_visible',
        'experience_level', 'status', 'is_featured', 'expires_at',
    ];

    protected $casts = [
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'salary_visible' => 'boolean',
        'is_featured' => 'boolean',
        'expires_at' => 'date',
        'views_count' => 'integer',
        'applications_count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function employer()
    {
        return $this->belongsTo(EmployerProfile::class, 'employer_profile_id');
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_skills')
            ->withPivot('is_required');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedBy()
    {
        return $this->hasMany(SavedJob::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(fn ($q) => $q
            ->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
        );
    }

    public function scopeForExperienceLevel($query, string $level)
    {
        return $query->where('experience_level', $level);
    }

    // ── Lifecycle ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title).'-'.Str::random(6);
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
