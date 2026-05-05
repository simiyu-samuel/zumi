<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevAuthSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['email' => 'admin@zumi.app'],
            [
                'name' => 'Zumi Admin',
                'username' => 'zumi_admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'status' => UserStatus::Active,
                'onboarding_completed' => true,
                'verified_at' => now(),
            ]
        );

        // 2. Creator (Pro) User
        User::updateOrCreate(
            ['email' => 'creator@zumi.app'],
            [
                'name' => 'Jane Creator',
                'username' => 'jane_creator',
                'password' => Hash::make('password'),
                'role' => UserRole::Pro,
                'status' => UserStatus::Active,
                'onboarding_completed' => true,
                'verified_at' => now(),
                'bio' => 'Professional content creator on Zumi.',
            ]
        );

        // 3. Regular New User (to test onboarding)
        User::updateOrCreate(
            ['email' => 'user@zumi.app'],
            [
                'name' => 'John User',
                'username' => 'john_user',
                'password' => Hash::make('password'),
                'role' => UserRole::User,
                'status' => UserStatus::Active,
                'onboarding_completed' => false,
            ]
        );

        $this->command->info('Test users created: admin@zumi.app, creator@zumi.app, user@zumi.app (Password: password)');
    }
}
