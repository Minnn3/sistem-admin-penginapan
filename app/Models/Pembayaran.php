<?php

/*
|--------------------------------------------------------------------------
| Model Pembayaran — Hocky Guest House
|--------------------------------------------------------------------------
| Merepresentasikan tabel 'pembayaran' di database.
| Satu pemesanan = satu pembayaran.
|
| Metode pembayaran yang tersedia: tunai, transfer, qris
| Untuk menambah metode baru, ubah enum di migrasi dan validasi controller.
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'pemesanan_id', // foreign key ke tabel pemesanan
        'jumlah_bayar', // nominal yang dibayarkan
        'metode',       // tunai / transfer / qris
        'keterangan',   // catatan tambahan (opsional)
        'tanggal_bayar',// waktu pembayaran dilakukan
    ];

    // Konversi tipe data otomatis
    protected $casts = [
        'jumlah_bayar'  => 'decimal:2',
        'tanggal_bayar' => 'datetime', // jadi Carbon datetime object
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Pembayaran ini terkait dengan satu pemesanan.
     * Akses: $pembayaran->pemesanan->kode_booking
     */
    public function pemesanan(): BelongsTo
    {
        return $this->belongsTo(Pemesanan::class);
    }
}
