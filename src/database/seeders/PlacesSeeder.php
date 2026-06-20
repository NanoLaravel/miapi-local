<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlacesSeeder extends Seeder
{
    public function run(): void
    {
        $types = ['restaurant', 'hotel', 'recreation', 'other'];
        $places = [];
        for ($i = 1; $i <= 20; $i++) {
            $places[] = [
                'name' => 'Lugar ' . $i,
                'description' => 'Descripción del lugar ' . $i,
                'address' => 'Dirección ' . $i,
                'latitude' => 4.0 + ($i * 0.01),
                'longitude' => -74.0 - ($i * 0.01),
                'phone' => '30000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'website' => 'https://lugar' . $i . '.com',
                'type' => $types[$i % 4],
                'rating' => rand(35, 50) / 10,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('places')->insert($places);
    }
}
