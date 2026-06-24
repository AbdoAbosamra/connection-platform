<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployerSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_profile_id', 'subscription_plan_id', 'stripe_subscription_id',
        'stripe_customer_id', 'billing_period', 'status', 'trial_ends_at',
        'current_period_start', 'current_period_end', 'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function employerProfile()
    {
        return $this->belongsTo(EmployerProfile::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }
}
