<?php

/*
|--------------------------------------------------------------------------
| PelangganController — Hocky Guest House
|--------------------------------------------------------------------------
| Menangani semua operasi CRUD untuk data Pelanggan:
|   index()   → daftar pelanggan (dengan pencarian)
|   create()  → form tambah pelanggan
|   store()   → simpan pelanggan baru
|   edit()    → form edit pelanggan
|   update()  → simpan perubahan data pelanggan
|   destroy() → hapus pelanggan
|   show()    → detail pelanggan + riwayat menginap
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar semua pelanggan dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        // withCount('pemesanan') = tambahkan kolom 'pemesanan_count' ke setiap pelanggan
        // sehingga kita bisa tampilkan "sudah menginap X kali" tanpa query tambahan
        $query = Pelanggan::withCount('pemesanan');

        // Jika ada kata kunci pencarian di URL (?search=Budi)
        if ($request->filled('search')) {
            $search = $request->search;

            // Cari di kolom nama, no_identitas, atau no_telepon
            // Tanda % = wildcard (apapun sebelum/sesudah kata kunci)
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_identitas', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        // withQueryString() = pertahankan parameter URL saat pindah halaman (pagination + search)
        $pelangganList = $query->orderBy('nama')->paginate(15)->withQueryString();

        return view('pelanggan.index', compact('pelangganList'));
    }

    /**
     * Menampilkan form tambah pelanggan baru.
     */
    public function create()
    {
        return view('pelanggan.create');
    }

    /**
     * Menyimpan pelanggan baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'            => ['required', 'string', 'max:150'],
            'no_identitas'    => ['nullable', 'string', 'max:30'],  // opsional
            'jenis_identitas' => ['required', 'in:KTP,SIM,Passport'], // hanya 3 pilihan
            'no_telepon'      => ['nullable', 'string', 'max:20'],  // opsional
            'alamat'          => ['nullable', 'string'],            // opsional
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        Pelanggan::create($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit data pelanggan.
     */
    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    /**
     * Menyimpan perubahan data pelanggan.
     */
    public function update(Request $request, Pelanggan $pelanggan)
    {
        $validated = $request->validate([
            'nama'            => ['required', 'string', 'max:150'],
            'no_identitas'    => ['nullable', 'string', 'max:30'],
            'jenis_identitas' => ['required', 'in:KTP,SIM,Passport'],
            'no_telepon'      => ['nullable', 'string', 'max:20'],
            'alamat'          => ['nullable', 'string'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Menghapus pelanggan dari database.
     * Tidak bisa hapus jika pelanggan masih punya pemesanan aktif.
     */
    public function destroy(Pelanggan $pelanggan)
    {
        // Cek apakah ada pemesanan aktif atas nama pelanggan ini
        if ($pelanggan->pemesanan()->where('status', 'aktif')->exists()) {
            return back()->with('error', 'Pelanggan tidak bisa dihapus karena masih memiliki pemesanan aktif.');
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }

    /**
     * Menampilkan detail satu pelanggan beserta riwayat menginap.
     */
    public function show(Pelanggan $pelanggan)
    {
        // Ambil semua pemesanan pelanggan ini
        // with('kamar', 'pembayaran') = sertakan data kamar dan pembayaran terkait
        // orderByDesc = terbaru di atas
        $riwayat = $pelanggan->pemesanan()
            ->with('kamar', 'pembayaran')
            ->orderByDesc('tanggal_checkin')
            ->paginate(10);

        return view('pelanggan.show', compact('pelanggan', 'riwayat'));
    }
}
