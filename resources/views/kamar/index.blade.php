@extends('layouts.app')
@section('title', 'Manajemen Kamar')
@section('page-title', 'Kamar')

@section('content')
<div class="section-header">
    <h2 class="section-title">Daftar Kamar</h2>
    <a href="{{ route('kamar.create') }}" class="btn btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Tambah Kamar
    </a>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Total: {{ $kamarList->total() }} kamar</span>
        <form method="GET" class="filter-bar">
            {{-- Filter Aktif/Nonaktif --}}
            <select name="aktif" class="form-control" style="width:160px;" onchange="this.form.submit()">
                <option value="semua" {{ $filterAktif === 'semua' ? 'selected' : '' }}>Semua Kamar</option>
                <option value="aktif" {{ $filterAktif === 'aktif' ? 'selected' : '' }}>Kamar Aktif</option>
                <option value="nonaktif" {{ $filterAktif === 'nonaktif' ? 'selected' : '' }}>Kamar Nonaktif</option>
            </select>
            {{-- Filter Tipe --}}
            <select name="tipe" class="form-control" style="width:140px;" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                @foreach($tipeList as $t)
                    <option value="{{ $t }}" {{ request('tipe') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            {{-- Filter Status --}}
            <select name="status" class="form-control" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="terisi"   {{ request('status') == 'terisi'   ? 'selected' : '' }}>Terisi</option>
                <option value="kotor"    {{ request('status') == 'kotor'    ? 'selected' : '' }}>Kotor</option>
            </select>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No. Kamar</th>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Harga / Malam</th>
                    <th>Status Kamar</th>
                    <th>Kondisi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kamarList as $kamar)
                <tr class="{{ !$kamar->is_aktif ? 'row-muted' : '' }}">
                    <td class="td-primary">{{ $kamar->nomor_kamar }}</td>
                    <td>{{ $kamar->nama_kamar ?? '-' }}</td>
                    <td>{{ $kamar->tipe }}</td>
                    <td class="money">Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}</td>
                    <td>
                        @if($kamar->is_aktif)
                            <span class="badge badge-{{ $kamar->status }}">{{ $kamar->status_label }}</span>
                        @else
                            <span class="badge badge-nonaktif">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        @if($kamar->is_aktif)
                            @if($kamar->status === 'kotor')
                                <form action="{{ route('kamar.ubah-status', $kamar) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="tersedia">
                                    <button class="btn btn-xs btn-success">Tandai Bersih</button>
                                </form>
                            @elseif($kamar->status === 'tersedia')
                                <form action="{{ route('kamar.ubah-status', $kamar) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="status" value="kotor">
                                    <button class="btn btn-xs btn-ghost">Tandai Kotor</button>
                                </form>
                            @else
                                <span style="font-size:12px;color:var(--text-tertiary);">Ada tamu aktif</span>
                            @endif
                        @else
                            <span style="font-size:12px;color:var(--text-tertiary);">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                            <a href="{{ route('kamar.edit', $kamar) }}" class="btn btn-xs btn-ghost">Edit</a>

                            {{-- Toggle Aktif/Nonaktif (menggantikan fitur hapus) --}}
                            <form action="{{ route('kamar.toggle-aktif', $kamar) }}" method="POST"
                                  onsubmit="return confirm('{{ $kamar->is_aktif ? 'Nonaktifkan kamar ' . $kamar->nomor_kamar . '? Kamar tidak bisa digunakan untuk check-in.' : 'Aktifkan kembali kamar ' . $kamar->nomor_kamar . '?' }}')">
                                @csrf
                                @if($kamar->is_aktif)
                                    <button class="btn btn-xs btn-danger" title="Nonaktifkan kamar">
                                        <i data-lucide="toggle-right" style="width:14px;height:14px;"></i> Nonaktifkan
                                    </button>
                                @else
                                    <button class="btn btn-xs btn-success" title="Aktifkan kamar">
                                        <i data-lucide="toggle-left" style="width:14px;height:14px;"></i> Aktifkan
                                    </button>
                                @endif
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i data-lucide="bed-double" class="empty-icon"></i>
                            <div class="empty-title">Belum ada kamar</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($kamarList->hasPages())
    <div class="pagination-wrap">
        {{ $kamarList->links('vendor.pagination.custom') }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
/* Baris kamar nonaktif tampil muted */
.row-muted td { opacity: 0.55; }
.row-muted .btn { opacity: 1; } /* tapi tombol tetap terlihat jelas */
</style>
@endpush
