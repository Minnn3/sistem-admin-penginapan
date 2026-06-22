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
     * Menampilkan riwayat faktur — hanya pemesanan yang sudah selesai.
     * Pemesanan aktif tidak ditampilkan di sini (sudah ada di menu Transaksi).
     */
    public function index(Request $request)
    {
        $query = Pemesanan::with('kamar', 'pelanggan', 'pembayaran')
            ->where('status', 'selesai') // default: hanya yang sudah checkout
            ->orderByDesc('created_at');

        // Pencarian berdasarkan kode booking ATAU nama pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_booking', 'like', "%{$search}%")
                  ->orWhereHas('pelanggan', fn($q) => $q->where('nama', 'like', "%{$search}%"));
            });
        }

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
