<?php

/*
|--------------------------------------------------------------------------
| KamarController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani semua operasi CRUD untuk data Kamar:
|   index()      → daftar semua kamar (dengan filter)
|   create()     → tampilkan form tambah kamar
|   store()      → simpan kamar baru ke database
|   edit()       → tampilkan form edit kamar
|   update()     → simpan perubahan kamar ke database
|   destroy()    → hapus kamar dari database
|   ubahStatus() → ubah status kamar (tersedia / kotor)
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar semua kamar.
     * Mendukung filter berdasarkan tipe dan status.
     */
    public function index(Request $request)
    {
        // Mulai query — belum dieksekusi ke database
        $query = Kamar::query();

        // Jika ada filter tipe di URL (?tipe=Deluxe), tambahkan kondisi WHERE
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Jika ada filter status di URL (?status=terisi), tambahkan kondisi WHERE
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Eksekusi query — paginate(15) = tampilkan 15 data per halaman
        $kamarList = $query->orderBy('nomor_kamar')->paginate(15);

        // Ambil daftar tipe unik untuk dropdown filter
        $tipeList = Kamar::select('tipe')->distinct()->orderBy('tipe')->pluck('tipe');

        // Kirim data ke view: resources/views/kamar/index.blade.php
        return view('kamar.index', compact('kamarList', 'tipeList'));
    }

    /**
     * Menampilkan form tambah kamar baru.
     * Tidak perlu data apapun — hanya tampilkan form kosong.
     */
    public function create()
    {
        return view('kamar.create');
    }

    /**
     * Menyimpan kamar baru ke database.
     * $request = data yang dikirim dari form (POST).
     */
    public function store(Request $request)
    {
        // Validasi input — jika gagal, otomatis balik ke form dengan error
        $validated = $request->validate([
            'nomor_kamar'     => ['required', 'string', 'unique:kamar,nomor_kamar'], // unik di tabel kamar
            'nama_kamar'      => ['nullable', 'string', 'max:100'],
            'tipe'            => ['required', 'string', 'max:50'],
            'harga_per_malam' => ['required', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique'   => 'Nomor kamar sudah digunakan.',
            'tipe.required'        => 'Tipe kamar wajib diisi.',
            'harga_per_malam.required' => 'Harga per malam wajib diisi.',
        ]);

        // Simpan ke database — Kamar::create() otomatis isi kolom yang ada di $fillable
        Kamar::create($validated);

        // Redirect ke daftar kamar dengan pesan sukses
        // with('success', ...) = flash message, muncul sekali lalu hilang
        return redirect()->route('kamar.index')
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kamar.
     * Laravel otomatis cari kamar berdasarkan ID di URL (Route Model Binding).
     */
    public function edit(Kamar $kamar)
    {
        // $kamar sudah otomatis diisi Laravel dari URL /kamar/{id}/edit
        return view('kamar.edit', compact('kamar'));
    }

    /**
     * Menyimpan perubahan kamar ke database.
     */
    public function update(Request $request, Kamar $kamar)
    {
        $validated = $request->validate([
            // unique:kamar,nomor_kamar,{id} = boleh sama dengan nomor kamar ini sendiri
            'nomor_kamar'     => ['required', 'string', 'unique:kamar,nomor_kamar,' . $kamar->id],
            'nama_kamar'      => ['nullable', 'string', 'max:100'],
            'tipe'            => ['required', 'string', 'max:50'],
            'harga_per_malam' => ['required', 'numeric', 'min:0'],
            'deskripsi'       => ['nullable', 'string'],
        ], [
            'nomor_kamar.required' => 'Nomor kamar wajib diisi.',
            'nomor_kamar.unique'   => 'Nomor kamar sudah digunakan.',
            'tipe.required'        => 'Tipe kamar wajib diisi.',
            'harga_per_malam.required' => 'Harga per malam wajib diisi.',
        ]);

        // Update data kamar di database
        $kamar->update($validated);

        return redirect()->route('kamar.index')
            ->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Menghapus kamar dari database.
     * Tidak bisa hapus jika kamar sedang ada tamu aktif.
     */
    public function destroy(Kamar $kamar)
    {
        // Cek dulu apakah ada pemesanan aktif di kamar ini
        if ($kamar->pemesanan()->where('status', 'aktif')->exists()) {
            return back()->with('error', 'Kamar tidak bisa dihapus karena sedang ada tamu menginap.');
        }

        $kamar->delete();

        return redirect()->route('kamar.index')
            ->with('success', 'Kamar berhasil dihapus.');
    }

    /**
     * Mengubah status kamar secara manual.
     * Digunakan untuk menandai kamar sebagai "tersedia" setelah dibersihkan,
     * atau menandai "kotor" jika perlu dibersihkan.
     */
    public function ubahStatus(Request $request, Kamar $kamar)
    {
        // Validasi — status hanya boleh salah satu dari tiga nilai ini
        $request->validate([
            'status' => ['required', 'in:tersedia,terisi,kotor'],
        ]);

        // Kamar yang terisi (ada tamu) tidak bisa diubah manual
        // Harus checkout dulu lewat proses normal
        if ($kamar->status === 'terisi' && $kamar->pemesananAktif) {
            return back()->with('error', 'Status kamar tidak bisa diubah karena masih ada tamu aktif. Lakukan checkout terlebih dahulu.');
        }

        // Update hanya kolom 'status'
        $kamar->update(['status' => $request->status]);

        return back()->with('success', 'Status kamar berhasil diperbarui.');
    }
}
