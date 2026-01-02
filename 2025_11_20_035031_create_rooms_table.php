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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            // Foreign Keys
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->foreignId('cs_staff_id')->nullable()->constrained('cs_staff')->onDelete('set null'); // Jika CS dihapus, kolom ini jadi null
        
            // Data Fisik
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('capacity');
            $table->enum('status', ['available', 'unavailable'])->default('available'); // Kondisi Fisik
        
            $table->softDeletes(); // Fitur Soft Delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
