<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Pro", "Enterprise"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_monthly');        // cents
            $table->unsignedInteger('price_annual');         // cents
            $table->unsignedSmallInteger('job_posts_limit'); // 0 = unlimited
            $table->boolean('featured_listings')->default(false);
            $table->boolean('candidate_search')->default(false);
            $table->boolean('analytics')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->enum('billing_period', ['monthly', 'annual'])->default('monthly');
            $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['employer_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
