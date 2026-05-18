@extends('layouts.app')
@section('title', 'Faktur ' . $pemesanan->kode_booking)
@section('page-title', 'Detail Faktur')

@push('styles')
<style>
@media print {
    @page { margin: 0; }
    body { 
        margin: 1.6cm; 
        -webkit-print-color-adjust: exact !important; 
        print-color-adjust: exact !important; 
    }
    
    .sidebar, .topbar, .no-print { display: none !important; }
    .main-content { margin: 0 !important; }
    .content-area { padding: 0 !important; }
    .invoice-paper { box-shadow: none !important; border: none !important; max-width: 100% !important; }
    body { background: #fff !important; color: #000 !important; }
    .invoice-paper * { color: #000 !important; }
    .inv-badge { border: 1px solid #333 !important; }
}
</style>
@endpush

@section('content')
<div class="section-header no-print">
    <h2 class="section-title">Faktur #{{ $pemesanan->kode_booking }}</h2>
    <div style="display:flex;gap:12px;">
        <button onclick="window.print()" class="btn btn-primary"><i data-lucide="printer" style="width: 16px; height: 16px;"></i> Cetak / PDF</button>
        <a href="{{ route('faktur.index') }}" class="btn btn-ghost"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
    </div>
</div>

<div class="invoice-paper" style="max-width:760px;background:#ffffff;border:1px solid var(--border-light);border-radius:var(--radius-md);overflow:hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

    {{-- Header --}}
    <div style="background:var(--primary);padding:32px 40px;display:flex;justify-content:space-between;align-items:flex-start;">
        <div style="display:flex;align-items:center;gap:16px;">
            <img src="{{ asset('images/logo.png') }}" alt="Hocky Guest House" style="width:56px;height:56px;border-radius:50%;background:#fff;padding:2px;object-fit:contain;">
            <div>
                <div style="font-size:22px;font-weight:700;color:var(--primary-fg);letter-spacing:-0.02em;margin-bottom:2px;">Hocky Guest House</div>
                <div style="font-size:13px;color:rgba(255,255,255,0.85); font-weight: 500;">Kwitansi Penginapan</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:20px;font-weight:700;color:var(--primary-fg); font-family: 'JetBrains Mono', monospace;">{{ $pemesanan->kode_booking }}</div>
            <div style="font-size:13px;color:rgba(255,255,255,0.85); margin-top: 4px;">Tanggal: {{ \Carbon\Carbon::parse($pemesanan->created_at)->format('d F Y') }}</div>
            <span class="inv-badge badge badge-{{ $pemesanan->status }}" style="margin-top:8px;display:inline-block;">{{ ucfirst($pemesanan->status) }}</span>
        </div>
    </div>

    <div style="padding:32px 40px;">
        {{-- Info 2 kolom --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px;padding-bottom:28px;border-bottom:1px solid var(--border-light);">
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:12px; font-weight: 600;">Data Tamu</div>
                <div style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:8px;">{{ $pemesanan->pelanggan->nama }}</div>
                <div style="font-size:13px;color:var(--text-secondary); margin-bottom:4px;">{{ $pemesanan->pelanggan->jenis_identitas }}: {{ $pemesanan->pelanggan->no_identitas ?? '-' }}</div>
                <div style="font-size:13px;color:var(--text-secondary); margin-bottom:4px;">Telepon: {{ $pemesanan->pelanggan->no_telepon ?? '-' }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">{{ $pemesanan->pelanggan->alamat ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:12px; font-weight: 600;">Detail Kamar</div>
                <div style="font-size:16px;font-weight:600;color:var(--text-primary);margin-bottom:8px;">Kamar No. {{ $pemesanan->kamar->nomor_kamar }}</div>
                <div style="font-size:13px;color:var(--text-secondary); margin-bottom:4px;">{{ $pemesanan->kamar->nama_kamar ?? $pemesanan->kamar->tipe }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">Tipe: {{ $pemesanan->kamar->tipe }}</div>
            </div>
        </div>

        {{-- Tabel rincian --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:32px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-light);">
                    <th style="padding:12px 14px 12px 0;text-align:left;font-size:12px;color:var(--text-tertiary);font-weight:500;">Deskripsi</th>
                    <th style="padding:12px 14px;text-align:right;font-size:12px;color:var(--text-tertiary);font-weight:500;">Qty</th>
                    <th style="padding:12px 14px;text-align:right;font-size:12px;color:var(--text-tertiary);font-weight:500;">Harga Satuan</th>
                    <th style="padding:12px 0 12px 14px;text-align:right;font-size:12px;color:var(--text-tertiary);font-weight:500;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border-light);">
                    <td style="padding:16px 14px 16px 0;color:var(--text-primary);">
                        Sewa Kamar {{ $pemesanan->kamar->nomor_kamar }} ({{ $pemesanan->kamar->tipe }})<br>
                        <small style="color:var(--text-tertiary); display:block; margin-top: 4px;">
                            {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkin)->format('d M Y') }} — 
                            {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkout)->format('d M Y') }}
                        </small>
                    </td>
                    <td style="padding:16px 14px;text-align:right;color:var(--text-secondary);">{{ $pemesanan->durasi_malam }} malam</td>
                    <td style="padding:16px 14px;text-align:right;color:var(--text-secondary);">Rp {{ number_format($pemesanan->harga_per_malam, 0, ',', '.') }}</td>
                    <td style="padding:16px 0 16px 14px;text-align:right;font-weight:600;color:var(--text-primary);">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Total box --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:36px;">
            <div style="background:var(--bg-base);border:1px solid var(--border-light);border-radius:var(--radius-sm);padding:20px 24px;min-width:320px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:14px;color:var(--text-secondary);">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:14px;color:var(--text-secondary);">
                    <span>Diskon</span>
                    <span>Rp 0</span>
                </div>
                <div style="border-top:1px dashed var(--border);padding-top:16px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:14px; font-weight:600; color:var(--text-primary);">TOTAL</span>
                    <span style="font-size:22px;font-weight:700;color:var(--primary);letter-spacing:-0.02em;">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                @if($pemesanan->pembayaran)
                <div style="margin-top:16px;font-size:12px;color:var(--text-tertiary);background: #fff; padding: 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
                    <div style="margin-bottom: 4px;"><strong>Metode:</strong> {{ ucfirst($pemesanan->pembayaran->metode) }}</div>
                    <div><strong>Dibayar:</strong> {{ \Carbon\Carbon::parse($pemesanan->pembayaran->tanggal_bayar)->format('d M Y, H:i') }} WIB</div>
                </div>
                @endif
            </div>
        </div>

        @if($pemesanan->catatan)
        <div style="background:var(--bg-base);border: 1px solid var(--border-light); border-radius:var(--radius-sm);padding:14px 18px;margin-bottom:32px;font-size:13px;color:var(--text-secondary);">
            <strong style="color:var(--text-primary); display:block; margin-bottom:4px;">Catatan:</strong> {{ $pemesanan->catatan }}
        </div>
        @endif

        {{-- Footer --}}
        <div style="border-top:1px solid var(--border-light);padding-top:24px;display:grid;grid-template-columns:1fr 1fr;gap:20px;font-size:13px;color:var(--text-secondary);">
            <div>
                <div style="margin-bottom:48px;">Hormat kami,</div>
                <div style="border-top:1px solid var(--border);padding-top:8px;color:var(--text-primary);font-weight:600; display:inline-block; min-width:140px;">Hocky Guest House</div>
            </div>
            <div style="text-align:right;">
                <div style="margin-bottom:48px;">Tanda terima,</div>
                <div style="border-top:1px solid var(--border);padding-top:8px;color:var(--text-primary);font-weight:600; display:inline-block; min-width:140px;">{{ $pemesanan->pelanggan->nama }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
