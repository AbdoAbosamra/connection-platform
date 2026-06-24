<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserErasureService;
use Illuminate\Console\Command;

/**
 * CLI entry point for GDPR erasure, e.g. for a data-protection officer running
 * a manual "Right to be Forgotten" request from the server terminal.
 *
 *   php artisan gdpr:forget 123
 *   php artisan gdpr:forget 123 --force   (skip the confirmation prompt)
 */
class ForgetUser extends Command
{
    protected $signature = 'gdpr:forget {user : The id of the user to erase} {--force : Skip the confirmation prompt}';

    protected $description = 'Permanently anonymise a user\'s personal data (GDPR Right to be Forgotten).';

    public function handle(UserErasureService $erasure): int
    {
        $user = User::find($this->argument('user'));

        if (!$user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if ($user->role === 'admin') {
            $this->error('Refusing to erase an admin account.');

            return self::FAILURE;
        }

        $this->warn("This will IRREVERSIBLY anonymise user #{$user->id} <{$user->email}> ({$user->role}).");

        if (!$this->option('force') && !$this->confirm('Proceed?')) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        $erasure->forget($user);

        $this->info("User #{$user->id} has been erased.");

        return self::SUCCESS;
    }
}
