<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PricesSeeder extends Seeder
{
    public function run(): void
    {
        // Ejemplo: 5 lugares, cada uno con 2 precios (por noche y por semana)
        $currencies = ['USD', 'EUR', 'COP'];
        for ($placeId = 1; $placeId <= 5; $placeId++) {
            DB::table('prices')->insert([
                [
                    'place_id' => $placeId,
                    'type' => 'night',
                    'value' => rand(30, 200),
                    'currency' => $currencies[array_rand($currencies)],
                    'description' => 'Precio por noche',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'place_id' => $placeId,
                    'type' => 'week',
                    'value' => rand(200, 1200),
                    'currency' => $currencies[array_rand($currencies)],
                    'description' => 'Precio por semana',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
