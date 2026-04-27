<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_kamar')->unique();
            $table->string('nama_kamar')->nullable();
            $table->string('tipe'); // standard, deluxe, suite, dll
            $table->decimal('harga_per_malam', 12, 2);
            $table->enum('status', ['tersedia', 'terisi', 'kotor'])->default('tersedia');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
    }
};
