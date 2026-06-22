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
|
| Jenis deposit:
|   'tunai'  → tamu bayar Rp 100.000 cash sebagai jaminan
|   'ktp'    → tamu serahkan KTP asli
|   'sim'    → tamu serahkan SIM asli
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
        'harga_per_malam',  // snapshot harga saat check-in
        'total_harga',      // = durasi_malam × harga_per_malam
        'status',           // aktif / selesai / dibatalkan
        'catatan',
        'deposit_jenis',    // tunai / ktp / sim
        'deposit_nominal',  // 100000.00 jika tunai, null jika ktp/sim
    ];

    // Konversi tipe data otomatis
    protected $casts = [
        'tanggal_checkin'  => 'date',
        'tanggal_checkout' => 'date',
        'harga_per_malam'  => 'decimal:2',
        'total_harga'      => 'decimal:2',
        'deposit_nominal'  => 'decimal:2',
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Pemesanan ini milik satu kamar.
     */
    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    /**
     * Pemesanan ini milik satu pelanggan.
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * Pemesanan ini punya satu record pembayaran.
     */
    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    // ── ACCESSOR ─────────────────────────────────────────────────────────────

    /**
     * Label teks untuk deposit yang mudah dibaca.
     * Contoh:
     *   deposit_jenis = 'tunai' → 'Tunai (Rp 100.000)'
     *   deposit_jenis = 'ktp'   → 'KTP'
     *   deposit_jenis = 'sim'   → 'SIM'
     */
    public function getDepositLabelAttribute(): string
    {
        return match ($this->deposit_jenis) {
            'tunai' => 'Tunai (Rp 100.000)',
            'ktp'   => 'KTP',
            'sim'   => 'SIM',
            default => '-',
        };
    }

    /**
     * CSS class badge untuk jenis deposit (dipakai di dashboard room card).
     * Tunai → badge hijau (ada uang jaminan)
     * KTP / SIM → badge amber (jaminan dokumen)
     */
    public function getDepositBadgeAttribute(): string
    {
        return match ($this->deposit_jenis) {
            'tunai' => 'badge-deposit-tunai',
            'ktp'   => 'badge-deposit-dok',
            'sim'   => 'badge-deposit-dok',
            default => '',
        };
    }

    // ── METHOD STATIS ─────────────────────────────────────────────────────────

    /**
     * Generate kode booking unik untuk setiap pemesanan baru.
     * Format: BK-YYYYMMDD-NNN
     * Contoh: BK-20260426-001
     */
    public static function generateKodeBooking(): string
    {
        $tanggal = now()->format('Ymd');
        $last = self::whereDate('created_at', today())->count() + 1;
        return 'BK-' . $tanggal . '-' . str_pad($last, 3, '0', STR_PAD_LEFT);
    }
}
