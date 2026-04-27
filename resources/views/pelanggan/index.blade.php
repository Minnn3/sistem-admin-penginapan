@extends('layouts.app')
@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('content')
<div class="section-header">
    <h2 class="section-title">Daftar Pelanggan</h2>
    <a href="{{ route('pelanggan.create') }}" class="btn btn-primary">✚ Tambah Pelanggan</a>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Total: {{ $pelangganList->total() }} pelanggan</span>
        <form method="GET" class="filter-bar">
            <div class="search-input-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" class="form-control search-input" value="{{ request('search') }}" placeholder="Cari nama / identitas...">
            </div>
            <button type="submit" class="btn btn-ghost">Cari</button>
            @if(request('search')) <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">✕ Reset</a> @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Identitas</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Total Menginap</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelangganList as $p)
                <tr>
                    <td class="td-primary">{{ $p->nama }}</td>
                    <td>
                        <span style="font-size:11px;color:var(--text-muted)">{{ $p->jenis_identitas }}</span><br>
                        {{ $p->no_identitas ?? '-' }}
                    </td>
                    <td>{{ $p->no_telepon ?? '-' }}</td>
                    <td style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->alamat ?? '-' }}</td>
                    <td><span class="badge" style="background:var(--blue-bg);color:var(--blue)">{{ $p->pemesanan_count }}x</span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="{{ route('pelanggan.show', $p) }}" class="btn btn-xs btn-ghost">📋 Riwayat</a>
                            <a href="{{ route('pelanggan.edit', $p) }}" class="btn btn-xs btn-ghost">✏ Edit</a>
                            <form action="{{ route('pelanggan.destroy', $p) }}" method="POST"
                                  onsubmit="return confirm('Hapus pelanggan {{ $p->nama }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon">👤</div>
                            <div class="empty-title">Belum ada pelanggan</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pelangganList->hasPages())
    <div class="pagination-wrap">{{ $pelangganList->links('vendor.pagination.custom') }}</div>
    @endif
</div>
@endsection
