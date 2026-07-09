<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly', 'price_annual',
        'job_posts_limit', 'featured_listings', 'candidate_search', 'advanced_search',
        'international_remote', 'verification_discount', 'analytics', 'priority_support', 'is_active',
    ];

    protected $casts = [
        'featured_listings' => 'boolean',
        'candidate_search' => 'boolean',
        'advanced_search' => 'boolean',
        'international_remote' => 'boolean',
        'verification_discount' => 'boolean',
        'analytics' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(EmployerSubscription::class);
    }
}
