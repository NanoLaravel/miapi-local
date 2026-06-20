<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewsSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [];
        for ($i = 1; $i <= 20; $i++) {
            $reviews[] = [
                'place_id' => ($i % 10) + 1, // Asume que hay al menos 10 lugares
                'user_id' => ($i % 20) + 1, // 20 usuarios
                'rating' => rand(3, 5),
                'comment' => 'Comentario de ejemplo ' . $i,
                'cleanliness' => $i <= 10 ? rand(3, 5) : null,
                'accuracy' => $i <= 10 ? rand(3, 5) : null,
                'check_in' => $i <= 10 ? rand(3, 5) : null,
                'communication' => $i <= 10 ? rand(3, 5) : null,
                'location' => $i <= 10 ? rand(3, 5) : null,
                'price' => $i <= 10 ? rand(3, 5) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('reviews')->insert($reviews);
    }
}
