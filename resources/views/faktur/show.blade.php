@extends('layouts.app')
@section('title', 'Faktur ' . $pemesanan->kode_booking)
@section('page-title', 'Detail Faktur')

@push('styles')
<style>
@media print {
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
    <div style="display:flex;gap:8px;">
        <button onclick="window.print()" class="btn btn-primary">🖨 Cetak / PDF</button>
        <a href="{{ route('faktur.index') }}" class="btn btn-ghost">← Kembali</a>
    </div>
</div>

<div class="invoice-paper" style="max-width:760px;background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:28px 36px;display:flex;justify-content:space-between;align-items:flex-start;">
        <div style="display:flex;align-items:center;gap:14px;">
            <img src="{{ asset('images/logo.png') }}" alt="Hocky Guest House" style="width:60px;height:60px;border-radius:50%;background:#fff;padding:3px;object-fit:contain;">
            <div>
                <div style="font-size:22px;font-weight:800;color:#fff;margin-bottom:2px;">Hocky Guest House</div>
                <div style="font-size:12px;color:rgba(255,255,255,0.75);">Kwitansi Penginapan</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:22px;font-weight:700;color:#fff;">{{ $pemesanan->kode_booking }}</div>
            <div style="font-size:12px;color:rgba(255,255,255,0.75);">Tanggal: {{ \Carbon\Carbon::parse($pemesanan->created_at)->format('d F Y') }}</div>
            <span class="inv-badge badge badge-{{ $pemesanan->status }}" style="margin-top:6px;display:inline-block;">{{ ucfirst($pemesanan->status) }}</span>
        </div>
    </div>

    <div style="padding:28px 36px;">
        {{-- Info 2 kolom --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;padding-bottom:24px;border-bottom:1px solid var(--border);">
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:10px;">Data Tamu</div>
                <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">{{ $pemesanan->pelanggan->nama }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">{{ $pemesanan->pelanggan->jenis_identitas }}: {{ $pemesanan->pelanggan->no_identitas ?? '-' }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">📞 {{ $pemesanan->pelanggan->no_telepon ?? '-' }}</div>
                <div style="font-size:13px;color:var(--text-secondary);margin-top:4px;">{{ $pemesanan->pelanggan->alamat ?? '-' }}</div>
            </div>
            <div>
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:10px;">Detail Kamar</div>
                <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:6px;">Kamar No. {{ $pemesanan->kamar->nomor_kamar }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">{{ $pemesanan->kamar->nama_kamar ?? $pemesanan->kamar->tipe }}</div>
                <div style="font-size:13px;color:var(--text-secondary);">Tipe: {{ $pemesanan->kamar->tipe }}</div>
            </div>
        </div>

        {{-- Tabel rincian --}}
        <table style="width:100%;border-collapse:collapse;margin-bottom:24px;">
            <thead>
                <tr style="background:var(--bg-base);">
                    <th style="padding:10px 14px;text-align:left;font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Deskripsi</th>
                    <th style="padding:10px 14px;text-align:right;font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Qty</th>
                    <th style="padding:10px 14px;text-align:right;font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Harga Satuan</th>
                    <th style="padding:10px 14px;text-align:right;font-size:11px;text-transform:uppercase;color:var(--text-muted);font-weight:600;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:12px 14px;color:var(--text-secondary);">
                        Sewa Kamar {{ $pemesanan->kamar->nomor_kamar }} ({{ $pemesanan->kamar->tipe }})<br>
                        <small style="color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkin)->format('d M Y') }} →
                            {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkout)->format('d M Y') }}
                        </small>
                    </td>
                    <td style="padding:12px 14px;text-align:right;color:var(--text-secondary);">{{ $pemesanan->durasi_malam }} malam</td>
                    <td style="padding:12px 14px;text-align:right;color:var(--text-secondary);">Rp {{ number_format($pemesanan->harga_per_malam, 0, ',', '.') }}</td>
                    <td style="padding:12px 14px;text-align:right;font-weight:600;color:var(--text-primary);">Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Total box --}}
        <div style="display:flex;justify-content:flex-end;margin-bottom:28px;">
            <div style="background:var(--bg-base);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px 24px;min-width:280px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:var(--text-muted);">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:var(--text-muted);">
                    <span>Diskon</span>
                    <span>Rp 0</span>
                </div>
                <div style="border-top:1px solid var(--border);padding-top:10px;display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:var(--indigo-light);">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                @if($pemesanan->pembayaran)
                <div style="margin-top:8px;font-size:12px;color:var(--text-muted);">
                    Metode: {{ ucfirst($pemesanan->pembayaran->metode) }} •
                    Dibayar: {{ \Carbon\Carbon::parse($pemesanan->pembayaran->tanggal_bayar)->format('d M Y H:i') }}
                </div>
                @endif
            </div>
        </div>

        @if($pemesanan->catatan)
        <div style="background:var(--bg-base);border-radius:var(--radius-sm);padding:12px 16px;margin-bottom:24px;font-size:13px;color:var(--text-muted);">
            <strong style="color:var(--text-secondary);">Catatan:</strong> {{ $pemesanan->catatan }}
        </div>
        @endif

        {{-- Footer --}}
        <div style="border-top:1px solid var(--border);padding-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:20px;font-size:12px;color:var(--text-muted);">
            <div>
                <div style="margin-bottom:40px;">Hormat kami,</div>
                <div style="border-top:1px solid var(--border-light);padding-top:6px;color:var(--text-secondary);font-weight:600;">Hocky Guest House</div>
            </div>
            <div style="text-align:right;">
                <div style="margin-bottom:40px;">Tanda terima,</div>
                <div style="border-top:1px solid var(--border-light);padding-top:6px;color:var(--text-secondary);font-weight:600;">{{ $pemesanan->pelanggan->nama }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
