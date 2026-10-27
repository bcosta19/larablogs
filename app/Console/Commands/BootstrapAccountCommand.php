<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BootstrapAccountCommand extends Command
{
    protected $signature = 'account:bootstrap {--email-file= : Private file containing the account email} {--password-file= : Private file containing the temporary password} {--reset : Reset an existing owner account}';

    protected $description = 'Create the private account with a temporary password that must be changed on first login';

    public function handle(): int
    {
        $emailFile = $this->option('email-file');
        $passwordFile = $this->option('password-file');
        $email = $emailFile ? @file_get_contents($emailFile) : $this->ask('Account email');
        $password = $passwordFile ? @file_get_contents($passwordFile) : $this->secret('Temporary password (at least 12 characters)');
        if (! is_string($email) || ! filter_var(trim($email), FILTER_VALIDATE_EMAIL)
            || ! is_string($password) || strlen(trim($password)) < 12) {
            $this->error('A valid email and a password of at least 12 characters are required.');

            return self::FAILURE;
        }
        $email = Str::lower(trim($email));
        if (User::query()->where('is_owner', true)->where('email', '!=', $email)->exists()) {
            $this->error('A private account already exists.');

            return self::FAILURE;
        }
        $user = User::query()->firstOrNew(['email' => $email]);
        if ($user->is_owner && ! $this->option('reset')) {
            $this->error('Use --reset explicitly to replace the existing account password.');

            return self::FAILURE;
        }
        $user->forceFill([
            'name' => $user->name ?: 'Larablogs',
            'password' => Hash::make(trim($password)),
            'is_owner' => true,
            'must_change_password' => true,
            'remember_token' => null,
        ])->save();
        $this->info('Private account ready. Password change is required at first login.');

        return self::SUCCESS;
    }
}
