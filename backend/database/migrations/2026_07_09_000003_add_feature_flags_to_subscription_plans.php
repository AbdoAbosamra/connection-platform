<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extra per-plan feature flags for the Free / Starter / Growth / Scale model.
 * (candidate_search, featured_listings, analytics, priority_support already exist.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('advanced_search')->default(false)->after('candidate_search');
            $table->boolean('international_remote')->default(false)->after('advanced_search');
            $table->boolean('verification_discount')->default(false)->after('international_remote');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['advanced_search', 'international_remote', 'verification_discount']);
        });
    }
};
