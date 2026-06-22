<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom is_aktif ke tabel kamar.
     *
     * Kolom ini menggantikan fitur "hapus kamar" dengan toggle aktif/nonaktif.
     * Kamar yang nonaktif (false) tidak akan muncul di daftar kamar tersedia
     * dan tidak bisa di-check-in, tapi data historisnya tetap tersimpan.
     *
     * Contoh penggunaan:
     *   - Kamar mau dijadikan gudang → is_aktif = false
     *   - Kamar sedang renovasi/maintenance → is_aktif = false
     *   - Kamar siap beroperasi → is_aktif = true
     */
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            // Boolean, default true = semua kamar aktif sebelum ada perubahan
            // after('deskripsi') = letakkan kolom ini setelah kolom deskripsi
            $table->boolean('is_aktif')->default(true)->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropColumn('is_aktif');
        });
    }
};
