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

        // Define permissions
        Permission::updateOrCreate(['name' => 'upload waves', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'manage drops', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'view analytics', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'create circles', 'guard_name' => 'web']);

        // Assign basic permissions to all users
        $userRole = Role::findByName('user');
        $userRole->givePermissionTo(['upload waves']);

        // Assign premium permissions to Pro and Studio
        $proRole = Role::findByName('pro');
        $proRole->givePermissionTo(['upload waves', 'view analytics', 'create circles']);

        $studioRole = Role::findByName('studio');
        $studioRole->givePermissionTo(['upload waves', 'view analytics', 'create circles']);

        // Assign everything to admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }
}
