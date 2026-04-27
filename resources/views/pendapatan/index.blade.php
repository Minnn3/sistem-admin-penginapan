@extends('layouts.app')
@section('title', 'Pendapatan')
@section('page-title', 'Pendapatan')

@push('styles')
<style>
/* ── Print CSS — hanya aktif saat mencetak ── */
@media print {
    /* Sembunyikan elemen navigasi & UI */
    .sidebar, .topbar, .no-print,
    .stats-grid, .table-card .table-header form,
    .pagination-wrap, .chart-section { display: none !important; }

    /* Hilangkan margin kiri dari sidebar */
    .main-content { margin: 0 !important; }
    .content-area { padding: 12px !important; }

    /* Latar belakang putih, teks hitam agar tinta hemat */
    body, .table-card, table, th, td {
        background: #fff !important;
        color: #000 !important;
        border-color: #ccc !important;
    }

    /* Tampilkan header print yang disembunyikan di layar */
    .print-header { display: block !important; }

    /* Pastikan tabel tidak terpotong antar halaman */
    tr { page-break-inside: avoid; }

    /* Tombol faktur tidak perlu muncul di print */
    .btn-xs { display: none !important; }

    /* Badge metode agar tetap terbaca */
    .badge { border: 1px solid #999 !important; background: transparent !important; color: #000 !important; }

    /* Total di bawah tabel */
    .print-total { display: block !important; }
}

/* Header print — disembunyikan di layar, muncul saat print */
.print-header {
    display: none;
    margin-bottom: 24px;
    border-bottom: 2px solid #333;
    padding-bottom: 12px;
}
.print-total { display: none; }
</style>
@endpush

@section('content')

{{-- ── PRINT HEADER (hanya muncul saat cetak) ── --}}
<div class="print-header">
    <table style="width:100%;border:none;">
        <tr>
            <td style="border:none;padding:0;vertical-align:middle;width:70px;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:64px;height:64px;object-fit:contain;">
            </td>
            <td style="border:none;padding:0 0 0 12px;vertical-align:middle;">
                <div style="font-size:20px;font-weight:800;">Hocky Guest House</div>
                <div style="font-size:13px;">Laporan Pendapatan Bulanan</div>
                <div style="font-size:13px;font-weight:700;">
                    Periode:
                    {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }}
                    {{ $tahun }}
                </div>
            </td>
            <td style="border:none;padding:0;text-align:right;vertical-align:top;font-size:11px;color:#555;">
                Dicetak: {{ now()->format('d M Y, H:i') }} WIB<br>
                Total Transaksi: {{ $transaksi->count() }} transaksi<br>
                Total Pendapatan: <strong>Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</strong>
            </td>
        </tr>
    </table>
</div>

{{-- ── TOMBOL CETAK (hanya muncul di layar, sembunyi saat print) ── --}}
<div class="section-header no-print">
    <h2 class="section-title">Laporan Pendapatan</h2>
    <a href="{{ route('pendapatan.index', ['bulan' => $bulan, 'tahun' => $tahun, 'cetak' => 1]) }}"
       onclick="window.setTimeout(() => window.print(), 400); return false;"
       class="btn btn-primary" id="btnCetak">
        🖨 Cetak Laporan {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
    </a>
</div>

{{-- SUMMARY CARDS --}}
<div class="stats-grid no-print" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-card">
        <div class="stat-icon green">📅</div>
        <div>
            <div class="stat-value" style="font-size:16px;">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</div>
            <div class="stat-label">Hari Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">📆</div>
        <div>
            <div class="stat-value" style="font-size:16px;">Rp {{ number_format($pendapatanMingguIni, 0, ',', '.') }}</div>
            <div class="stat-label">Minggu Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon indigo">🗓</div>
        <div>
            <div class="stat-value" style="font-size:16px;">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
            <div class="stat-label">Bulan Ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">💰</div>
        <div>
            <div class="stat-value" style="font-size:16px;">Rp {{ number_format($pendapatanTahunIni, 0, ',', '.') }}</div>
            <div class="stat-label">Tahun {{ $tahun }}</div>
        </div>
    </div>
</div>

{{-- CHART --}}
<div class="table-card chart-section no-print" style="margin-bottom:24px;">
    <div class="table-header">
        <span class="table-title">📊 Grafik Pendapatan 12 Bulan Terakhir</span>
    </div>
    <div style="padding:20px;">
        <div style="position:relative;height:220px;">
            <canvas id="chartBulanan"></canvas>
        </div>
    </div>
</div>

{{-- TABEL TRANSAKSI --}}
<div class="table-card">
    <div class="table-header">
        <span class="table-title">
            Riwayat Transaksi —
            {{ \Carbon\Carbon::create()->month($bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
        </span>
        {{-- Filter form: sembunyi saat cetak --}}
        <form method="GET" class="filter-bar no-print">
            <select name="bulan" class="form-control" style="width:130px;">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}</option>
                @endforeach
            </select>
            <select name="tahun" class="form-control" style="width:100px;">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost">Filter</button>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Booking</th>
                    <th>Pelanggan</th>
                    <th>Kamar</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th class="no-print"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $i => $t)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal_bayar)->format('d/m/Y H:i') }}</td>
                    <td class="td-primary">{{ $t->pemesanan->kode_booking ?? '-' }}</td>
                    <td>{{ $t->pemesanan->pelanggan->nama ?? '-' }}</td>
                    <td>{{ $t->pemesanan->kamar->nomor_kamar ?? '-' }}</td>
                    <td><span class="badge" style="background:var(--blue-bg);color:var(--blue)">{{ ucfirst($t->metode) }}</span></td>
                    <td class="money">Rp {{ number_format($t->jumlah_bayar, 0, ',', '.') }}</td>
                    <td class="no-print">
                        @if($t->pemesanan)
                        <a href="{{ route('faktur.show', $t->pemesanan->id) }}" class="btn btn-xs btn-ghost">🧾</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state"><div class="empty-icon">💰</div><div class="empty-title">Tidak ada transaksi pada periode ini</div></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            {{-- Baris total — muncul di print maupun layar --}}
            @if($transaksi->count() > 0)
            <tfoot>
                <tr style="background:var(--bg-base);">
                    <td colspan="6" style="text-align:right;font-weight:700;color:var(--text-primary);padding:12px 16px;">
                        TOTAL PENDAPATAN
                    </td>
                    <td style="font-weight:800;font-size:15px;color:var(--indigo-light);padding:12px 16px;">
                        Rp {{ number_format($transaksi->sum('jumlah_bayar'), 0, ',', '.') }}
                    </td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if(!$isCetak && $transaksi->hasPages())
    <div class="pagination-wrap no-print">{{ $transaksi->links('vendor.pagination.custom') }}</div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const data = <?php echo json_encode($grafikBulanan); ?>;
new Chart(document.getElementById('chartBulanan').getContext('2d'), {
    type: 'line',
    data: {
        labels: data.map(d => d.label),
        datasets: [{
            label: 'Pendapatan',
            data: data.map(d => d.total),
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            borderWidth: 2,
            pointBackgroundColor: '#6366f1',
            pointRadius: 4,
            tension: 0.4,
            fill: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: '#2e3350' }, ticks: { color: '#64748b', font: { size: 10 } } },
            y: {
                grid: { color: '#2e3350' },
                ticks: {
                    color: '#64748b', font: { size: 10 },
                    callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? (v/1000).toFixed(0)+'rb' : v)
                }
            }
        }
    }
});

// Jika mode cetak (ada ?cetak=1 di URL), langsung buka dialog print
<?php if($isCetak): ?>
window.addEventListener('load', () => window.print());
<?php endif; ?>
</script>
@endpush
