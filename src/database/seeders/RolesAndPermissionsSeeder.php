<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'view_basic_filters',
            'view_advanced_filters',
            'create_content',
            'edit_content',
            'delete_content',
            'manage_users',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Crear roles
        $roles = [
            'guest' => ['view_basic_filters'],
            'user' => ['view_basic_filters', 'view_advanced_filters'],
            'editor' => ['view_basic_filters', 'view_advanced_filters', 'create_content', 'edit_content'],
            'admin' => $permissions,
        ];

        foreach ($roles as $role => $perms) {
            $roleObj = Role::firstOrCreate(['name' => $role]);
            $roleObj->syncPermissions($perms);
        }
    }
}
