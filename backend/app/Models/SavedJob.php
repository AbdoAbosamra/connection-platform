<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedJob extends Model
{
    protected $fillable = ['job_id', 'job_seeker_profile_id'];

    public function job()       { return $this->belongsTo(Job::class); }
    public function jobSeeker() { return $this->belongsTo(JobSeekerProfile::class, 'job_seeker_profile_id'); }
}
