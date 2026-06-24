<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id', 'reportable_type', 'reportable_id',
        'reason', 'details', 'status', 'priority', 'resolved_by',
        'resolution_note', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    /**
     * Reasons that automatically raise a flag's priority — the high-harm
     * categories a moderator should see first.
     */
    public const HIGH_PRIORITY_REASONS = [
        'scam', 'phishing', 'harassment', 'hate_speech', 'threats',
        'fake_listing', 'money_laundering', 'fake_profile', 'resume_fraud',
    ];

    // When a target collects this many open flags, every open flag on it is
    // escalated to 'critical' so repeat offenders surface automatically.
    public const AUTO_ESCALATE_THRESHOLD = 3;

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reportable()
    {
        return $this->morphTo();
    }

    public function moderationLogs()
    {
        return $this->hasMany(ModerationLog::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public static function priorityForReason(string $reason): string
    {
        return in_array($reason, self::HIGH_PRIORITY_REASONS, true) ? 'high' : 'normal';
    }
}
