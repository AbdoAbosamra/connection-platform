<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('category')->nullable();  // e.g. "Programming", "Design"
            $table->timestamps();
        });

        // Job seeker <-> skill pivot
        Schema::create('job_seeker_skills', function (Blueprint $table) {
            $table->foreignId('job_seeker_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->enum('proficiency', ['beginner', 'intermediate', 'advanced', 'expert'])->default('intermediate');
            $table->primary(['job_seeker_profile_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seeker_skills');
        Schema::dropIfExists('skills');
    }
};
