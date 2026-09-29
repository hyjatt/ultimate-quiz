<?php

namespace App\Console\Commands;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('admin:create')]
#[Description('Create a verified administrator account')]
class CreateAdministrator extends Command
{
    use PasswordValidationRules;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->ask('Username');
        $email = $this->ask('Email address');
        $role = $this->choice('Role', [UserRole::Admin->value, UserRole::SuperAdmin->value], UserRole::SuperAdmin->value);
        $password = $this->secret('Password');
        $confirmation = $this->secret('Confirm password');

        $validated = Validator::make([
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,superadmin'],
            'password' => $this->passwordRules(),
        ])->validate();

        User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
            'email_verified_at' => now(),
        ]);

        $this->components->info("{$validated['role']} account created for {$validated['email']}.");

        return self::SUCCESS;
    }
}
