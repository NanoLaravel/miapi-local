<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagesSeeder extends Seeder
{
    public function run(): void
    {
        $images = [];
        for ($i = 1; $i <= 20; $i++) {
            $images[] = [
                'place_id' => $i, // Asociar a los primeros 20 lugares
                'path' => 'lugares/hotel_' . $i . '.jpg',
                'description' => 'Imagen de hotel ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('images')->insert($images);
    }
}
