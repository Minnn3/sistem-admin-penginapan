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
        <div class="stat-label">Total Kamar</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-accent"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tersedia</div>
        <div class="stat-value">{{ $stats['tersedia'] }}</div>
        <div class="stat-accent green"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Terisi</div>
        <div class="stat-value">{{ $stats['terisi'] }}</div>
        <div class="stat-accent red"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Perlu Dibersihkan</div>
        <div class="stat-value">{{ $stats['kotor'] }}</div>
        <div class="stat-accent amber"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Pendapatan Hari Ini</div>
        <div class="stat-value" style="font-size: 20px;">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</div>
        <div class="stat-accent blue"></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;" class="dashboard-2col">

    {{-- ROOM GRID --}}
    <div>
        <div class="section-header">
            <h2 class="section-title">Status Kamar</h2>
            <div style="display:flex;gap:12px;font-size:12px;color:var(--text-tertiary);align-items:center;">
                <span style="display:flex;align-items:center;gap:6px;"><span class="room-status-dot tersedia"></span>Tersedia</span>
                <span style="display:flex;align-items:center;gap:6px;"><span class="room-status-dot terisi"></span>Terisi</span>
                <span style="display:flex;align-items:center;gap:6px;"><span class="room-status-dot kotor"></span>Kotor</span>
            </div>
        </div>

        @if($kamarList->isEmpty())
            <div class="empty-state">
                <i data-lucide="bed-double" class="empty-icon"></i>
                <div class="empty-title">Belum ada kamar</div>
                <div class="empty-desc"><a href="{{ route('kamar.create') }}" style="color:var(--accent); font-weight: 500;">Tambah kamar pertama</a></div>
            </div>
        @else
        <div class="room-grid">
            @foreach($kamarList as $kamar)
            <div class="room-card">
                <div class="room-header">
                    <span class="room-status-dot {{ $kamar->status }}"></span>
                    <div class="room-number">No. {{ $kamar->nomor_kamar }}</div>
                </div>
                
                <div class="room-type">{{ $kamar->nama_kamar ?? $kamar->tipe }}</div>
                <div class="room-price">Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}/malam</div>

                @if($kamar->status === 'terisi' && $kamar->pemesananAktif)
                    @php $pem = $kamar->pemesananAktif @endphp
                    <div class="room-info"><i data-lucide="user" style="width: 14px; height: 14px; color: var(--text-tertiary);"></i> {{ $pem->pelanggan->nama }}</div>
                    <div class="room-type" style="margin-top:0;">Out: {{ \Carbon\Carbon::parse($pem->tanggal_checkout)->format('d M Y') }} • Kode: <span class="mono">{{ $pem->kode_booking }}</span></div>
                    <div class="room-actions">
                        <form action="{{ route('pemesanan.checkout', $pem->id) }}" method="POST" onsubmit="return confirm('Lakukan checkout untuk kamar {{ $kamar->nomor_kamar }}?')">
                            @csrf
                            <button class="btn btn-sm btn-danger btn-full">Check-Out</button>
                        </form>
                    </div>

                @elseif($kamar->status === 'kotor')
                    <div class="room-info" style="color:var(--amber);"><i data-lucide="spray-can" style="width: 14px; height: 14px;"></i> Perlu dibersihkan</div>
                    <div class="room-actions">
                        <form action="{{ route('kamar.ubah-status', $kamar->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="tersedia">
                            <button class="btn btn-sm btn-success btn-full">Tandai Bersih</button>
                        </form>
                    </div>

                @else
                    <div class="room-info" style="color:var(--green);"><i data-lucide="check" style="width: 14px; height: 14px;"></i> Siap digunakan</div>
                    <div class="room-actions">
                        <a href="{{ route('pemesanan.create', ['kamar_id' => $kamar->id]) }}" class="btn btn-sm btn-primary btn-full">Check-In</a>
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
                <span class="table-title">Aksi Cepat</span>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:12px;">
                <a href="{{ route('pemesanan.create') }}" class="btn btn-primary btn-full">Check-In Tamu Baru</a>
                <a href="{{ route('kamar.create') }}" class="btn btn-ghost btn-full">Tambah Kamar</a>
                <a href="{{ route('pelanggan.create') }}" class="btn btn-ghost btn-full">Tambah Pelanggan</a>
                <a href="{{ route('faktur.index') }}" class="btn btn-ghost btn-full">Lihat Faktur</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@media(max-width:900px){
    .dashboard-2col { grid-template-columns: 1fr !important; }
}
</style>
@endpush
