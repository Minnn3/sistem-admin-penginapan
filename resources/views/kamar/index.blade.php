@extends('layouts.app')
@section('title', 'Manajemen Kamar')
@section('page-title', 'Kamar')

@section('content')
<div class="section-header">
    <h2 class="section-title">Daftar Kamar</h2>
    <a href="{{ route('kamar.create') }}" class="btn btn-primary">Tambah Kamar</a>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Total: {{ $kamarList->total() }} kamar</span>
        <form method="GET" class="filter-bar">
            <select name="tipe" class="form-control" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Tipe</option>
                @foreach($tipeList as $t)
                    <option value="{{ $t }}" {{ request('tipe') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
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
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kamarList as $kamar)
                <tr>
                    <td class="td-primary">{{ $kamar->nomor_kamar }}</td>
                    <td>{{ $kamar->nama_kamar ?? '-' }}</td>
                    <td>{{ $kamar->tipe }}</td>
                    <td class="money">Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}</td>
                    <td><span class="badge badge-{{ $kamar->status }}">{{ $kamar->status_label }}</span></td>
                    <td>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                            <a href="{{ route('kamar.edit', $kamar) }}" class="btn btn-xs btn-ghost">Edit</a>
                            @if($kamar->status === 'kotor')
                                <form action="{{ route('kamar.ubah-status', $kamar) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="tersedia">
                                    <button class="btn btn-xs btn-success">Bersih</button>
                                </form>
                            @elseif($kamar->status === 'tersedia')
                                <form action="{{ route('kamar.ubah-status', $kamar) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="kotor">
                                    <button class="btn btn-xs btn-ghost">Kotor</button>
                                </form>
                            @endif
                            <form action="{{ route('kamar.destroy', $kamar) }}" method="POST"
                                  onsubmit="return confirm('Hapus kamar {{ $kamar->nomor_kamar }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" style="padding: 4px 6px;">
                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
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
