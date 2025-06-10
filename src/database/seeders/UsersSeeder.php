<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $users[] = [
                'name' => 'Usuario ' . $i,
                'email' => 'usuario' . $i . '@ejemplo.com',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('users')->insert($users);
    }
}
