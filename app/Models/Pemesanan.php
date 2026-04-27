<?php

/*
|--------------------------------------------------------------------------
| Model Pemesanan — Hocky Guest House
|--------------------------------------------------------------------------
| Merepresentasikan tabel 'pemesanan' di database.
| Satu record = satu transaksi check-in tamu.
|
| Status pemesanan:
|   'aktif'      → tamu sedang menginap
|   'selesai'    → tamu sudah checkout
|   'dibatalkan' → pemesanan dibatalkan
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pemesanan extends Model
{
    protected $table = 'pemesanan';

    protected $fillable = [
        'kode_booking',     // kode unik seperti BK-20260426-001
        'kamar_id',         // foreign key ke tabel kamar
        'pelanggan_id',     // foreign key ke tabel pelanggan
        'tanggal_checkin',
        'tanggal_checkout',
        'durasi_malam',     // jumlah malam menginap
        'harga_per_malam',  // snapshot harga saat check-in (bisa berbeda dengan harga kamar sekarang)
        'total_harga',      // = durasi_malam × harga_per_malam
        'status',           // aktif / selesai / dibatalkan
        'catatan',
    ];

    // Konversi tipe data otomatis
    protected $casts = [
        'tanggal_checkin'  => 'date',       // otomatis jadi Carbon date object
        'tanggal_checkout' => 'date',
        'harga_per_malam'  => 'decimal:2',
        'total_harga'      => 'decimal:2',
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Pemesanan ini milik satu kamar.
     * Akses: $pemesanan->kamar->nomor_kamar
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    /**
     * Pemesanan ini milik satu pelanggan.
     * Akses: $pemesanan->pelanggan->nama
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Pemesanan ini punya satu record pembayaran.
     * Akses: $pemesanan->pembayaran->metode
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    // ── METHOD STATIS ─────────────────────────────────────────────────────────

    /**
     * Generate kode booking unik untuk setiap pemesanan baru.
     * Format: BK-YYYYMMDD-NNN
     * Contoh: BK-20260426-001
     *
     * NNN = nomor urut hari ini (padded 3 digit)
     * Jika hari ini sudah ada 2 booking, kode berikutnya adalah 003.
     */
    public static function generateKodeBooking(): string
    {
        $tanggal = now()->format('Ymd'); // contoh: 20260426

        // Hitung berapa pemesanan sudah dibuat hari ini
        $last = self::whereDate('created_at', today())->count() + 1;

        // str_pad = tambah angka 0 di depan agar selalu 3 digit
        return 'BK-' . $tanggal . '-' . str_pad($last, 3, '0', STR_PAD_LEFT);
    }
}
