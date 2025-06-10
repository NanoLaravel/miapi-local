<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagesSeeder extends Seeder
{
    public function run(): void
    {
        $images = [];
        for ($i = 1; $i <= 10; $i++) {
            $images[] = [
                'place_id' => $i, // Asociar a los primeros 10 lugares
                'path' => 'lugares/ejemplo' . $i . '.jpg',
                'description' => 'Imagen de ejemplo ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('images')->insert($images);
    }
}
