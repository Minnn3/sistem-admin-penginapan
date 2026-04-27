<?php

/*
|--------------------------------------------------------------------------
| DashboardController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani halaman Dashboard utama.
| Dashboard menampilkan:
|   - Status semua kamar (tersedia / terisi / kotor)
|   - Statistik ringkasan (total kamar, pendapatan hari ini, dll)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     * Mengambil semua data yang diperlukan lalu kirim ke view.
     */
    public function index()
    {
        // Ambil semua kamar beserta data pemesanan aktif & pelanggan-nya
        // with('pemesananAktif.pelanggan') = eager loading, hindari N+1 query
        // orderBy('nomor_kamar') = urutkan berdasarkan nomor kamar
        $kamarList = Kamar::with('pemesananAktif.pelanggan')
            ->orderBy('nomor_kamar')
            ->get();

        // Hitung statistik dari koleksi $kamarList yang sudah diambil
        // (tidak perlu query tambahan ke database)
        $stats = [
            'total'    => $kamarList->count(),                          // total semua kamar
            'tersedia' => $kamarList->where('status', 'tersedia')->count(), // kamar kosong
            'terisi'   => $kamarList->where('status', 'terisi')->count(),   // kamar terisi tamu
            'kotor'    => $kamarList->where('status', 'kotor')->count(),    // kamar perlu dibersihkan
        ];

        // Hitung total pendapatan hari ini dari tabel pembayaran
        // whereDate() = filter berdasarkan tanggal saja (tanpa jam)
        // sum() = jumlahkan kolom jumlah_bayar
        $pendapatanHariIni = Pembayaran::whereDate('tanggal_bayar', today())
            ->sum('jumlah_bayar');

        // Kirim data ke view dashboard
        // compact() = shortcut untuk ['kamarList' => $kamarList, 'stats' => $stats, ...]
        return view('dashboard.index', compact('kamarList', 'stats', 'pendapatanHariIni'));
    }
}
