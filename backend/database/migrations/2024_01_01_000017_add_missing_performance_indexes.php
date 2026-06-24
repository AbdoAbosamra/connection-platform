<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add indexes identified during the production-readiness audit.
 *
 * job_applications.job_id (alone)
 *   — Employer queries "all applications for my job" filter on job_id without
 *     the composite index covering it efficiently.
 *
 * job_applications.(job_id, status)
 *   — Common filter: applications for a job filtered by status.
 *
 * job_seeker_profiles.(profile_complete, is_featured)
 *   — Public ProfessionalController::index() always filters profile_complete=true
 *     and optionally filters is_featured — a full table scan without this index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            // Drop the existing composite unique first if it covers job_id so
            // we don't create a redundant prefix index.
            // The unique index is on [job_id, job_seeker_profile_id] — that already
            // serves as an index for job_id prefix lookups on MySQL InnoDB, so we
            // only add the (job_id, status) composite for filtered queries.
            if (!$this->indexExists('job_applications', 'app_job_status')) {
                $table->index(['job_id', 'status'], 'app_job_status');
            }
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            if (!$this->indexExists('job_seeker_profiles', 'seeker_public_list')) {
                $table->index(['profile_complete', 'is_featured'], 'seeker_public_list');
            }
        });
    }

    public function down(): void
    {
        if ($this->indexExists('job_applications', 'app_job_status')) {
            Schema::table('job_applications', function (Blueprint $table) {
                $table->dropIndex('app_job_status');
            });
        }

        if ($this->indexExists('job_seeker_profiles', 'seeker_public_list')) {
            Schema::table('job_seeker_profiles', function (Blueprint $table) {
                $table->dropIndex('seeker_public_list');
            });
        }
    }

    /**
     * Driver-agnostic index existence check. Uses Laravel's schema introspection
     * (Schema::hasIndex) so the migration runs identically on MySQL (production)
     * and SQLite (test suite). The previous implementation used a MySQL-only
     * `SHOW INDEX` query that broke under the in-memory SQLite test database.
     */
    private function indexExists(string $table, string $index): bool
    {
        return Schema::hasIndex($table, $index);
    }
};
