@extends('layouts.app')
@section('title', 'Faktur')
@section('page-title', 'Faktur')

@section('content')
<div class="section-header">
    <h2 class="section-title">Daftar Faktur</h2>
</div>

<div class="table-card">
    <div class="table-header">
        <span class="table-title">Total: {{ $fakturList->total() }} transaksi</span>
        <form method="GET" class="filter-bar">
            <div class="search-input-wrap">
                <i data-lucide="search" class="search-icon" style="width: 14px; height: 14px;"></i>
                <input type="text" name="search" class="form-control search-input" value="{{ request('search') }}" placeholder="Kode booking / nama...">
            </div>
            <select name="status" class="form-control" style="width:140px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif"      {{ request('status') == 'aktif'      ? 'selected' : '' }}>Aktif</option>
                <option value="selesai"    {{ request('status') == 'selesai'    ? 'selected' : '' }}>Selesai</option>
                <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="btn btn-ghost">Cari</button>
            @if(request()->hasAny(['search', 'status'])) 
                <a href="{{ route('faktur.index') }}" class="btn btn-ghost" style="padding: 9px 12px;"><i data-lucide="x" style="width: 14px; height: 14px;"></i></a> 
            @endif
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Pelanggan</th>
                    <th>Kamar</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Durasi</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($fakturList as $f)
                <tr>
                    <td class="td-primary"><span class="mono">{{ $f->kode_booking }}</span></td>
                    <td>{{ $f->pelanggan->nama ?? '-' }}</td>
                    <td>{{ $f->kamar->nomor_kamar ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->tanggal_checkin)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($f->tanggal_checkout)->format('d/m/Y') }}</td>
                    <td>{{ $f->durasi_malam }} malam</td>
                    <td class="money">Rp {{ number_format($f->total_harga, 0, ',', '.') }}</td>
                    <td>{{ $f->pembayaran ? ucfirst($f->pembayaran->metode) : '-' }}</td>
                    <td><span class="badge badge-{{ $f->status }}">{{ ucfirst($f->status) }}</span></td>
                    <td>
                        <a href="{{ route('faktur.show', $f) }}" class="btn btn-xs btn-primary">Lihat Faktur</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i data-lucide="file-text" class="empty-icon"></i>
                            <div class="empty-title">Belum ada faktur</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($fakturList->hasPages())
    <div class="pagination-wrap">{{ $fakturList->links('vendor.pagination.custom') }}</div>
    @endif
</div>
@endsection
