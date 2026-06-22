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
|
| Validasi No. Identitas (revisi pasca seminar):
|   KTP / SIM  → harus tepat 16 digit angka
|   Passport   → 6-15 karakter huruf dan angka (alphanumeric)
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
        $query = Pelanggan::withCount('pemesanan');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_identitas', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

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
     * Validasi no. identitas sesuai jenis (KTP/SIM = 16 digit, Passport = 6-15 alnum).
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->rules($request),
            $this->messages()
        );

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
        $validated = $request->validate(
            $this->rules($request),
            $this->messages()
        );

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    /**
     * Menghapus pelanggan dari database.
     * Tidak bisa hapus jika masih ada pemesanan aktif.
     */
    public function destroy(Pelanggan $pelanggan)
    {
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
        $riwayat = $pelanggan->pemesanan()
            ->with('kamar', 'pembayaran')
            ->orderByDesc('tanggal_checkin')
            ->paginate(10);

        return view('pelanggan.show', compact('pelanggan', 'riwayat'));
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────────────────

    /**
     * Aturan validasi untuk store() dan update().
     *
     * No. identitas divalidasi sesuai jenis:
     *   KTP / SIM  → harus tepat 16 digit angka (no. KTP/SIM Indonesia = 16 digit)
     *   Passport   → 6-15 karakter, huruf dan angka (standar internasional)
     */
    private function rules(Request $request): array
    {
        $jenisIdentitas = $request->input('jenis_identitas', 'KTP');

        // Tentukan rule validasi no. identitas berdasarkan jenis
        if (in_array($jenisIdentitas, ['KTP', 'SIM'])) {
            // Tepat 16 digit angka — 'digits:16' lebih tepat dari 'size:16'
            $noIdentitasRule = ['required', 'string', 'digits:16'];
        } else {
            // Passport: 6-15 karakter alphanumeric (huruf + angka)
            $noIdentitasRule = ['required', 'string', 'regex:/^[A-Z0-9]{6,15}$/i'];
        }

        return [
            'nama'            => ['required', 'string', 'max:150'],
            'no_identitas'    => $noIdentitasRule,
            'jenis_identitas' => ['required', 'in:KTP,SIM,Passport'],
            'no_telepon'      => ['nullable', 'string', 'max:20'],
            'alamat'          => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan error validasi dalam Bahasa Indonesia.
     */
    private function messages(): array
    {
        return [
            'nama.required'            => 'Nama pelanggan wajib diisi.',
            'no_identitas.required'    => 'Nomor identitas wajib diisi.',
            'no_identitas.digits'      => 'Nomor KTP/SIM harus tepat 16 digit angka.',
            'no_identitas.regex'       => 'Nomor Passport harus 6-15 karakter (huruf dan angka).',
            'jenis_identitas.required' => 'Jenis identitas wajib dipilih.',
            'jenis_identitas.in'       => 'Jenis identitas tidak valid.',
        ];
    }
}
