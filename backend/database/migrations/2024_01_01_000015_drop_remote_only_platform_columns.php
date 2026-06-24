<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RemoteArena is a remote-only platform.
 *
 * Drop columns that are now meaningless given this constraint:
 *   jobs:
 *     - visa_sponsorship        (was a filter; remote-only makes this irrelevant at platform level)
 *     - open_to_international   (all remote positions are inherently international)
 *   job_seeker_profiles:
 *     - open_to_remote          (all seekers on this platform are open to remote by definition)
 *     - willing_to_relocate     (platform is remote-only; relocation is irrelevant)
 *
 * location_type column is retained on jobs (always 'remote') for data integrity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['visa_sponsorship', 'open_to_international']);
        });

        // The composite index from migration 000003 references open_to_remote.
        // SQLite refuses to drop a column that is still part of an index, so the
        // index must be removed first. (MySQL tolerates either order, but doing
        // it explicitly keeps the migration portable across both engines.)
        if (Schema::hasIndex('job_seeker_profiles', 'job_seeker_profiles_open_to_remote_experience_level_index')) {
            Schema::table('job_seeker_profiles', function (Blueprint $table) {
                $table->dropIndex('job_seeker_profiles_open_to_remote_experience_level_index');
            });
        }

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn(['open_to_remote', 'willing_to_relocate']);
        });

        // Re-create an equivalent index on the retained experience_level column so
        // public seeker listing queries stay fast after the column drop.
        if (!Schema::hasIndex('job_seeker_profiles', 'seeker_experience_level_index')) {
            Schema::table('job_seeker_profiles', function (Blueprint $table) {
                $table->index('experience_level', 'seeker_experience_level_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->boolean('visa_sponsorship')->default(false)->after('experience_level');
            $table->boolean('open_to_international')->default(true)->after('visa_sponsorship');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->boolean('open_to_remote')->default(true)->after('nationality');
            $table->boolean('willing_to_relocate')->default(false)->after('open_to_remote');
        });
    }
};
