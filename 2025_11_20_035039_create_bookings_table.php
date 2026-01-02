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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Relasi
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('restrict'); // Jangan hapus booking jika room dihapus (integritas)
            $table->foreignId('booking_category_id')->constrained('booking_categories');

            // Data Kegiatan
            $table->string('activity_name');
            $table->string('instance_name'); // Nama Instansi
            $table->integer('participants_count');
            
            // Waktu
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('reason_for_rejection')->nullable();
            
            // File Paths
            $table->string('surat_pinjam_path');
            $table->string('rundown_path');
            $table->string('disposisi_path')->nullable(); // Diisi admin nanti

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
