<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full-text search indexes for relevance-ranked search.
 *
 * FULLTEXT is a MySQL/InnoDB feature, so these are guarded to the MySQL driver.
 * On SQLite (the test database) the search layer falls back to LIKE, so no index
 * is needed there. The column lists must exactly match the MATCH(...) calls in
 * JobService / ProfessionalService for the index to be used.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->fullText(['title', 'description', 'requirements'], 'jobs_fulltext');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->fullText(['headline', 'bio', 'current_job_title', 'desired_job_title'], 'seekers_fulltext');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $table->dropFullText('jobs_fulltext');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropFullText('seekers_fulltext');
        });
    }
};
