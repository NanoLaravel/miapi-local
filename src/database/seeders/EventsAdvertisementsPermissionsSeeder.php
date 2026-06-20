<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class EventsAdvertisementsPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos para Eventos
        $eventPermissions = [
            'view_any_event',
            'view_event',
            'create_event',
            'update_event',
            'delete_event',
            'restore_event',
            'force_delete_event',
        ];

        foreach ($eventPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Crear permisos para Publicidad
        $adPermissions = [
            'view_any_advertisement',
            'view_advertisement',
            'create_advertisement',
            'update_advertisement',
            'delete_advertisement',
            'restore_advertisement',
            'force_delete_advertisement',
        ];

        foreach ($adPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Asignar permisos a roles existentes
        $editorRole = Role::where('name', 'editor')->first();
        if ($editorRole) {
            $editorRole->givePermissionTo([
                'view_any_event',
                'view_event',
                'create_event',
                'update_event',
                'view_any_advertisement',
                'view_advertisement',
                'create_advertisement',
                'update_advertisement',
            ]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_merge($eventPermissions, $adPermissions));
        }

        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $userRole->givePermissionTo([
                'view_any_event',
                'view_event',
                'view_any_advertisement',
                'view_advertisement',
            ]);
        }
    }
}
