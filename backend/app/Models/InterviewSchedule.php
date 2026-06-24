<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_application_id', 'scheduled_by', 'scheduled_at',
        'duration_minutes', 'format', 'meeting_link',
        'location', 'notes', 'status', 'confirmed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function scheduledBy()
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}
