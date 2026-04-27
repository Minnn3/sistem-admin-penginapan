<?php

/*
|--------------------------------------------------------------------------
| FakturController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani halaman Faktur/Kwitansi:
|   index() → daftar semua faktur dengan filter & pencarian
|   show()  → detail satu faktur (bisa dicetak)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Pemesanan;
use Illuminate\Http\Request;

class FakturController extends Controller
{
    /**
     * Menampilkan daftar semua faktur/pemesanan.
     * Mendukung filter status dan pencarian kode booking / nama pelanggan.
     */
    public function index(Request $request)
    {
        // Mulai query dengan relasi kamar, pelanggan, dan pembayaran
        // with() = eager loading, memuat relasi sekaligus tanpa query tambahan
        $query = Pemesanan::with('kamar', 'pelanggan', 'pembayaran')
            ->orderByDesc('created_at'); // terbaru di atas

        // Filter berdasarkan status (aktif / selesai / dibatalkan)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Pencarian berdasarkan kode booking ATAU nama pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_booking', 'like', "%{$search}%")
                  // whereHas = cari berdasarkan relasi (nama di tabel pelanggan)
                  ->orWhereHas('pelanggan', fn($q) => $q->where('nama', 'like', "%{$search}%"));
            });
        }

        // withQueryString() = pertahankan filter di URL saat ganti halaman
        $fakturList = $query->paginate(15)->withQueryString();

        return view('faktur.index', compact('fakturList'));
    }

    /**
     * Menampilkan detail satu faktur.
     * View ini bisa langsung dicetak dengan tombol Print.
     */
    public function show(Pemesanan $pemesanan)
    {
        // load() = muat relasi (mirip with() tapi setelah data diambil)
        $pemesanan->load('kamar', 'pelanggan', 'pembayaran');

        return view('faktur.show', compact('pemesanan'));
    }
}
