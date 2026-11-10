<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {--name=} {--email=}';

    protected $description = 'Create an admin account (admins cannot be created from the register page)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = strtolower((string) ($this->option('email') ?: $this->ask('Email')));
        $password = $this->secret('Password (at least 8 characters)');
        $confirm = $this->secret('Confirm the password');

        if ($password !== $confirm) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $admin = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $admin->role = 'admin'; // set here, never from a form
        $admin->save();

        $this->info("Admin account created for {$email}. Log in at /login.");

        return self::SUCCESS;
    }
}