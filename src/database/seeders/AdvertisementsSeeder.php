<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Advertisement;
use App\Models\Place;
use App\Models\User;
use App\Models\Image;
use Carbon\Carbon;

class AdvertisementsSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuario admin o el primero disponible
        $user = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first() ?? User::first();

        if (!$user) {
            $this->command->warn('No hay usuarios disponibles. Ejecuta UsersSeeder primero.');
            return;
        }

        $places = Place::all();
        $now = Carbon::now();

        // Imágenes placeholder para los anuncios (se guardarán en storage/app/public/publicidad)
        $adImages = [
            'publicidad/hotel-paradise.jpg',
            'publicidad/restaurante-sabor.jpg',
            'publicidad/tour-aventura.jpg',
            'publicidad/spa-relax.jpg',
            'publicidad/festival-verano.jpg',
            'publicidad/cafe-valle.jpg',
            'publicidad/bicicletas.jpg',
            'publicidad/artesanias.jpg',
            'publicidad/surf-school.jpg',
            'publicidad/transporte.jpg',
        ];

        $advertisements = [
            [
                'title' => 'Hotel Paradise - 50% OFF',
                'description' => 'Reserva ahora y obtén un 50% de descuento en tu estadía. Incluye desayuno buffet y acceso a la piscina.',
                'link_url' => 'https://hotel-paradise.com/promo',
                'type' => 'banner',
                'position' => 'home',
                'start_date' => $now->copy()->subDays(5),
                'end_date' => $now->copy()->addDays(30),
                'priority' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Restaurante El Sabor',
                'description' => 'La mejor cocina local con ingredientes frescos del mercado. Reserva tu mesa hoy.',
                'link_url' => 'https://el-sabor-restaurante.com',
                'type' => 'banner',
                'position' => 'places',
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addDays(45),
                'priority' => 90,
                'is_active' => true,
            ],
            [
                'title' => 'Tour Aventura Extrema',
                'description' => '¡Vive la adrenalina! Canopy, rafting y rappel en un solo paquete. Precios especiales para grupos.',
                'link_url' => 'https://aventura-extrema.com',
                'type' => 'banner',
                'position' => 'events',
                'start_date' => $now->copy()->subDays(2),
                'end_date' => $now->copy()->addDays(60),
                'priority' => 85,
                'is_active' => true,
            ],
            [
                'title' => 'Spa Relaxación Total',
                'description' => 'Masajes, tratamientos faciales y corporales. Tu momento de paz te espera.',
                'link_url' => 'https://spa-relaxacion.com',
                'type' => 'sidebar',
                'position' => 'all',
                'start_date' => $now->copy()->subDays(15),
                'end_date' => $now->copy()->addDays(20),
                'priority' => 75,
                'is_active' => true,
            ],
            [
                'title' => 'Festival de Verano 2026',
                'description' => 'No te pierdas el evento del año. Música, comida y diversión para toda la familia.',
                'link_url' => 'https://festival-verano.com',
                'type' => 'popup',
                'position' => 'home',
                'start_date' => $now->copy()->subDays(1),
                'end_date' => $now->copy()->addDays(15),
                'priority' => 95,
                'is_active' => true,
            ],
            [
                'title' => 'Café Premium del Valle',
                'description' => 'Descubre el auténtico sabor del café colombiano. Envíos a todo el país.',
                'link_url' => 'https://cafe-valle.com',
                'type' => 'inline',
                'position' => 'places',
                'start_date' => $now->copy()->subDays(20),
                'end_date' => $now->copy()->addDays(40),
                'priority' => 70,
                'is_active' => true,
            ],
            [
                'title' => 'Alquiler de Bicicletas',
                'description' => 'Explora la ciudad sobre ruedas. Bicicletas eléctricas y tradicionales disponibles.',
                'link_url' => 'https://bike-rental.com',
                'type' => 'banner',
                'position' => 'all',
                'start_date' => $now->copy()->subDays(7),
                'end_date' => $now->copy()->addDays(90),
                'priority' => 60,
                'is_active' => true,
            ],
            [
                'title' => 'Artesanías Locales',
                'description' => 'Lleva un pedazo de nuestra cultura contigo. Productos hechos a mano por artesanos locales.',
                'link_url' => 'https://artesanias-locales.com',
                'type' => 'sidebar',
                'position' => 'events',
                'start_date' => $now->copy()->subDays(3),
                'end_date' => $now->copy()->addDays(25),
                'priority' => 65,
                'is_active' => true,
            ],
            [
                'title' => 'Clases de Surf',
                'description' => 'Aprende a surfear con los mejores instructores. Clases para todos los niveles.',
                'link_url' => 'https://surf-school.com',
                'type' => 'banner',
                'position' => 'places',
                'start_date' => $now->copy()->subDays(12),
                'end_date' => $now->copy()->addDays(50),
                'priority' => 80,
                'is_active' => true,
            ],
            [
                'title' => 'Transporte Privado',
                'description' => 'Viaja cómodo y seguro. Servicio de transporte privado a todos los destinos turísticos.',
                'link_url' => 'https://transporte-privado.com',
                'type' => 'inline',
                'position' => 'all',
                'start_date' => $now->copy()->subDays(8),
                'end_date' => $now->copy()->addDays(120),
                'priority' => 55,
                'is_active' => true,
            ],
        ];

        foreach ($advertisements as $index => $adData) {
            $adData['user_id'] = $user->id;
            $adData['image_path'] = $adImages[$index];
            $adData['clicks_count'] = rand(10, 500);
            $adData['views_count'] = rand(500, 5000);
            
            // Asignar un lugar aleatorio si hay disponibles
            if ($places->count() > 0 && rand(0, 1)) {
                $adData['place_id'] = $places->random()->id;
            }
            
            $advertisement = Advertisement::create($adData);
            
            // Crear la imagen en la tabla images con relación polimórfica
            Image::create([
                'imageable_type' => Advertisement::class,
                'imageable_id' => $advertisement->id,
                'path' => $adImages[$index],
                'description' => 'Imagen principal de ' . $adData['title'],
            ]);
        }

        $this->command->info('10 anuncios de prueba creados exitosamente con sus imágenes.');
    }
}
