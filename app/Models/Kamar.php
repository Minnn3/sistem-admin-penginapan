<?php

/*
|--------------------------------------------------------------------------
| Model Kamar — Hocky Guest House
|--------------------------------------------------------------------------
| Model merepresentasikan tabel 'kamar' di database.
| Setiap properti dan method di sini berkaitan dengan data kamar.
|
| Konsep penting:
|   $fillable  = kolom yang boleh diisi via create() / update()
|   $casts     = konversi tipe data otomatis
|   Relasi     = hubungan antar tabel (hasMany, belongsTo, dll)
|   Accessor   = properti virtual yang dihitung dari data yang ada
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kamar extends Model
{
    // Nama tabel di database (default Laravel: plural snake_case dari nama class)
    protected $table = 'kamar';

    // Kolom-kolom yang boleh diisi via Kamar::create() atau $kamar->update()
    // Kolom yang tidak ada di sini tidak bisa diubah secara massal (mass assignment protection)
    protected $fillable = [
        'nomor_kamar',
        'nama_kamar',
        'tipe',
        'harga_per_malam',
        'status',
        'deskripsi',
    ];

    // Konversi tipe data otomatis saat mengambil dari database
    // 'decimal:2' = selalu 2 angka di belakang koma
    protected $casts = [
        'harga_per_malam' => 'decimal:2',
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Satu kamar bisa punya banyak pemesanan (riwayat).
     * Digunakan untuk cek apakah kamar pernah dipakai, dll.
     */
    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    /**
     * Mengambil SATU pemesanan aktif saat ini (jika ada).
     * Digunakan di dashboard untuk tampilkan nama tamu yang menginap.
     * hasOne = ambil hanya satu, dengan kondisi status = 'aktif'
     */
    public function pemesananAktif()
    {
        return $this->hasOne(Pemesanan::class)
            ->where('status', 'aktif')
            ->with('pelanggan'); // sertakan data pelanggan sekaligus
    }

    // ── ACCESSOR (properti virtual) ──────────────────────────────────────────
    // Accessor = properti yang bisa dipanggil seperti $kamar->status_badge
    // tapi sebenarnya dihitung dari data yang ada, bukan kolom database

    /**
     * CSS class untuk badge status kamar.
     * Contoh: $kamar->status_badge → 'badge-tersedia'
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'tersedia' => 'badge-tersedia',
            'terisi'   => 'badge-terisi',
            'kotor'    => 'badge-kotor',
            default    => 'badge-tersedia',
        };
    }

    /**
     * Label teks untuk status kamar dalam Bahasa Indonesia.
     * Contoh: $kamar->status_label → 'Perlu Dibersihkan'
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tersedia' => 'Tersedia',
            'terisi'   => 'Terisi',
            'kotor'    => 'Perlu Dibersihkan',
            default    => 'Tersedia',
        };
    }
}
