<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'employer', 'job_seeker'])->default('job_seeker');
            $table->boolean('is_active')->default(true);
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'is_active']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // sessions table is created in 000014_create_sessions_table.php
        // to keep each migration responsible for exactly one table.
    }

    public function down(): void
    {
        // sessions is dropped in its own migration file (000014)
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
