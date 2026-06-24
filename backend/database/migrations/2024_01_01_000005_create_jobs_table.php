<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('category');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance', 'internship'])->default('full_time');
            $table->enum('location_type', ['remote', 'hybrid', 'on_site'])->default('remote');
            $table->string('location_city')->nullable();
            $table->string('location_state')->nullable();
            $table->string('location_country')->default('US');
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('currency')->default('USD');
            $table->enum('salary_period', ['hourly', 'monthly', 'annual'])->default('annual');
            $table->boolean('salary_visible')->default(true);
            $table->enum('experience_level', ['entry', 'mid', 'senior', 'lead', 'executive'])->default('mid');
            $table->boolean('visa_sponsorship')->default(false);
            $table->boolean('open_to_international')->default(true);
            $table->enum('status', ['draft', 'active', 'paused', 'closed', 'expired'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('applications_count')->default(0);
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['location_type', 'experience_level']);
            $table->index('category');
            $table->index('expires_at');
        });

        // Job <-> skill pivot
        Schema::create('job_skills', function (Blueprint $table) {
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_required')->default(true);
            $table->primary(['job_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_skills');
        Schema::dropIfExists('jobs');
    }
};
