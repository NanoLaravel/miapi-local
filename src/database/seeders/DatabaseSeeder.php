<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Desactivar claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reviews')->truncate();
        DB::table('images')->truncate();
        DB::table('place_category')->truncate();
        DB::table('places')->truncate();
        DB::table('categories')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call([
            UsersSeeder::class,
            CategoriesSeeder::class,
            PlacesSeeder::class,
            PlaceCategorySeeder::class,
            ImagesSeeder::class,
            ReviewsSeeder::class,
            PricesSeeder::class,
            FavoritesSeeder::class,
            EventsAdvertisementsPermissionsSeeder::class,
        ]);
    }
}
