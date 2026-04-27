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
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PendapatanController;
use Illuminate\Support\Facades\Route;

// ── ROUTE AUTENTIKASI (tidak perlu login) ────────────────────────────────────
// Halaman form login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Proses login (POST = kirim form)
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Proses logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── ROUTE YANG MEMBUTUHKAN LOGIN ──────────────────────────────────────────────
// middleware('auth') → Laravel otomatis redirect ke /login jika belum login
Route::middleware('auth')->group(function () {

    // Redirect dari "/" langsung ke dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));

    // ── DASHBOARD ──────────────────────────────────────────────────────────────
    // Halaman utama — menampilkan status semua kamar + statistik
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── KAMAR ──────────────────────────────────────────────────────────────────
    // Route::resource otomatis membuat 7 route CRUD sekaligus:
    //   GET    /kamar              → index   (daftar kamar)
    //   GET    /kamar/create       → create  (form tambah)
    //   POST   /kamar              → store   (simpan baru)
    //   GET    /kamar/{id}         → show    (detail — jarang dipakai)
    //   GET    /kamar/{id}/edit    → edit    (form edit)
    //   PUT    /kamar/{id}         → update  (simpan perubahan)
    //   DELETE /kamar/{id}         → destroy (hapus)
    Route::resource('kamar', KamarController::class);

    // Route tambahan: ubah status kamar (tersedia / kotor)
    // POST karena ada perubahan data
    Route::post('/kamar/{kamar}/status', [KamarController::class, 'ubahStatus'])->name('kamar.ubah-status');

    // ── PELANGGAN ──────────────────────────────────────────────────────────────
    // CRUD pelanggan — sama seperti kamar
    Route::resource('pelanggan', PelangganController::class);

    // ── PEMESANAN / CHECK-IN / CHECK-OUT ───────────────────────────────────────
    // Form check-in tamu baru
    Route::get('/checkin', [PemesananController::class, 'create'])->name('pemesanan.create');
    // Proses simpan check-in (POST)
    Route::post('/checkin', [PemesananController::class, 'store'])->name('pemesanan.store');
    // Proses check-out berdasarkan ID pemesanan
    Route::post('/checkout/{pemesanan}', [PemesananController::class, 'checkout'])->name('pemesanan.checkout');

    // ── FAKTUR ─────────────────────────────────────────────────────────────────
    // Daftar semua faktur/kwitansi
    Route::get('/faktur', [FakturController::class, 'index'])->name('faktur.index');
    // Detail satu faktur (untuk cetak)
    Route::get('/faktur/{pemesanan}', [FakturController::class, 'show'])->name('faktur.show');

    // ── PENDAPATAN ─────────────────────────────────────────────────────────────
    // Laporan pendapatan (ringkasan + grafik + tabel transaksi)
    Route::get('/pendapatan', [PendapatanController::class, 'index'])->name('pendapatan.index');
});
