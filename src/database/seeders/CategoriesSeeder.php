<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Comida típica', 'description' => 'Platos tradicionales de la región', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Comida rápida', 'description' => 'Hamburguesas, pizzas, perros calientes', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hotel familiar', 'description' => 'Hoteles para toda la familia', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Hotel boutique', 'description' => 'Hoteles con encanto y diseño', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parque', 'description' => 'Zonas verdes y parques recreativos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Piscina', 'description' => 'Sitios con piscina', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bar', 'description' => 'Bares y sitios nocturnos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Café', 'description' => 'Cafeterías y coffee shops', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Deporte', 'description' => 'Sitios para actividades deportivas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cultura', 'description' => 'Museos, teatros, centros culturales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mirador', 'description' => 'Sitios con vistas panorámicas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Spa', 'description' => 'Centros de relajación y spa', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camping', 'description' => 'Zonas de camping', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gourmet', 'description' => 'Restaurantes de alta cocina', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vegetariano', 'description' => 'Opciones vegetarianas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Heladería', 'description' => 'Heladerías y postres', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Discoteca', 'description' => 'Discotecas y baile', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Centro comercial', 'description' => 'Centros comerciales y tiendas', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Artesanías', 'description' => 'Venta de artesanías', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Senderismo', 'description' => 'Rutas y senderos', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
