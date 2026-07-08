<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Hiring mode is the primary location concept now. Local by default;
            // remote and international remote are opt-in per job.
            //   local              → hire in a specific city / on-site or hybrid
            //   national_remote     → remote, but within the same country
            //   international_remote → remote, open to talent in other countries
            $table->string('hiring_mode')->default('local')->after('employment_type');

            // ── International Remote only ──────────────────────────────────────
            // These are populated exclusively when hiring_mode = international_remote,
            // and cleared otherwise. The product assumption is that international
            // candidates stay in their own country and work remotely — so there is
            // deliberately NO visa-sponsorship or work-authorization field here.
            $table->json('accepted_countries')->nullable()->after('location_country');
            $table->json('time_zones')->nullable()->after('accepted_countries');
            $table->json('languages')->nullable()->after('time_zones');
            $table->string('contract_type')->nullable()->after('languages');            // contractor | remote_employee
            $table->string('working_hours')->nullable()->after('contract_type');         // e.g. "4h overlap with EST"
            $table->string('currency_preference', 3)->nullable()->after('working_hours'); // ISO-4217, e.g. USD
            $table->string('payroll_preference')->nullable()->after('currency_preference');
            $table->text('collaboration_preferences')->nullable()->after('payroll_preference');

            $table->index('hiring_mode');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex(['hiring_mode']);
            $table->dropColumn([
                'hiring_mode', 'accepted_countries', 'time_zones', 'languages',
                'contract_type', 'working_hours', 'currency_preference',
                'payroll_preference', 'collaboration_preferences',
            ]);
        });
    }
};
