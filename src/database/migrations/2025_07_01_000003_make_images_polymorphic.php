<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer la tabla images polimórfica
        Schema::table('images', function (Blueprint $table) {
            // Eliminar la restricción foreign key de place_id
            $table->dropForeign(['place_id']);
            
            // Renombrar place_id a imageable_id y agregar imageable_type
            $table->renameColumn('place_id', 'imageable_id');
            $table->string('imageable_type')->after('id')->nullable();
            
            // Agregar índice para la relación polimórfica
            $table->index(['imageable_type', 'imageable_id']);
        });

        // Actualizar los registros existentes con el tipo de modelo
        DB::table('images')->update(['imageable_type' => 'App\\Models\\Place']);
    }

    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropIndex(['imageable_type', 'imageable_id']);
            $table->dropColumn('imageable_type');
            $table->renameColumn('imageable_id', 'place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
        });
    }
};
