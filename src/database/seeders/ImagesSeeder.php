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
                'place_id' => ($i % 10) + 1, // Asume que hay al menos 10 lugares
                'url' => 'https://picsum.photos/seed/' . $i . '/400/300',
                'description' => 'Imagen de ejemplo ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('images')->insert($images);
    }
}
