<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom deposit ke tabel pemesanan.
     *
     * Deposit adalah jaminan yang diberikan tamu saat check-in.
     * Ada 3 jenis deposit:
     *   - 'tunai'  → tamu bayar cash Rp 100.000 sebagai jaminan
     *   - 'ktp'    → tamu serahkan KTP asli sebagai jaminan
     *   - 'sim'    → tamu serahkan SIM asli sebagai jaminan
     *
     * deposit_nominal hanya diisi saat deposit_jenis = 'tunai' (100000.00)
     * Untuk ktp dan sim, deposit_nominal = null (tidak ada nominal uang)
     *
     * Tujuan: agar admin tidak lupa tamu depositnya apa —
     * info ini ditampilkan di dashboard pada room card yang terisi.
     */
    public function up(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            // Jenis deposit: tunai, ktp, atau sim — default ktp
            $table->enum('deposit_jenis', ['tunai', 'ktp', 'sim'])->default('ktp')->after('catatan');

            // Nominal deposit — hanya diisi jika deposit_jenis = 'tunai'
            // nullable karena ktp/sim tidak ada nominalnya
            $table->decimal('deposit_nominal', 12, 2)->nullable()->after('deposit_jenis');
        });
    }

    public function down(): void
    {
        Schema::table('pemesanan', function (Blueprint $table) {
            $table->dropColumn(['deposit_jenis', 'deposit_nominal']);
        });
    }
};
