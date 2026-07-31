<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('place_id')->nullable()->constrained('places')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('reservation_code')->unique();
            $table->date('check_in_date')->nullable();
            $table->date('check_out_date')->nullable();
            $table->integer('nights')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('COP');
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
