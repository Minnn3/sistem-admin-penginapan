<?php

/*
|--------------------------------------------------------------------------
| PendapatanController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani halaman Laporan Pendapatan:
|   index() → ringkasan pendapatan per periode + grafik + tabel transaksi
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PendapatanController extends Controller
{
    /**
     * Menampilkan halaman laporan pendapatan.
     * User bisa filter berdasarkan bulan dan tahun.
     */
    public function index(Request $request)
    {
        // Ambil bulan & tahun dari URL, default = bulan/tahun sekarang
        // Contoh URL: /pendapatan?bulan=3&tahun=2026
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // ── RINGKASAN STATISTIK ──────────────────────────────────────────────
        // Pendapatan hari ini
        $pendapatanHariIni = Pembayaran::whereDate('tanggal_bayar', today())
            ->sum('jumlah_bayar');

        // Pendapatan minggu ini (Senin s.d. Minggu)
        $pendapatanMingguIni = Pembayaran::whereBetween('tanggal_bayar', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->sum('jumlah_bayar');

        // Pendapatan bulan yang dipilih di filter
        $pendapatanBulanIni = Pembayaran::whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->sum('jumlah_bayar');

        // Pendapatan seluruh tahun yang dipilih
        $pendapatanTahunIni = Pembayaran::whereYear('tanggal_bayar', $tahun)
            ->sum('jumlah_bayar');

        // ── TABEL TRANSAKSI ──────────────────────────────────────────────────
        // Ambil pembayaran pada bulan & tahun yang dipilih
        // with() = sertakan data pemesanan, kamar, dan pelanggan terkait
        $isCetak = $request->boolean('cetak'); // true jika URL mengandung ?cetak=1

        $transaksiQuery = Pembayaran::with('pemesanan.kamar', 'pemesanan.pelanggan')
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->orderByDesc('tanggal_bayar');

        // Mode cetak: ambil SEMUA tanpa pagination agar laporan lengkap
        // Mode normal: paginate 15 per halaman
        $transaksi = $isCetak
            ? $transaksiQuery->get()          // Collection (tidak ada pagination)
            : $transaksiQuery->paginate(15)->withQueryString();

        // ── DATA GRAFIK ──────────────────────────────────────────────────────
        // Buat array data untuk grafik garis 12 bulan terakhir
        // Loop dari 11 bulan lalu sampai bulan ini
        $grafikBulanan = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $grafikBulanan[] = [
                'label' => $date->format('M Y'),  // contoh: "Apr 2026"
                'total' => Pembayaran::whereMonth('tanggal_bayar', $date->month)
                                     ->whereYear('tanggal_bayar', $date->year)
                                     ->sum('jumlah_bayar'),
            ];
        }

        // Daftar tahun untuk dropdown filter (5 tahun ke belakang)
        $tahunList = range(now()->year, now()->year - 4, -1);

        // Kirim semua data ke view
        return view('pendapatan.index', compact(
            'pendapatanHariIni',
            'pendapatanMingguIni',
            'pendapatanBulanIni',
            'pendapatanTahunIni',
            'transaksi',
            'grafikBulanan',
            'bulan',
            'tahun',
            'tahunList',
            'isCetak'
        ));
    }
}
