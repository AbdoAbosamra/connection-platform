<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('company_slug')->unique();
            $table->text('description')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();     // e.g. "1-10", "11-50"
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('headquarters_city')->nullable();
            $table->string('headquarters_state')->nullable();
            $table->string('headquarters_country')->default('US');
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->year('founded_year')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->enum('subscription_tier', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->integer('job_post_credits')->default(3);
            $table->timestamps();

            $table->index('company_slug');
            $table->index(['is_verified', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employer_profiles');
    }
};
