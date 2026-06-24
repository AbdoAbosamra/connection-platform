<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seeker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->string('resume')->nullable();           // stored file path
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_country')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('open_to_remote')->default(true);
            $table->boolean('willing_to_relocate')->default(false);
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'lead', 'executive'])->default('mid');
            $table->integer('years_of_experience')->default(0);
            $table->string('current_job_title')->nullable();
            $table->string('desired_job_title')->nullable();
            $table->unsignedInteger('desired_salary_min')->nullable();
            $table->unsignedInteger('desired_salary_max')->nullable();
            $table->string('currency')->default('USD');
            $table->enum('availability', ['immediately', 'two_weeks', 'one_month', 'negotiable'])->default('negotiable');
            $table->boolean('profile_complete')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index(['open_to_remote', 'experience_level']);
            $table->index('current_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_profiles');
    }
};
