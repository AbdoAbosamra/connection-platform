<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Create a new platform administrator, or promote an existing user to admin.
 *
 * The public registration endpoint only allows the `employer` and `job_seeker`
 * roles, so admin accounts can ONLY be minted from the server terminal. This is
 * the single, auditable entry point the site owner uses to grant admin access.
 *
 *   php artisan admin:create
 *   php artisan admin:create --name="Site Owner" --email=owner@site.com
 *   php artisan admin:create --email=owner@site.com --password=secret123 --force
 *
 * If --password is omitted a strong random password is generated and printed
 * once. If the email already belongs to a user, that user is promoted to admin
 * (and reactivated) instead of creating a duplicate.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name=    : Full name of the admin}
        {--email=   : Email address (login)}
        {--password= : Password (min 8 chars; generated if omitted)}
        {--force    : Skip the confirmation prompt}';

    protected $description = 'Create a new platform administrator (or promote an existing user to admin).';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Full name', 'Site Owner');
        $email = $this->option('email') ?: $this->ask('Email address');

        // Generate a strong password if none was supplied, so non-interactive
        // runs still produce a secure account rather than a weak default.
        $generated = false;
        $password = $this->option('password');
        if (!$password) {
            $password = Str::password(16);
            $generated = true;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $err) {
                $this->error($err);
            }

            return self::FAILURE;
        }

        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->isAdmin()) {
                $this->warn("User <{$email}> is already an admin (#{$existing->id}).");

                return self::SUCCESS;
            }

            $this->warn("A {$existing->role} account already exists for <{$email}> (#{$existing->id}).");

            if (!$this->option('force') && !$this->confirm('Promote this existing user to admin?', true)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }

            $existing->update([
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make($password),
            ]);

            $this->info("✓ User #{$existing->id} <{$email}> promoted to admin.");
            if ($generated) {
                $this->newLine();
                $this->line('  Generated password: <fg=yellow>'.$password.'</>');
                $this->line('  <fg=gray>Store it now — it will not be shown again.</>');
            }

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Create new admin <{$email}>?", true)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info("✓ Admin created: #{$admin->id} <{$email}>");
        if ($generated) {
            $this->newLine();
            $this->line('  Generated password: <fg=yellow>'.$password.'</>');
            $this->line('  <fg=gray>Store it now — it will not be shown again.</>');
        }

        return self::SUCCESS;
    }
}
