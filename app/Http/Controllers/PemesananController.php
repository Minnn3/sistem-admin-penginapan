<?php

/*
|--------------------------------------------------------------------------
| PemesananController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani proses check-in dan check-out tamu:
|   create()  → tampilkan form check-in
|   store()   → proses check-in (simpan pemesanan + ubah status kamar)
|   checkout() → proses check-out (selesaikan pemesanan + kamar jadi kotor)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Pelanggan;
use App\Models\Pemesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemesananController extends Controller
{
    /**
     * Menampilkan form check-in.
     * Menyiapkan daftar kamar yang tersedia dan daftar pelanggan.
     */
    public function create(Request $request)
    {
        // Hanya ambil kamar dengan status 'tersedia'
        $kamarTersedia = Kamar::where('status', 'tersedia')->orderBy('nomor_kamar')->get();

        // Ambil semua pelanggan untuk autocomplete
        $pelangganList = Pelanggan::orderBy('nama')->get();

        // Jika ada parameter ?kamar_id=X di URL (dari tombol Check-In di dashboard),
        // pre-select kamar tersebut di form
        $selectedKamar = $request->filled('kamar_id')
            ? Kamar::find($request->kamar_id)
            : null;

        return view('pemesanan.create', compact('kamarTersedia', 'pelangganList', 'selectedKamar'));
    }

    /**
     * Memproses check-in dan menyimpan data pemesanan.
     * Juga membuat record pembayaran dan mengubah status kamar menjadi 'terisi'.
     */
    public function store(Request $request)
    {
        // Validasi semua input dari form
        $validated = $request->validate([
            'kamar_id'         => ['required', 'exists:kamar,id'],         // harus ada di tabel kamar
            'pelanggan_id'     => ['required', 'exists:pelanggan,id'],     // harus ada di tabel pelanggan
            'tanggal_checkin'  => ['required', 'date'],
            'tanggal_checkout' => ['required', 'date', 'after_or_equal:tanggal_checkin'],
            'metode_bayar'     => ['required', 'in:tunai,transfer,qris'],  // hanya 3 pilihan ini
            'catatan'          => ['nullable', 'string'],
        ], [
            'kamar_id.required'               => 'Kamar wajib dipilih.',
            'pelanggan_id.required'           => 'Pelanggan wajib dipilih.',
            'tanggal_checkin.required'        => 'Tanggal check-in wajib diisi.',
            'tanggal_checkout.required'       => 'Tanggal check-out wajib diisi.',
            'tanggal_checkout.after_or_equal' => 'Tanggal check-out harus sama atau setelah check-in.',
            'metode_bayar.required'           => 'Metode pembayaran wajib dipilih.',
        ]);

        // Ambil data kamar yang dipilih
        $kamar = Kamar::findOrFail($validated['kamar_id']);

        // Pastikan kamar masih tersedia saat diproses
        // (mencegah double-booking jika ada dua tab browser)
        if ($kamar->status !== 'tersedia') {
            return back()->with('error', 'Kamar tidak tersedia untuk di-check in.')->withInput();
        }

        // Hitung durasi menginap
        $checkin  = \Carbon\Carbon::parse($validated['tanggal_checkin']);
        $checkout = \Carbon\Carbon::parse($validated['tanggal_checkout']);
        $durasi   = max(1, $checkin->diffInDays($checkout)); // minimal 1 malam

        // Hitung total harga
        $totalHarga = $kamar->harga_per_malam * $durasi;

        // DB::transaction() = semua operasi di dalamnya berhasil semua atau batal semua
        // Ini penting agar data tidak setengah tersimpan jika terjadi error di tengah proses
        DB::transaction(function () use ($validated, $kamar, $checkin, $checkout, $durasi, $totalHarga) {

            // Buat record pemesanan baru
            $pemesanan = Pemesanan::create([
                'kode_booking'     => Pemesanan::generateKodeBooking(), // generate kode unik otomatis
                'kamar_id'         => $kamar->id,
                'pelanggan_id'     => $validated['pelanggan_id'],
                'tanggal_checkin'  => $checkin->toDateString(),
                'tanggal_checkout' => $checkout->toDateString(),
                'durasi_malam'     => $durasi,
                'harga_per_malam'  => $kamar->harga_per_malam, // snapshot harga saat check-in
                'total_harga'      => $totalHarga,
                'status'           => 'aktif',
                'catatan'          => $validated['catatan'] ?? null,
            ]);

            // Buat record pembayaran terkait pemesanan ini
            Pembayaran::create([
                'pemesanan_id' => $pemesanan->id,
                'jumlah_bayar' => $totalHarga,
                'metode'       => $validated['metode_bayar'],
                'tanggal_bayar' => now(), // waktu saat check-in = waktu bayar
            ]);

            // Ubah status kamar menjadi 'terisi'
            $kamar->update(['status' => 'terisi']);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Check-in berhasil! Kamar ' . $kamar->nomor_kamar . ' sekarang terisi.');
    }

    /**
     * Memproses check-out tamu.
     * Mengubah status pemesanan jadi 'selesai' dan status kamar jadi 'kotor'.
     */
    public function checkout(Pemesanan $pemesanan)
    {
        // Pastikan pemesanan masih aktif (belum checkout sebelumnya)
        if ($pemesanan->status !== 'aktif') {
            return back()->with('error', 'Pemesanan ini sudah selesai atau dibatalkan.');
        }

        // Gunakan transaction agar kedua update berhasil semua atau batal semua
        DB::transaction(function () use ($pemesanan) {
            // Tandai pemesanan sebagai selesai
            $pemesanan->update(['status' => 'selesai']);

            // Tandai kamar perlu dibersihkan (status 'kotor')
            // Staf harus tandai 'bersih' secara manual dari dashboard sebelum bisa check-in lagi
            $pemesanan->kamar->update(['status' => 'kotor']);
        });

        return redirect()->route('dashboard')
            ->with('success', 'Check-out berhasil! Kamar ' . $pemesanan->kamar->nomor_kamar . ' perlu dibersihkan.');
    }
}
