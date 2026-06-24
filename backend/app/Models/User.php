<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'avatar', 'phone', 'country', 'timezone', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function employerProfile()
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function jobSeekerProfile()
    {
        return $this->hasOne(JobSeekerProfile::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    public function isJobSeeker(): bool
    {
        return $this->role === 'job_seeker';
    }

    public function profile(): EmployerProfile|JobSeekerProfile|null
    {
        return match ($this->role) {
            'employer' => $this->employerProfile,
            'job_seeker' => $this->jobSeekerProfile,
            default => null,
        };
    }

    /**
     * Send the password reset notification with a link to the SPA (not the API).
     * FRONTEND_URL may contain a comma-separated list (dev + preview); use the first.
     */
    public function sendPasswordResetNotification($token): void
    {
        $frontend = explode(',', (string) env('FRONTEND_URL', config('app.url')))[0];
        $url = rtrim($frontend, '/').'/reset-password?token='.$token.'&email='.urlencode($this->email);

        $this->notify(new ResetPasswordNotification($url));
    }
}
