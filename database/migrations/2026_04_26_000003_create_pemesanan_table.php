<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique(); // e.g. BK-20260426-001
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('restrict');
            $table->date('tanggal_checkin');
            $table->date('tanggal_checkout');
            $table->integer('durasi_malam')->default(1);
            $table->decimal('harga_per_malam', 12, 2); // snapshot harga saat check-in
            $table->decimal('total_harga', 12, 2);
            $table->enum('status', ['aktif', 'selesai', 'dibatalkan'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemesanan');
    }
};
