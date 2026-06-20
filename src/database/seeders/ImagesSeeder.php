<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Place;

class ImagesSeeder extends Seeder
{
    public function run(): void
    {
        $places = Place::all();
        
        foreach ($places as $place) {
            // Crear 1-3 imágenes por lugar
            $numImages = rand(1, 3);
            for ($i = 1; $i <= $numImages; $i++) {
                Image::create([
                    'imageable_type' => Place::class,
                    'imageable_id' => $place->id,
                    'path' => 'lugares/' . $place->id . '_' . $i . '.jpg',
                    'description' => 'Imagen ' . $i . ' de ' . $place->name,
                ]);
            }
        }

        $this->command->info('Imágenes de lugares creadas exitosamente.');
    }
}
