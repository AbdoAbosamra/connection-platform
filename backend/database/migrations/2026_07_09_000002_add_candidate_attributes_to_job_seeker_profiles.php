<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Candidate attributes that power the employer talent-search filters.
 *
 * Basic filters:    industry, education_level, remote_experience_years
 * Advanced (intl.): languages, time_zone, contract_preference,
 *                   certifications, has_security_clearance
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('desired_job_title');
            $table->string('education_level')->nullable()->after('industry');           // high_school | associate | bachelor | master | doctorate | other
            $table->unsignedInteger('remote_experience_years')->default(0)->after('years_of_experience');
            $table->json('languages')->nullable()->after('current_country');
            $table->string('time_zone')->nullable()->after('languages');
            $table->string('contract_preference')->nullable()->after('time_zone');       // contractor | employee | either
            $table->text('certifications')->nullable()->after('contract_preference');
            $table->boolean('has_security_clearance')->default(false)->after('certifications');

            $table->index('industry');
            $table->index('education_level');
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropIndex(['industry']);
            $table->dropIndex(['education_level']);
            $table->dropColumn([
                'industry', 'education_level', 'remote_experience_years', 'languages',
                'time_zone', 'contract_preference', 'certifications', 'has_security_clearance',
            ]);
        });
    }
};
