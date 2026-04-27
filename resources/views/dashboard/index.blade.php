@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
.chart-wrap { padding: 20px; }
.chart-container { position: relative; height: 180px; }
</style>
@endpush

@section('content')

{{-- STATS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon indigo">🛏</div>
        <div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Kamar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">✓</div>
        <div>
            <div class="stat-value" style="color:var(--green)">{{ $stats['tersedia'] }}</div>
            <div class="stat-label">Tersedia</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">👥</div>
        <div>
            <div class="stat-value" style="color:var(--red)">{{ $stats['terisi'] }}</div>
            <div class="stat-label">Terisi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">🧹</div>
        <div>
            <div class="stat-value" style="color:var(--yellow)">{{ $stats['kotor'] }}</div>
            <div class="stat-label">Perlu Dibersihkan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon indigo">💰</div>
        <div>
            <div class="stat-value" style="font-size:17px;">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan Hari Ini</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;" class="dashboard-cols">

    {{-- ROOM GRID --}}
    <div>
        <div class="section-header">
            <h2 class="section-title">Status Kamar</h2>
            <div style="display:flex;gap:10px;font-size:12px;color:var(--text-muted);align-items:center;">
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:var(--green);border-radius:50%;display:inline-block;"></span>Tersedia</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:var(--red);border-radius:50%;display:inline-block;"></span>Terisi</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="width:10px;height:10px;background:var(--yellow);border-radius:50%;display:inline-block;"></span>Kotor</span>
            </div>
        </div>

        @if($kamarList->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🛏</div>
                <div class="empty-title">Belum ada kamar</div>
                <div class="empty-desc"><a href="{{ route('kamar.create') }}" style="color:var(--indigo-light)">Tambah kamar pertama</a></div>
            </div>
        @else
        <div class="room-grid">
            @foreach($kamarList as $kamar)
            <div class="room-card {{ $kamar->status }}">
                <div class="room-header">
                    <div>
                        <div class="room-number">No. {{ $kamar->nomor_kamar }}</div>
                        <div class="room-type">{{ $kamar->nama_kamar ?? $kamar->tipe }}</div>
                    </div>
                    <span class="badge badge-{{ $kamar->status }}">
                        {{ $kamar->status_label }}
                    </span>
                </div>

                <div class="room-price">Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}/malam</div>

                @if($kamar->status === 'terisi' && $kamar->pemesananAktif)
                    @php $pem = $kamar->pemesananAktif @endphp
                    <div class="room-guest">👤 {{ $pem->pelanggan->nama }}</div>
                    <div class="room-checkout">Check-out: {{ \Carbon\Carbon::parse($pem->tanggal_checkout)->format('d M Y') }}</div>
                    <div class="room-checkout" style="margin-top:2px;">Kode: {{ $pem->kode_booking }}</div>
                    <div class="room-actions">
                        <form action="{{ route('pemesanan.checkout', $pem->id) }}" method="POST" onsubmit="return confirm('Lakukan checkout untuk kamar {{ $kamar->nomor_kamar }}?')">
                            @csrf
                            <button class="btn btn-sm btn-danger">⬆ Check-Out</button>
                        </form>
                        <a href="{{ route('faktur.show', $pem->id) }}" class="btn btn-sm btn-ghost">🧾 Faktur</a>
                    </div>

                @elseif($kamar->status === 'kotor')
                    <div class="room-guest" style="color:var(--yellow);">🧹 Perlu dibersihkan</div>
                    <div class="room-actions">
                        <form action="{{ route('kamar.ubah-status', $kamar->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="tersedia">
                            <button class="btn btn-sm btn-success">✓ Tandai Bersih</button>
                        </form>
                    </div>

                @else
                    <div class="room-guest" style="color:var(--green);">✓ Siap digunakan</div>
                    <div class="room-actions">
                        <a href="{{ route('pemesanan.create', ['kamar_id' => $kamar->id]) }}" class="btn btn-sm btn-primary">⬇ Check-In</a>
                    </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Quick Actions --}}
    <div>
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">⚡ Aksi Cepat</span>
            </div>
            <div style="padding:16px;display:flex;flex-direction:column;gap:8px;">
                <a href="{{ route('pemesanan.create') }}" class="btn btn-primary btn-full">✚ Check-In Tamu Baru</a>
                <a href="{{ route('kamar.create') }}" class="btn btn-ghost btn-full">🛏 Tambah Kamar</a>
                <a href="{{ route('pelanggan.create') }}" class="btn btn-ghost btn-full">👤 Tambah Pelanggan</a>
                <a href="{{ route('faktur.index') }}" class="btn btn-ghost btn-full">🧾 Lihat Faktur</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media(max-width:900px){
    .dashboard-cols { grid-template-columns: 1fr !important; }
}
</style>
@endpush
