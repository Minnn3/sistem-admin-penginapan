@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- STATS GRID --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Total Kamar Aktif</div>
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

{{-- STATUS KAMAR (full-width, no more 2-col layout) --}}
<div>
    <div class="section-header" style="margin-top: 8px;">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <h2 class="section-title" style="margin:0;">Status Kamar</h2>
            <div style="display:flex;gap:12px;font-size:12px;color:var(--text-tertiary);align-items:center;">
                <span style="display:flex;align-items:center;gap:5px;"><span class="room-status-dot tersedia"></span>Tersedia</span>
                <span style="display:flex;align-items:center;gap:5px;"><span class="room-status-dot terisi"></span>Terisi</span>
                <span style="display:flex;align-items:center;gap:5px;"><span class="room-status-dot kotor"></span>Kotor</span>
                <span style="display:flex;align-items:center;gap:5px;"><span class="room-status-dot nonaktif"></span>Nonaktif</span>
            </div>
        </div>

        {{-- Filter kamar --}}
        <form method="GET" style="display:flex;align-items:center;gap:8px;">
            <select name="filter" class="form-control" style="width:180px;" onchange="this.form.submit()">
                <option value="aktif"    {{ $filterDashboard === 'aktif'    ? 'selected' : '' }}>Kamar Aktif</option>
                <option value="semua"    {{ $filterDashboard === 'semua'    ? 'selected' : '' }}>Semua Kamar</option>
                <option value="nonaktif" {{ $filterDashboard === 'nonaktif' ? 'selected' : '' }}>Kamar Nonaktif</option>
            </select>
        </form>
    </div>

    @if($kamarList->isEmpty())
        <div class="empty-state">
            <i data-lucide="bed-double" class="empty-icon"></i>
            <div class="empty-title">Tidak ada kamar untuk ditampilkan</div>
            <div class="empty-desc"><a href="{{ route('kamar.create') }}" style="color:var(--accent);font-weight:500;">Tambah kamar pertama</a></div>
        </div>
    @else
    <div class="room-grid">
        @foreach($kamarList as $kamar)
        <div class="room-card {{ !$kamar->is_aktif ? 'room-card-nonaktif' : '' }}">
            <div class="room-header">
                <span class="room-status-dot {{ $kamar->is_aktif ? $kamar->status : 'nonaktif' }}"></span>
                <div class="room-number">No. {{ $kamar->nomor_kamar }}</div>
                @if(!$kamar->is_aktif)
                    <span class="badge badge-nonaktif" style="margin-left:auto;font-size:10px;">Nonaktif</span>
                @endif
            </div>

            <div class="room-type">{{ $kamar->nama_kamar ?? $kamar->tipe }}</div>
            <div class="room-price">Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}/malam</div>

            @if(!$kamar->is_aktif)
                {{-- Kamar nonaktif: tampilkan info saja, tidak ada aksi --}}
                <div class="room-info" style="color:var(--text-tertiary);">
                    <i data-lucide="wrench" style="width:14px;height:14px;"></i> Nonaktif / Maintenance
                </div>

            @elseif($kamar->status === 'terisi' && $kamar->pemesananAktif)
                {{-- Kamar terisi: info tamu + badge deposit --}}
                @php $pem = $kamar->pemesananAktif @endphp
                <div class="room-info"><i data-lucide="user" style="width:14px;height:14px;color:var(--text-tertiary);"></i> {{ $pem->pelanggan->nama }}</div>
                <div class="room-type" style="margin-top:0;">Out: {{ \Carbon\Carbon::parse($pem->tanggal_checkout)->format('d M Y') }}</div>
                {{-- Badge deposit --}}
                <div style="margin-top:8px;">
                    <span class="badge {{ $pem->deposit_badge }}" style="font-size:10px;">
                        Deposit: {{ $pem->deposit_label }}
                    </span>
                </div>
                {{-- Tombol tandai bersih setelah checkout (dari sini bukan checkout) --}}
                <div class="room-actions" style="margin-top:10px;">
                    <a href="{{ route('transaksi.index') }}" class="btn btn-sm btn-ghost btn-full">Lihat di Transaksi</a>
                </div>

            @elseif($kamar->status === 'kotor')
                <div class="room-info" style="color:var(--amber);"><i data-lucide="spray-can" style="width:14px;height:14px;"></i> Perlu dibersihkan</div>
                <div class="room-actions">
                    <form action="{{ route('kamar.ubah-status', $kamar->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="tersedia">
                        <button class="btn btn-sm btn-success btn-full">Tandai Bersih</button>
                    </form>
                </div>

            @else
                <div class="room-info" style="color:var(--green);"><i data-lucide="check" style="width:14px;height:14px;"></i> Siap digunakan</div>
                <div class="room-actions">
                    <a href="{{ route('transaksi.create', ['kamar_id' => $kamar->id]) }}" class="btn btn-sm btn-primary btn-full">Check-In</a>
                </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
/* Kamar nonaktif di dashboard: tampil muted */
.room-card-nonaktif {
    opacity: 0.6;
    background: var(--bg-elevated);
}
.room-card-nonaktif .room-number,
.room-card-nonaktif .room-price {
    color: var(--text-tertiary);
}

/* Dot status untuk kamar nonaktif */
.room-status-dot.nonaktif {
    background: var(--border-hover, #D4D3CF);
}

/* Badge deposit tunai (hijau) vs dokumen (amber) */
.badge-deposit-tunai {
    background: var(--green-bg);
    color: var(--green);
}
.badge-deposit-dok {
    background: var(--amber-bg);
    color: var(--amber);
}

@media(max-width:900px){
    .dashboard-2col { grid-template-columns: 1fr !important; }
}
</style>
@endpush
