<?php

/*
|--------------------------------------------------------------------------
| Model Pelanggan — Hocky Guest House
|--------------------------------------------------------------------------
| Merepresentasikan tabel 'pelanggan' di database.
| Menyimpan data identitas dan kontak tamu penginapan.
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    // Kolom yang boleh diisi via create() / update()
    protected $fillable = [
        'nama',
        'no_identitas',    // nomor KTP / SIM / Passport
        'jenis_identitas', // KTP, SIM, atau Passport
        'no_telepon',
        'alamat',
    ];

    // ── RELASI ───────────────────────────────────────────────────────────────

    /**
     * Satu pelanggan bisa punya banyak pemesanan (riwayat menginap).
     * Digunakan untuk tampilkan riwayat di halaman detail pelanggan.
     */
    public function pemesanan(): HasMany
    {
        return $this->hasMany(Pemesanan::class);
    }

    // ── ACCESSOR ─────────────────────────────────────────────────────────────

    /**
     * Menghitung total berapa kali pelanggan sudah menginap (status selesai).
     * Contoh: $pelanggan->total_menginap → 3
     */
    public function getTotalMenginapAttribute(): int
    {
        return $this->pemesanan()
            ->where('status', 'selesai')
            ->count();
    }
}
