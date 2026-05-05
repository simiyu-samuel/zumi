<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Enums\UserRole;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            // Basic User Features
            'upload waves',
            'join circles',
            'gift drops',
            'view wallet',
            
            // Premium Features
            'create circles',
            'publish skill drops',
            'host gated rooms',
            'host wave challenges',
            'view analytics',
            
            // Administrative Features
            'manage users',
            'manage drops',
            'view admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Create Roles and Assign Permissions
        
        // Admin
        $adminRole = Role::updateOrCreate(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        // Studio
        $studioRole = Role::updateOrCreate(['name' => UserRole::Studio->value, 'guard_name' => 'web']);
        $studioRole->syncPermissions([
            'upload waves',
            'join circles',
            'gift drops',
            'view wallet',
            'create circles',
            'publish skill drops',
            'host gated rooms',
            'host wave challenges',
            'view analytics',
        ]);

        // Pro
        $proRole = Role::updateOrCreate(['name' => UserRole::Pro->value, 'guard_name' => 'web']);
        $proRole->syncPermissions([
            'upload waves',
            'join circles',
            'gift drops',
            'view wallet',
            'create circles',
            'publish skill drops',
            'host gated rooms',
            'host wave challenges',
            'view analytics',
        ]);

        // User (Free)
        $userRole = Role::updateOrCreate(['name' => UserRole::User->value, 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'upload waves',
            'join circles',
            'gift drops',
            'view wallet',
        ]);

        $this->command->info('Roles and Permissions seeded successfully.');
    }
}
