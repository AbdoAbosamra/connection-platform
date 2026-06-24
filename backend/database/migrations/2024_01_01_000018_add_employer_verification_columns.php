<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employer verification trail.
 *
 * `is_verified` already exists (boolean gate). This adds the audit fields:
 *   - verified_at:          when verification completed
 *   - verification_method:  how — 'domain' (corporate email auto-approve),
 *                           'linkedin' (OAuth), or 'payment' (Stripe authorization)
 *   - linkedin_id:          stable LinkedIn subject id when verified via OAuth
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('is_verified');
            $table->string('verification_method')->nullable()->after('verified_at');
            $table->string('linkedin_id')->nullable()->after('verification_method');
        });
    }

    public function down(): void
    {
        Schema::table('employer_profiles', function (Blueprint $table) {
            $table->dropColumn(['verified_at', 'verification_method', 'linkedin_id']);
        });
    }
};
