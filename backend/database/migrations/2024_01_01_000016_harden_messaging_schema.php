<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Messaging schema hardening:
 *  1. Adds proper FK on conversations.last_message_id (nullOnDelete to avoid orphan refs)
 *  2. Adds read_at index on messages for fast unread-count queries
 *  3. Adds sender_id index on messages for fast "sent by me" lookups
 *  4. Adds composite index on conversations for fast user-scoped list queries
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add FK for last_message_id (nullify when message is deleted — no orphan pointer).
        //    SQLite cannot add a foreign-key constraint to an existing table via ALTER,
        //    so we guard the FK on the driver. The integrity guarantee matters in
        //    production (MySQL); the test database (SQLite) relies on application-level
        //    nulling instead. Indexes are portable and added on every driver.
        $isMysql = Schema::getConnection()->getDriverName() === 'mysql';

        Schema::table('conversations', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->foreign('last_message_id')
                    ->references('id')
                    ->on('messages')
                    ->nullOnDelete();
            }

            // Fast lookup for employer inbox
            $table->index(['employer_profile_id', 'last_message_at'], 'conv_employer_recent');
            // Fast lookup for job-seeker inbox
            $table->index(['job_seeker_profile_id', 'last_message_at'], 'conv_seeker_recent');
        });

        // 2. Performance indexes on messages
        Schema::table('messages', function (Blueprint $table) {
            // Needed for unread-count subqueries: WHERE conversation_id=? AND sender_id!=? AND read_at IS NULL
            $table->index(['conversation_id', 'sender_id', 'read_at'], 'msg_unread_lookup');
        });
    }

    public function down(): void
    {
        $isMysql = Schema::getConnection()->getDriverName() === 'mysql';

        Schema::table('conversations', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->dropForeign(['last_message_id']);
            }
            $table->dropIndex('conv_employer_recent');
            $table->dropIndex('conv_seeker_recent');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_unread_lookup');
        });
    }
};
