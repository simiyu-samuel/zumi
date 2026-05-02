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

        // Create Roles
        Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'studio', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'pro', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'creator', 'guard_name' => 'web']);
        Role::updateOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Define basic permissions (example)
        Permission::updateOrCreate(['name' => 'upload waves', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'manage drops', 'guard_name' => 'web']);
        Permission::updateOrCreate(['name' => 'view analytics', 'guard_name' => 'web']);

        // Assign permissions to creator
        $creatorRole = Role::findByName('creator');
        $creatorRole->givePermissionTo(['upload waves', 'view analytics']);

        // Assign everything to admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }
}
