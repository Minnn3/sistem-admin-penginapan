@extends('layouts.app')
@section('title', 'Pendapatan')
@section('page-title', 'Pendapatan')

@push('styles')
<style>
/* ── Print CSS — hanya aktif saat mencetak ── */
@media print {
    @page { margin: 0; }
    body { 
        margin: 1.6cm; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important; 
    }
    
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
        border-color: #ddd !important;
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
    padding-bottom: 16px;
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
            <td style="border:none;padding:0 0 0 16px;vertical-align:middle;">
                <div style="font-size:22px;font-weight:700; letter-spacing:-0.02em;">Hocky Guest House</div>
                <div style="font-size:14px; color:#555;">Laporan Pendapatan Bulanan</div>
                <div style="font-size:14px;font-weight:600; margin-top: 4px;">
                    Periode:
                    {{ \Carbon\Carbon::create()->month((int)$bulan)->locale('id')->isoFormat('MMMM') }}
                    {{ $tahun }}
                </div>
            </td>
            <td style="border:none;padding:0;text-align:right;vertical-align:top;font-size:12px;color:#555;">
                Dicetak: {{ now()->format('d M Y, H:i') }} WIB<br>
                Total Transaksi: {{ $transaksi->count() }} transaksi<br>
                Total Pendapatan: <strong style="font-size:14px; color:#000;">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</strong>
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
        <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Cetak Laporan {{ \Carbon\Carbon::create()->month((int)$bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
    </a>
</div>

{{-- SUMMARY CARDS --}}
<div class="stats-grid no-print" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-card">
        <div class="stat-label">Hari Ini</div>
        <div class="stat-value" style="font-size:20px;">Rp {{ number_format($pendapatanHariIni, 0, ',', '.') }}</div>
        <div class="stat-accent"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Minggu Ini</div>
        <div class="stat-value" style="font-size:20px;">Rp {{ number_format($pendapatanMingguIni, 0, ',', '.') }}</div>
        <div class="stat-accent green"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Bulan Ini</div>
        <div class="stat-value" style="font-size:20px;">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</div>
        <div class="stat-accent blue"></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Tahun {{ $tahun }}</div>
        <div class="stat-value" style="font-size:20px;">Rp {{ number_format($pendapatanTahunIni, 0, ',', '.') }}</div>
        <div class="stat-accent amber"></div>
    </div>
</div>

{{-- CHART --}}
<div class="table-card chart-section no-print" style="margin-bottom:24px;">
    <div class="table-header">
        <span class="table-title" style="display:flex; align-items:center; gap:8px;">
            <i data-lucide="bar-chart-2" style="width:16px;height:16px;color:var(--text-tertiary);"></i>
            Grafik Pendapatan 12 Bulan Terakhir
        </span>
    </div>
    <div style="padding:20px;">
        <div style="position:relative;height:240px;">
            <canvas id="chartBulanan"></canvas>
        </div>
    </div>
</div>

{{-- TABEL TRANSAKSI --}}
<div class="table-card">
    <div class="table-header">
        <span class="table-title">
            Riwayat Transaksi —
            {{ \Carbon\Carbon::create()->month((int)$bulan)->locale('id')->isoFormat('MMMM') }} {{ $tahun }}
        </span>
        {{-- Filter form: sembunyi saat cetak --}}
        <form method="GET" class="filter-bar no-print">
            <select name="bulan" class="form-control" style="width:130px;" onchange="this.form.submit()">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month((int)$m)->locale('id')->isoFormat('MMMM') }}</option>
                @endforeach
            </select>
            <select name="tahun" class="form-control" style="width:100px;" onchange="this.form.submit()">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-ghost" style="display:none;">Filter</button>
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
                    <td class="td-primary"><span class="mono">{{ $t->pemesanan->kode_booking ?? '-' }}</span></td>
                    <td>{{ $t->pemesanan->pelanggan->nama ?? '-' }}</td>
                    <td>{{ $t->pemesanan->kamar->nomor_kamar ?? '-' }}</td>
                    <td><span class="badge" style="background:var(--blue-bg);color:var(--blue)">{{ ucfirst($t->metode) }}</span></td>
                    <td class="money">Rp {{ number_format($t->jumlah_bayar, 0, ',', '.') }}</td>
                    <td class="no-print">
                        @if($t->pemesanan)
                        <a href="{{ route('faktur.show', $t->pemesanan->id) }}" class="btn btn-xs btn-ghost" style="padding: 4px 6px;">
                            <i data-lucide="file-text" style="width: 14px; height: 14px;"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i data-lucide="trending-up" class="empty-icon"></i>
                            <div class="empty-title">Tidak ada transaksi pada periode ini</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            {{-- Baris total — muncul di print maupun layar --}}
            @if($transaksi->count() > 0)
            <tfoot>
                <tr style="background:var(--bg-base);">
                    <td colspan="6" style="text-align:right;font-weight:600;color:var(--text-secondary);padding:14px 16px;">
                        TOTAL PENDAPATAN
                    </td>
                    <td style="font-weight:700;font-size:15px;color:var(--primary);padding:14px 16px; letter-spacing:-0.01em;">
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

{{-- Inject data grafik ke JS di dalam @section agar variabel PHP pasti tersedia --}}
<script>
    window._grafikBulanan = @json($grafikBulanan);
</script>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const data = window._grafikBulanan;
new Chart(document.getElementById('chartBulanan').getContext('2d'), {
    type: 'line',
    data: {
        labels: data.map(d => d.label),
        datasets: [{
            label: 'Pendapatan',
            data: data.map(d => d.total),
            borderColor: '#1A1A1A', /* var(--primary) */
            backgroundColor: 'rgba(26,26,26,0.05)',
            borderWidth: 2,
            pointBackgroundColor: '#1A1A1A',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.3,
            fill: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { 
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1A1A1A',
                titleFont: { family: 'Inter', size: 13 },
                bodyFont: { family: 'Inter', size: 13 },
                padding: 10,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: { 
                grid: { color: '#E5E4E0', drawBorder: false }, 
                ticks: { color: '#737373', font: { family: 'Inter', size: 11 } } 
            },
            y: {
                grid: { color: '#E5E4E0', drawBorder: false, borderDash: [4, 4] },
                ticks: {
                    color: '#737373', font: { family: 'Inter', size: 11 },
                    callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? (v/1000).toFixed(0)+'rb' : v)
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
    }
});

// Jika mode cetak (ada ?cetak=1 di URL), langsung buka dialog print
<?php if($isCetak): ?>
window.addEventListener('load', () => window.print());
<?php endif; ?>
</script>
@endpush
