<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserWithRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        $roles = ['admin', 'editor', 'user', 'guest'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Crear usuarios y asignar roles
        $users = [
            ['name' => 'Admin Uno', 'email' => 'admin1@example.com', 'role' => 'admin'],
            ['name' => 'Admin Dos', 'email' => 'admin2@example.com', 'role' => 'admin'],
            ['name' => 'Editor Uno', 'email' => 'editor1@example.com', 'role' => 'editor'],
            ['name' => 'Editor Dos', 'email' => 'editor2@example.com', 'role' => 'editor'],
            ['name' => 'User Uno', 'email' => 'user1@example.com', 'role' => 'user'],
            ['name' => 'User Dos', 'email' => 'user2@example.com', 'role' => 'user'],
            ['name' => 'User Tres', 'email' => 'user3@example.com', 'role' => 'user'],
            ['name' => 'Guest Uno', 'email' => 'guest1@example.com', 'role' => 'guest'],
            ['name' => 'Guest Dos', 'email' => 'guest2@example.com', 'role' => 'guest'],
            ['name' => 'Guest Tres', 'email' => 'guest3@example.com', 'role' => 'guest'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password123'),
                ]
            );
            $user->assignRole($u['role']);
        }
    }
}
