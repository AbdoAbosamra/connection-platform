<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_profile_id', 'job_seeker_profile_id',
        'job_id', 'last_message_at', 'last_message_id',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function employer()
    {
        return $this->belongsTo(EmployerProfile::class, 'employer_profile_id');
    }

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeekerProfile::class, 'job_seeker_profile_id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->whereNot('sender_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}
