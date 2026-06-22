@extends('layouts.app')
@section('title', 'Faktur ' . $pemesanan->kode_booking)
@section('page-title', 'Detail Faktur')

@push('styles')
    <style>
        /* ── Print CSS ── */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm 1.2cm;
            }

            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                font-size: 12px !important;
            }

            .sidebar,
            .topbar,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
            }

            .content-area {
                padding: 0 !important;
            }

            /* Invoice container — fill page, no shadow/border */
            .invoice-paper {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                max-width: 100% !important;
                overflow: visible !important;
            }

            /* Force all text to black */
            .invoice-paper * {
                color: #000 !important;
            }

            /* Badge keep a border so it reads clearly */
            .inv-badge {
                border: 1px solid #333 !important;
                background: transparent !important;
                color: #000 !important;
            }

            /* Remove any element that might add height */
            .inv-header-bg {
                background: transparent !important;
                padding: 16px 0 12px !important;
                border-bottom: 2px solid #000 !important;
            }

            /* Tighten spacing */
            .inv-info-grid {
                margin-bottom: 14px !important;
                padding-bottom: 14px !important;
            }

            .inv-table {
                margin-bottom: 14px !important;
            }

            .inv-total-box {
                margin-bottom: 16px !important;
            }

            .inv-footer {
                padding-top: 14px !important;
            }

            .inv-sign-space {
                margin-bottom: 28px !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="section-header no-print">
        <h2 class="section-title">Faktur #{{ $pemesanan->kode_booking }}</h2>
        <div style="display:flex;gap:12px;">
            <button onclick="window.print()" class="btn btn-primary"><i data-lucide="printer"
                    style="width: 16px; height: 16px;"></i> Cetak / PDF</button>
            <a href="{{ route('faktur.index') }}" class="btn btn-ghost"><i data-lucide="arrow-left"
                    style="width: 16px; height: 16px;"></i> Kembali</a>
        </div>
    </div>

    {{-- Invoice Paper --}}
    <div class="invoice-paper"
        style="max-width:760px; background:#ffffff; border:1px solid var(--border); border-radius:var(--radius-md); overflow:hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

        {{-- Header — clean border-bottom style (DESIGN.md §10) --}}
        <div class="inv-header-bg"
            style="padding:24px 36px 20px; border-bottom:2px solid var(--primary); display:flex; justify-content:space-between; align-items:flex-start;">
            <div style="display:flex; align-items:center; gap:14px;">
                <img src="{{ asset('images/logo.png') }}" alt="Hocky Guest House"
                    style="width:48px; height:48px; border-radius:50%; object-fit:contain;">
                <div>
                    <div
                        style="font-size:20px; font-weight:700; color:var(--text-primary); letter-spacing:-0.02em; line-height:1.2;">
                        Hocky Guest House</div>
                    <div style="font-size:12px; color:var(--text-tertiary); font-weight:500; margin-top:2px;">Faktur
                        Penginapan</div>
                </div>
            </div>
            <div style="text-align:right;">
                <div
                    style="font-size:18px; font-weight:700; color:var(--text-primary); font-family:'JetBrains Mono',monospace;">
                    {{ $pemesanan->kode_booking }}
                </div>
                <div style="font-size:12px; color:var(--text-secondary); margin-top:3px;">Tanggal:
                    {{ \Carbon\Carbon::parse($pemesanan->created_at)->format('d F Y') }}
                </div>
                <span class="inv-badge badge badge-{{ $pemesanan->status }}"
                    style="margin-top:6px; display:inline-block;">{{ ucfirst($pemesanan->status) }}</span>
            </div>
        </div>

        <div style="padding:20px 36px 24px;">

            {{-- Info 2 kolom --}}
            <div class="inv-info-grid"
                style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; padding-bottom:18px; border-bottom:1px solid var(--border);">
                <div>
                    <div
                        style="font-size:10px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-tertiary); margin-bottom:8px; font-weight:600;">
                        Data Tamu</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:4px;">
                        {{ $pemesanan->pelanggan->nama }}
                    </div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-bottom:2px;">
                        {{ $pemesanan->pelanggan->jenis_identitas }}: {{ $pemesanan->pelanggan->no_identitas ?? '-' }}
                    </div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-bottom:2px;">Telepon:
                        {{ $pemesanan->pelanggan->no_telepon ?? '-' }}
                    </div>
                    <div style="font-size:12px; color:var(--text-secondary);">{{ $pemesanan->pelanggan->alamat ?? '-' }}
                    </div>
                </div>
                <div>
                    <div
                        style="font-size:10px; text-transform:uppercase; letter-spacing:.06em; color:var(--text-tertiary); margin-bottom:8px; font-weight:600;">
                        Detail Kamar</div>
                    <div style="font-size:14px; font-weight:600; color:var(--text-primary); margin-bottom:4px;">Kamar No.
                        {{ $pemesanan->kamar->nomor_kamar }}
                    </div>
                    <div style="font-size:12px; color:var(--text-secondary); margin-bottom:2px;">
                        {{ $pemesanan->kamar->nama_kamar ?? $pemesanan->kamar->tipe }}
                    </div>
                    <div style="font-size:12px; color:var(--text-secondary);">Tipe: {{ $pemesanan->kamar->tipe }}</div>
                </div>
            </div>

            {{-- Tabel rincian --}}
            <table class="inv-table" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);">
                        <th
                            style="padding:8px 12px 8px 0; text-align:left; font-size:11px; color:var(--text-tertiary); font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                            Deskripsi</th>
                        <th
                            style="padding:8px 12px; text-align:right; font-size:11px; color:var(--text-tertiary); font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                            Qty</th>
                        <th
                            style="padding:8px 12px; text-align:right; font-size:11px; color:var(--text-tertiary); font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                            Harga/Malam</th>
                        <th
                            style="padding:8px 0 8px 12px; text-align:right; font-size:11px; color:var(--text-tertiary); font-weight:600; text-transform:uppercase; letter-spacing:.04em;">
                            Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:12px 12px 12px 0; color:var(--text-primary); font-size:13px;">
                            Sewa Kamar {{ $pemesanan->kamar->nomor_kamar }} ({{ $pemesanan->kamar->tipe }})<br>
                            <small style="color:var(--text-tertiary); display:block; margin-top:3px; font-size:11px;">
                                {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkin)->format('d M Y') }} —
                                {{ \Carbon\Carbon::parse($pemesanan->tanggal_checkout)->format('d M Y') }}
                            </small>
                        </td>
                        <td style="padding:12px; text-align:right; color:var(--text-secondary); font-size:13px;">
                            {{ $pemesanan->durasi_malam }} malam
                        </td>
                        <td style="padding:12px; text-align:right; color:var(--text-secondary); font-size:13px;">Rp
                            {{ number_format($pemesanan->harga_per_malam, 0, ',', '.') }}
                        </td>
                        <td
                            style="padding:12px 0 12px 12px; text-align:right; font-weight:600; color:var(--text-primary); font-size:13px;">
                            Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Total box --}}
            <div class="inv-total-box" style="display:flex; justify-content:flex-end; margin-bottom:20px;">
                <div
                    style="background:var(--bg-base); border:1px solid var(--border); border-radius:var(--radius-sm); padding:16px 20px; min-width:300px;">
                    <div
                        style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:13px; color:var(--text-secondary);">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                    <div
                        style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:13px; color:var(--text-secondary);">
                        <span>Diskon</span>
                        <span>Rp 0</span>
                    </div>
                    <div
                        style="border-top:1px dashed var(--border); padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:13px; font-weight:600; color:var(--text-primary);">TOTAL</span>
                        <span style="font-size:20px; font-weight:700; color:var(--primary); letter-spacing:-0.02em;">Rp
                            {{ number_format($pemesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                    @if($pemesanan->pembayaran)
                        <div
                            style="margin-top:12px; font-size:11px; color:var(--text-tertiary); background:#fff; padding:8px 10px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                            <div style="margin-bottom:2px;"><strong>Metode:</strong>
                                Tunai</div>
                            <div style="margin-bottom:2px;"><strong>Deposit:</strong>
                                {{ $pemesanan->deposit_label }}</div>
                            <div><strong>Dibayar:</strong>
                                {{ \Carbon\Carbon::parse($pemesanan->pembayaran->tanggal_bayar)->format('d M Y, H:i') }} WIB
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($pemesanan->catatan)
                <div
                    style="background:var(--bg-base); border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px 14px; margin-bottom:18px; font-size:12px; color:var(--text-secondary);">
                    <strong style="color:var(--text-primary); display:block; margin-bottom:3px;">Catatan:</strong>
                    {{ $pemesanan->catatan }}
                </div>
            @endif

            {{-- Footer tanda tangan --}}
            <div class="inv-footer"
                style="border-top:1px solid var(--border); padding-top:18px; display:grid; grid-template-columns:1fr 1fr; gap:20px; font-size:12px; color:var(--text-secondary);">
                <div>
                    <div class="inv-sign-space" style="margin-bottom:36px;">Hormat kami,</div>
                    <div
                        style="border-top:1px solid var(--border); padding-top:6px; color:var(--text-primary); font-weight:600; display:inline-block; min-width:140px;">
                        Hocky Guest House</div>
                </div>
                <div style="text-align:right;">
                    <div class="inv-sign-space" style="margin-bottom:36px;">Tanda terima,</div>
                    <div
                        style="border-top:1px solid var(--border); padding-top:6px; color:var(--text-primary); font-weight:600; display:inline-block; min-width:140px;">
                        {{ $pemesanan->pelanggan->nama }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection