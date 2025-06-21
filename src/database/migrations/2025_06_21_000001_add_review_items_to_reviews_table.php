<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedTinyInteger('cleanliness')->nullable()->after('rating');
            $table->unsignedTinyInteger('accuracy')->nullable()->after('cleanliness');
            $table->unsignedTinyInteger('check_in')->nullable()->after('accuracy');
            $table->unsignedTinyInteger('communication')->nullable()->after('check_in');
            $table->unsignedTinyInteger('location')->nullable()->after('communication');
            $table->unsignedTinyInteger('price')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['cleanliness', 'accuracy', 'check_in', 'communication', 'location', 'price']);
        });
    }
};
