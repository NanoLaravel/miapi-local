<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Place;
use App\Models\User;
use App\Models\Image;
use Carbon\Carbon;

class EventsSeeder extends Seeder
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

        // Imágenes placeholder para eventos (se guardarán en storage/app/public/eventos)
        $eventImages = [
            'eventos/festival-musica.jpg',
            'eventos/feria-gastronomica.jpg',
            'eventos/maraton.jpg',
            'eventos/expo-arte.jpg',
            'eventos/concierto-rock.jpg',
            'eventos/taller-fotografia.jpg',
            'eventos/festival-cafe.jpg',
            'eventos/cine-estrellas.jpg',
            'eventos/torneo-volleyball.jpg',
            'eventos/cuentacuentos.jpg',
        ];

        $events = [
            [
                'title' => 'Festival de Música Tropical',
                'description' => 'Disfruta de una noche llena de ritmos caribeños con los mejores artistas locales e internacionales. Ven a bailar al son de la salsa, merengue y cumbia.',
                'start_date' => $now->copy()->addDays(5)->setHour(19)->setMinute(0),
                'end_date' => $now->copy()->addDays(5)->setHour(23)->setMinute(59),
                'location' => 'Plaza Principal',
                'latitude' => 4.5709,
                'longitude' => -74.2973,
                'price' => 25000.00,
                'is_featured' => true,
                'contact_phone' => '+57 300 123 4567',
                'contact_email' => 'festival@eventos.com',
                'website' => 'https://festival-tropical.com',
            ],
            [
                'title' => 'Feria Gastronómica Local',
                'description' => 'Más de 30 expositores con lo mejor de la cocina regional. Degustaciones, shows en vivo y talleres de cocina.',
                'start_date' => $now->copy()->addDays(10)->setHour(10)->setMinute(0),
                'end_date' => $now->copy()->addDays(12)->setHour(20)->setMinute(0),
                'location' => 'Centro de Convenciones',
                'latitude' => 4.5720,
                'longitude' => -74.2950,
                'price' => 15000.00,
                'is_featured' => true,
                'contact_phone' => '+57 301 234 5678',
                'contact_email' => 'feria@gastronomia.com',
            ],
            [
                'title' => 'Maratón de la Ciudad',
                'description' => 'Carrera atlética de 10km y 21km por las principales avenidas de la ciudad. Premios para las primeras 3 posiciones de cada categoría.',
                'start_date' => $now->copy()->addDays(15)->setHour(6)->setMinute(0),
                'end_date' => $now->copy()->addDays(15)->setHour(12)->setMinute(0),
                'location' => 'Parque Central',
                'latitude' => 4.5715,
                'longitude' => -74.2960,
                'price' => 35000.00,
                'is_featured' => false,
                'contact_phone' => '+57 302 345 6789',
                'contact_email' => 'maraton@deportes.com',
                'website' => 'https://maraton-ciudad.com',
            ],
            [
                'title' => 'Expo Arte Contemporáneo',
                'description' => 'Exhibición de obras de artistas locales y nacionales. Pinturas, esculturas e instalaciones interactivas.',
                'start_date' => $now->copy()->addDays(3)->setHour(9)->setMinute(0),
                'end_date' => $now->copy()->addDays(30)->setHour(18)->setMinute(0),
                'location' => 'Museo Municipal',
                'latitude' => 4.5730,
                'longitude' => -74.2940,
                'price' => 10000.00,
                'is_featured' => true,
                'contact_phone' => '+57 303 456 7890',
                'contact_email' => 'expo@arte.com',
            ],
            [
                'title' => 'Concierto de Rock Clásico',
                'description' => 'Tributo a las grandes bandas de los 70s y 80s. Una noche nostálgica con los mejores clásicos del rock.',
                'start_date' => $now->copy()->addDays(20)->setHour(20)->setMinute(0),
                'end_date' => $now->copy()->addDays(20)->setHour(23)->setMinute(59),
                'location' => 'Teatro al Aire Libre',
                'latitude' => 4.5740,
                'longitude' => -74.2930,
                'price' => 45000.00,
                'is_featured' => false,
                'contact_phone' => '+57 304 567 8901',
                'contact_email' => 'rock@conciertos.com',
                'website' => 'https://rock-clasico.com',
            ],
            [
                'title' => 'Taller de Fotografía Paisajística',
                'description' => 'Aprende técnicas profesionales de fotografía de paisajes. Incluye salida práctica al atardecer.',
                'start_date' => $now->copy()->addDays(7)->setHour(14)->setMinute(0),
                'end_date' => $now->copy()->addDays(7)->setHour(19)->setMinute(0),
                'location' => 'Mirador Cerro Verde',
                'latitude' => 4.5750,
                'longitude' => -74.2920,
                'price' => 80000.00,
                'is_featured' => false,
                'contact_phone' => '+57 305 678 9012',
                'contact_email' => 'taller@fotografia.com',
            ],
            [
                'title' => 'Festival del Café',
                'description' => 'Celebra la cultura cafetera con catas, baristas en acción, música en vivo y el mejor café de la región.',
                'start_date' => $now->copy()->addDays(25)->setHour(8)->setMinute(0),
                'end_date' => $now->copy()->addDays(27)->setHour(18)->setMinute(0),
                'location' => 'Hacienda Cafetera El Paraíso',
                'latitude' => 4.5760,
                'longitude' => -74.2910,
                'price' => 20000.00,
                'is_featured' => true,
                'contact_phone' => '+57 306 789 0123',
                'contact_email' => 'festival@cafe.com',
                'website' => 'https://festival-cafe.com',
            ],
            [
                'title' => 'Cine bajo las Estrellas',
                'description' => 'Proyección de películas clásicas al aire libre. Trae tu manta y disfruta de una noche mágica.',
                'start_date' => $now->copy()->addDays(12)->setHour(19)->setMinute(30),
                'end_date' => $now->copy()->addDays(12)->setHour(23)->setMinute(0),
                'location' => 'Jardín Botánico',
                'latitude' => 4.5770,
                'longitude' => -74.2900,
                'price' => 0.00,
                'is_featured' => false,
                'contact_phone' => '+57 307 890 1234',
                'contact_email' => 'cine@estrellas.com',
            ],
            [
                'title' => 'Torneo de Beach Volleyball',
                'description' => 'Competencia de voleibol playa con equipos de toda la región. Inscripciones abiertas hasta el 15 del mes.',
                'start_date' => $now->copy()->addDays(18)->setHour(8)->setMinute(0),
                'end_date' => $now->copy()->addDays(19)->setHour(18)->setMinute(0),
                'location' => 'Club de Playa Costa Azul',
                'latitude' => 4.5780,
                'longitude' => -74.2890,
                'price' => 50000.00,
                'is_featured' => false,
                'contact_phone' => '+57 308 901 2345',
                'contact_email' => 'torneo@volleyball.com',
            ],
            [
                'title' => 'Noche de Cuentacuentos',
                'description' => 'Una velada mágica con los mejores narradores de la región. Ideal para toda la familia.',
                'start_date' => $now->copy()->addDays(8)->setHour(18)->setMinute(0),
                'end_date' => $now->copy()->addDays(8)->setHour(21)->setMinute(0),
                'location' => 'Biblioteca Pública Municipal',
                'latitude' => 4.5790,
                'longitude' => -74.2880,
                'price' => 5000.00,
                'is_featured' => false,
                'contact_phone' => '+57 309 012 3456',
                'contact_email' => 'cuentos@biblioteca.com',
            ],
        ];

        foreach ($events as $index => $eventData) {
            $eventData['user_id'] = $user->id;
            $eventData['is_active'] = true;
            $eventData['image_path'] = $eventImages[$index];
            
            // Asignar un lugar aleatorio si hay disponibles
            if ($places->count() > 0 && rand(0, 1)) {
                $eventData['place_id'] = $places->random()->id;
            }
            
            $event = Event::create($eventData);
            
            // Crear la imagen en la tabla images con relación polimórfica
            Image::create([
                'imageable_type' => Event::class,
                'imageable_id' => $event->id,
                'path' => $eventImages[$index],
                'description' => 'Imagen principal de ' . $eventData['title'],
            ]);
        }

        $this->command->info('10 eventos de prueba creados exitosamente con sus imágenes.');
    }
}
