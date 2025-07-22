<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $total = $users->count();
        $admins = $users->take(2); // Primeros 2 como admin
        $editors = $users->skip(2)->take(5); // Siguientes 5 como editor
        $rest = $users->skip(7); // El resto como user

        foreach ($admins as $user) {
            $user->assignRole('admin');
        }
        foreach ($editors as $user) {
            $user->assignRole('editor');
        }
        foreach ($rest as $user) {
            $user->assignRole('user');
        }
    }
}
