<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlaceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [];
        for ($i = 1; $i <= 20; $i++) {
            $data[] = [
                'place_id' => ($i % 10) + 1, // Asume que hay al menos 10 lugares
                'category_id' => ($i % 20) + 1, // 20 categorías
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('place_category')->insert($data);
    }
}
