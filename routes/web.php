<?php

/*
|--------------------------------------------------------------------------
| WEB ROUTES — Hocky Guest House
|--------------------------------------------------------------------------
| File ini mendaftarkan semua URL (route) yang bisa diakses di aplikasi.
| Setiap route menghubungkan URL → Controller → Method yang menanganinya.
|
| Format:
|   Route::get('/url', [Controller::class, 'method'])->name('nama.route');
|
| ->name('...') → digunakan di Blade dengan {{ route('nama.route') }}
| middleware('auth') → hanya bisa diakses jika sudah login
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FakturController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PendapatanController;
use Illuminate\Support\Facades\Route;

// ── ROUTE AUTENTIKASI (tidak perlu login) ────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── ROUTE YANG MEMBUTUHKAN LOGIN ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Redirect dari "/" langsung ke dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // ── DASHBOARD ──────────────────────────────────────────────────────────────
    // Halaman informasi — status kamar, statistik, monitoring
    // (tidak ada aksi check-in/out di sini — semua pindah ke Transaksi)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── KAMAR ──────────────────────────────────────────────────────────────────
    // CRUD kamar — TANPA route destroy (hapus dihilangkan, diganti toggleAktif)
    Route::get('/kamar', [KamarController::class, 'index'])->name('kamar.index');
    Route::get('/kamar/create', [KamarController::class, 'create'])->name('kamar.create');
    Route::post('/kamar', [KamarController::class, 'store'])->name('kamar.store');
    Route::get('/kamar/{kamar}/edit', [KamarController::class, 'edit'])->name('kamar.edit');
    Route::put('/kamar/{kamar}', [KamarController::class, 'update'])->name('kamar.update');

    // Toggle aktif / nonaktif kamar (menggantikan destroy)
    Route::post('/kamar/{kamar}/toggle-aktif', [KamarController::class, 'toggleAktif'])->name('kamar.toggle-aktif');

    // Ubah status kamar (tersedia / kotor) — untuk tandai sudah dibersihkan
    Route::post('/kamar/{kamar}/status', [KamarController::class, 'ubahStatus'])->name('kamar.ubah-status');

    // ── PELANGGAN ──────────────────────────────────────────────────────────────
    // CRUD pelanggan — lengkap termasuk detail (show)
    Route::resource('pelanggan', PelangganController::class);

    // ── TRANSAKSI (Check-In / Check-Out) ───────────────────────────────────────
    // Menggantikan menu "Check-In" yang lama.
    // Pusat operasional harian: lihat tamu aktif, proses check-in, proses check-out.
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/checkin', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::post('/transaksi/checkin', [TransaksiController::class, 'store'])->name('transaksi.store');
    Route::post('/transaksi/{pemesanan}/checkout', [TransaksiController::class, 'checkout'])->name('transaksi.checkout');

    // Redirect route lama /checkin → /transaksi/checkin (backward compatibility)
    Route::get('/checkin', fn() => redirect()->route('transaksi.create'));
    Route::post('/checkin', fn() => redirect()->route('transaksi.store'));
    Route::post('/checkout/{pemesanan}', fn($pemesanan) => redirect()->route('transaksi.checkout', $pemesanan));

    // ── FAKTUR ─────────────────────────────────────────────────────────────────
    // Riwayat faktur — hanya pemesanan yang sudah selesai
    // Setelah check-out, admin otomatis diarahkan ke faktur/show
    Route::get('/faktur', [FakturController::class, 'index'])->name('faktur.index');
    Route::get('/faktur/{pemesanan}', [FakturController::class, 'show'])->name('faktur.show');

    // ── PENDAPATAN ─────────────────────────────────────────────────────────────
    // Laporan pendapatan (ringkasan + grafik + tabel transaksi)
    Route::get('/pendapatan', [PendapatanController::class, 'index'])->name('pendapatan.index');
});
