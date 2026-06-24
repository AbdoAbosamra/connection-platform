<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_seeker_profile_id')->constrained()->cascadeOnDelete();
            $table->text('cover_letter')->nullable();
            $table->string('resume_snapshot')->nullable();  // copy of resume at time of apply
            $table->unsignedInteger('expected_salary')->nullable();
            $table->string('currency')->default('USD');
            $table->enum('status', [
                'submitted',
                'viewed',
                'shortlisted',
                'interview_scheduled',
                'offer_extended',
                'hired',
                'rejected',
                'withdrawn',
            ])->default('submitted');
            $table->text('employer_notes')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'job_seeker_profile_id']);
            $table->index(['status', 'created_at']);
            $table->index('job_seeker_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
