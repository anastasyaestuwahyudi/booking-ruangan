<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Relasi (Foreign Keys)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Penting: unique() memastikan 1 Booking cuma boleh 1 Review
            $table->foreignId('booking_id')->constrained()->onDelete('cascade')->unique();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');

            // --- BAGIAN DUAL REVIEW ---
            
            // 1. Ulasan Ruangan
            $table->integer('rating_room'); // Bintang 1-5
            $table->text('comment_room');   // Komentar teks
            
            // 2. Ulasan CS Staff
            $table->integer('rating_cs');   // Bintang 1-5
            $table->text('comment_cs');     // Komentar teks

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
