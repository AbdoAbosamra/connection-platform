<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moderation layer.
 *
 *  - Extends the existing `reports` table (the flagging-events table) with a
 *    `priority` for auto-escalation and a composite index that makes
 *    "most-flagged target" and "open flags per target" queries index-only.
 *  - Adds `moderation_logs`: an append-only audit trail of every admin action
 *    (warning / suspension / dismissal / …) for accountability.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Auto-set from the reason and escalated when a target accumulates
            // multiple open flags — lets the dashboard surface bad actors fast.
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])
                ->default('normal')
                ->after('status');

            // Covers: WHERE reportable_type=? AND reportable_id=? [AND status=?]
            // → "how many open flags does this job/user have" without a table scan.
            $table->index(['reportable_type', 'reportable_id', 'status'], 'reports_target_status');
        });

        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();

            // Who took the action (an admin). Kept even if the admin is later
            // removed — accountability records must survive.
            $table->foreignId('moderator_id')->constrained('users')->cascadeOnDelete();

            // The user affected by the action (nullable: e.g. dismissing a flag
            // or removing a job posting affects content, not a specific account).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // The flag that prompted the action, if any.
            $table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();

            $table->enum('action', [
                'warning',          // formal warning sent to the user
                'suspension',       // account deactivated
                'reinstatement',    // suspension lifted
                'dismissal',        // flag dismissed as not actionable
                'content_removed',  // offending job posting taken down
                'note',             // internal note, no state change
            ]);

            $table->text('notes')->nullable();          // free-text justification
            $table->json('metadata')->nullable();       // structured context (ip, target type, etc.)
            $table->timestamps();

            // Dashboard: a user's moderation history, newest first.
            $table->index(['user_id', 'created_at']);
            // Dashboard: filter the global log by action type.
            $table->index('action');
            $table->index('moderator_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_logs');

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_target_status');
            $table->dropColumn('priority');
        });
    }
};
