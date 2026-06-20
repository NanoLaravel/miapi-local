<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoritesSeeder extends Seeder
{
    public function run(): void
    {
        // Ejemplo: 5 usuarios, cada uno con 2 lugares favoritos
        for ($userId = 1; $userId <= 5; $userId++) {
            DB::table('favorites')->insert([
                [
                    'user_id' => $userId,
                    'place_id' => (($userId - 1) % 5) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'user_id' => $userId,
                    'place_id' => (($userId) % 5) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
