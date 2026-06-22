<?php

/*
|--------------------------------------------------------------------------
| TransaksiController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani semua operasi transaksi harian tamu:
|   index()    → daftar tamu yang sedang menginap (status = 'aktif')
|   create()   → form check-in tamu baru
|   store()    → proses check-in (simpan pemesanan + pembayaran + ubah status kamar)
|   checkout() → proses check-out → redirect ke faktur untuk download
|
| Alur bisnis:
|   Admin buka menu Transaksi → lihat siapa yang sedang menginap
|   → klik "Check-In Tamu Baru" → isi form → simpan → kembali ke Transaksi
|   → klik "Check-Out" pada baris tamu → konfirmasi → proses
|   → otomatis redirect ke halaman Faktur untuk download/print
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    /**
     * Menampilkan daftar tamu yang sedang aktif menginap.
     *
     * Ini adalah halaman "pusat operasional" admin sehari-hari.
     * Admin bisa lihat siapa yang menginap, depositnya apa, dan proses check-out.
     */
    public function index()
    {
        // Ambil semua pemesanan aktif beserta relasi kamar dan pelanggan
        // with() = eager loading untuk hindari N+1 query
        $pemesananAktif = Pemesanan::with(['kamar', 'pelanggan', 'pembayaran'])
            ->where('status', 'aktif')
            ->orderBy('tanggal_checkout') // yang paling dekat checkout ditampilkan dulu
            ->get();

        return view('transaksi.index', compact('pemesananAktif'));
    }

    /**
     * Menampilkan form check-in tamu baru.
     *
     * Hanya kamar yang AKTIF dan TERSEDIA yang bisa dipilih.
     * Kamar nonaktif (maintenance) tidak muncul di form ini.
     */
    public function create(Request $request)
    {
        // Hanya kamar aktif (is_aktif = true) dan tersedia (status = 'tersedia')
        $kamarTersedia = Kamar::aktif()
            ->where('status', 'tersedia')
            ->orderBy('nomor_kamar')
            ->get();

        // Ambil semua pelanggan untuk autocomplete
        $pelangganList = Pelanggan::orderBy('nama')->get();

        // Pre-select kamar jika ada parameter ?kamar_id=X di URL
        $selectedKamar = $request->filled('kamar_id')
            ? Kamar::find($request->kamar_id)
            : null;

        return view('transaksi.create', compact('kamarTersedia', 'pelangganList', 'selectedKamar'));
    }

    /**
     * Memproses check-in dan menyimpan data pemesanan.
     *
     * Yang dilakukan:
     *   1. Validasi input
     *   2. Cek kamar masih tersedia (anti double-booking)
     *   3. Hitung durasi dan total harga
     *   4. Simpan pemesanan (+ deposit info)
     *   5. Simpan pembayaran tunai
     *   6. Ubah status kamar → 'terisi'
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kamar_id'         => ['required', 'exists:kamar,id'],
            'pelanggan_id'     => ['required', 'exists:pelanggan,id'],
            'tanggal_checkin'  => ['required', 'date'],
            'tanggal_checkout' => ['required', 'date', 'after_or_equal:tanggal_checkin'],
            'deposit_jenis'    => ['required', 'in:tunai,ktp,sim'],
            'catatan'          => ['nullable', 'string'],
        ], [
            'kamar_id.required'               => 'Kamar wajib dipilih.',
            'pelanggan_id.required'           => 'Pelanggan wajib dipilih.',
            'tanggal_checkin.required'        => 'Tanggal check-in wajib diisi.',
            'tanggal_checkout.required'       => 'Tanggal check-out wajib diisi.',
            'tanggal_checkout.after_or_equal' => 'Tanggal check-out harus sama atau setelah check-in.',
            'deposit_jenis.required'          => 'Jenis deposit wajib dipilih.',
        ]);

        $kamar = Kamar::findOrFail($validated['kamar_id']);

        // Cek kamar masih tersedia (anti double-booking jika ada 2 tab browser)
        if ($kamar->status !== 'tersedia' || !$kamar->is_aktif) {
            return back()->with('error', 'Kamar tidak tersedia untuk di-check in.')->withInput();
        }

        // Hitung durasi menginap
        $checkin  = \Carbon\Carbon::parse($validated['tanggal_checkin']);
        $checkout = \Carbon\Carbon::parse($validated['tanggal_checkout']);
        $durasi   = max(1, $checkin->diffInDays($checkout)); // minimal 1 malam

        // Hitung total harga
        $totalHarga = $kamar->harga_per_malam * $durasi;

        // Tentukan nominal deposit (tunai = 100.000, ktp/sim = null)
        $depositNominal = $validated['deposit_jenis'] === 'tunai' ? 100000.00 : null;

        // Transaction: semua operasi berhasil semua atau batal semua
        $pemesanan = DB::transaction(function () use ($validated, $kamar, $checkin, $checkout, $durasi, $totalHarga, $depositNominal) {

            // Simpan pemesanan baru
            $pemesanan = Pemesanan::create([
                'kode_booking'     => Pemesanan::generateKodeBooking(),
                'kamar_id'         => $kamar->id,
                'pelanggan_id'     => $validated['pelanggan_id'],
                'tanggal_checkin'  => $checkin->toDateString(),
                'tanggal_checkout' => $checkout->toDateString(),
                'durasi_malam'     => $durasi,
                'harga_per_malam'  => $kamar->harga_per_malam,
                'total_harga'      => $totalHarga,
                'status'           => 'aktif',
                'catatan'          => $validated['catatan'] ?? null,
                'deposit_jenis'    => $validated['deposit_jenis'],
                'deposit_nominal'  => $depositNominal,
            ]);

            // Simpan pembayaran — selalu tunai (sesuai revisi penguji)
            Pembayaran::create([
                'pemesanan_id'  => $pemesanan->id,
                'jumlah_bayar'  => $totalHarga,
                'metode'        => 'tunai', // hardcoded: hanya tunai
                'tanggal_bayar' => now(),
            ]);

            // Ubah status kamar menjadi 'terisi'
            $kamar->update(['status' => 'terisi']);

            return $pemesanan;
        });

        return redirect()->route('transaksi.index')
            ->with('success', "Check-in berhasil! Kamar {$kamar->nomor_kamar} sekarang terisi. Tamu: {$pemesanan->pelanggan->nama}.");
    }

    /**
     * Memproses check-out tamu.
     *
     * Setelah check-out berhasil, otomatis redirect ke halaman faktur
     * sehingga admin langsung bisa print/download kwitansi untuk tamu.
     */
    public function checkout(Pemesanan $pemesanan)
    {
        // Pastikan pemesanan masih aktif
        if ($pemesanan->status !== 'aktif') {
            return back()->with('error', 'Pemesanan ini sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($pemesanan) {
            // Tandai pemesanan sebagai selesai
            $pemesanan->update(['status' => 'selesai']);

            // Tandai kamar perlu dibersihkan
            $pemesanan->kamar->update(['status' => 'kotor']);
        });

        // Redirect ke faktur/show untuk download — sesuai permintaan dosen
        return redirect()->route('faktur.show', $pemesanan->id)
            ->with('success', "Check-out berhasil! Kamar {$pemesanan->kamar->nomor_kamar} perlu dibersihkan. Berikut faktur untuk tamu.");
    }
}
