<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles (Aligned with UserRole Enum)
        Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'studio', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'pro', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Define Permissions
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

        // Assign permissions to 'user' (Free)
        $userRole = Role::findByName('user');
        $userRole->givePermissionTo([
            'upload waves',
            'join circles',
            'gift drops',
            'view wallet',
        ]);

        // Assign permissions to 'pro'
        $proRole = Role::findByName('pro');
        $proRole->givePermissionTo([
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

        // Assign permissions to 'studio' (Same as Pro, with potentially more in future)
        $studioRole = Role::findByName('studio');
        $studioRole->givePermissionTo([
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

        // Assign everything to admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }
}
