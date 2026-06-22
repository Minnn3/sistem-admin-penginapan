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
    protected $fillable = [
        'nomor_kamar',
        'nama_kamar',
        'tipe',
        'harga_per_malam',
        'status',
        'deskripsi',
        'is_aktif',  // true = kamar operasional, false = nonaktif/maintenance/gudang
    ];

    // Konversi tipe data otomatis saat mengambil dari database
    protected $casts = [
        'harga_per_malam' => 'decimal:2',
        'is_aktif'        => 'boolean', // otomatis jadi true/false (bukan 1/0)
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Satu kamar bisa punya banyak pemesanan (riwayat).
     */
    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    /**
     * Mengambil SATU pemesanan aktif saat ini (jika ada).
     * Digunakan di dashboard untuk tampilkan nama tamu + info deposit.
     */
    public function pemesananAktif()
    {
        return $this->hasOne(Pemesanan::class)
            ->where('status', 'aktif')
            ->with('pelanggan'); // sertakan data pelanggan sekaligus
    }

    // ── SCOPE ────────────────────────────────────────────────────────────────

    /**
     * Scope untuk filter kamar yang aktif saja.
     * Penggunaan: Kamar::aktif()->get()
     */
    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk filter kamar yang tidak aktif.
     * Penggunaan: Kamar::nonaktif()->get()
     */
    public function scopeNonaktif($query)
    {
        return $query->where('is_aktif', false);
    }

    // ── ACCESSOR (properti virtual) ──────────────────────────────────────────

    /**
     * CSS class untuk badge status operasional kamar.
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

    /**
     * Label untuk status aktif/nonaktif kamar.
     * Contoh: $kamar->aktif_label → 'Aktif' atau 'Nonaktif'
     */
    public function getAktifLabelAttribute(): string
    {
        return $this->is_aktif ? 'Aktif' : 'Nonaktif';
    }

    /**
     * CSS class badge untuk status aktif/nonaktif kamar.
     * Contoh: $kamar->aktif_badge → 'badge-tersedia' atau 'badge-nonaktif'
     */
    public function getAktifBadgeAttribute(): string
    {
        return $this->is_aktif ? 'badge-tersedia' : 'badge-nonaktif';
    }
}
