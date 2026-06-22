<?php

/*
|--------------------------------------------------------------------------
| KamarController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani semua operasi untuk data Kamar:
|   index()       → daftar semua kamar (dengan filter aktif/nonaktif/semua)
|   create()      → tampilkan form tambah kamar
|   store()       → simpan kamar baru ke database
|   edit()        → tampilkan form edit kamar
|   update()      → simpan perubahan kamar ke database
|   toggleAktif() → aktifkan / nonaktifkan kamar (menggantikan destroy)
|   ubahStatus()  → ubah status kamar (tersedia / kotor)
|
| CATATAN: Fitur hapus (destroy) dihapus sesuai revisi.
| Kamar tidak dihapus tapi dinonaktifkan agar data historis tetap aman.
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Kamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Menampilkan daftar semua kamar.
     * Mendukung filter: aktif (default), nonaktif, atau semua.
     */
    public function index(Request $request)
    {
        $query = Kamar::query();

        // Filter aktif/nonaktif — default: tampilkan semua (aktif + nonaktif)
        // agar admin bisa lihat kondisi lengkap kamar
        $filterAktif = $request->get('aktif', 'semua');
        if ($filterAktif === 'aktif') {
            $query->where('is_aktif', true);
        } elseif ($filterAktif === 'nonaktif') {
            $query->where('is_aktif', false);
        }
        // 'semua' = tidak ada filter tambahan

        // Filter tipe kamar (jika ada)
        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        // Filter status operasional (jika ada)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Eksekusi query dengan pagination
        $kamarList = $query->orderBy('nomor_kamar')->paginate(15)->withQueryString();

        // Daftar tipe unik untuk dropdown filter
        $tipeList = Kamar::select('tipe')->distinct()->orderBy('tipe')->pluck('tipe');

        return view('kamar.index', compact('kamarList', 'tipeList', 'filterAktif'));
    }

    /**
     * Menampilkan form tambah kamar baru.
     */
    public function create()
    {
        return view('kamar.create');
    }

    /**
     * Menyimpan kamar baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_kamar'     => ['required', 'string', 'unique:kamar,nomor_kamar'],
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

        // Kamar baru selalu aktif secara default
        $validated['is_aktif'] = true;
        $validated['status']   = 'tersedia';

        Kamar::create($validated);

        return redirect()->route('kamar.index')
            ->with('success', 'Kamar berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit kamar.
     */
    public function edit(Kamar $kamar)
    {
        return view('kamar.edit', compact('kamar'));
    }

    /**
     * Menyimpan perubahan kamar ke database.
     */
    public function update(Request $request, Kamar $kamar)
    {
        $validated = $request->validate([
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

        $kamar->update($validated);

        return redirect()->route('kamar.index')
            ->with('success', 'Kamar berhasil diperbarui.');
    }

    /**
     * Toggle aktif / nonaktif kamar.
     *
     * Menggantikan fitur "hapus". Kamar tidak dihapus dari database,
     * hanya dinonaktifkan sehingga tidak bisa digunakan untuk check-in.
     * Kamar yang nonaktif tetap ada di riwayat tapi tidak muncul di
     * daftar kamar tersedia.
     *
     * Tidak bisa nonaktifkan kamar yang sedang terisi (ada tamu).
     */
    public function toggleAktif(Kamar $kamar)
    {
        // Cegah nonaktifkan kamar yang sedang ada tamunya
        if ($kamar->is_aktif && $kamar->status === 'terisi') {
            return back()->with('error', 'Kamar tidak bisa dinonaktifkan karena sedang ada tamu menginap. Lakukan check-out terlebih dahulu.');
        }

        // Flip nilai is_aktif: true → false, false → true
        $kamar->update(['is_aktif' => !$kamar->is_aktif]);

        $label = $kamar->is_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kamar {$kamar->nomor_kamar} berhasil {$label}.");
    }

    /**
     * Mengubah status kamar secara manual (tersedia / kotor).
     * Digunakan untuk tandai kamar sudah dibersihkan.
     */
    public function ubahStatus(Request $request, Kamar $kamar)
    {
        $request->validate([
            'status' => ['required', 'in:tersedia,terisi,kotor'],
        ]);

        // Kamar yang terisi tidak bisa diubah manual — harus checkout dulu
        if ($kamar->status === 'terisi' && $kamar->pemesananAktif) {
            return back()->with('error', 'Status kamar tidak bisa diubah karena masih ada tamu aktif. Lakukan checkout terlebih dahulu.');
        }

        $kamar->update(['status' => $request->status]);

        return back()->with('success', 'Status kamar berhasil diperbarui.');
    }
}
