<?php

/*
|--------------------------------------------------------------------------
| DashboardController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani halaman Dashboard utama.
| Dashboard sekarang adalah halaman INFORMASI saja:
|   - Status semua kamar (tersedia / terisi / kotor / nonaktif)
|   - Statistik ringkasan
|   - Filter kamar: aktif / nonaktif / semua
|
| CATATAN: Aksi check-in dan check-out sudah pindah ke TransaksiController.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Pembayaran;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     * Mendukung filter kamar: aktif (default), nonaktif, atau semua.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        // Baca filter dari URL — default: hanya kamar aktif
        $filterDashboard = $request->get('filter', 'aktif');

        // Bangun query kamar
        $query = Kamar::with('pemesananAktif.pelanggan')
            ->orderBy('nomor_kamar');

        // Terapkan filter is_aktif
        if ($filterDashboard === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($filterDashboard === 'nonaktif') {
            $query->where('is_aktif', false);
        }
        // 'semua' = tidak ada filter tambahan

        $kamarList = $query->get();

        // Hitung statistik — selalu dari kamar AKTIF saja (bukan bergantung filter tampilan)
        $kamarAktif = Kamar::where('is_aktif', true)->get();
        $stats = [
            'total'    => $kamarAktif->count(),
            'tersedia' => $kamarAktif->where('status', 'tersedia')->count(),
            'terisi'   => $kamarAktif->where('status', 'terisi')->count(),
            'kotor'    => $kamarAktif->where('status', 'kotor')->count(),
        ];

        // Pendapatan hari ini dari tabel pembayaran
        $pendapatanHariIni = Pembayaran::whereDate('tanggal_bayar', today())
            ->sum('jumlah_bayar');

        return view('dashboard.index', compact(
            'kamarList',
            'stats',
            'pendapatanHariIni',
            'filterDashboard'
        ));
    }
}
