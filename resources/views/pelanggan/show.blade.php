@extends('layouts.app')
@section('title', 'Riwayat Pelanggan')
@section('page-title', 'Riwayat Pelanggan')

@section('content')
<div class="section-header">
    <h2 class="section-title">Detail Pelanggan</h2>
    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">
    <div class="form-card" style="max-width:none;">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--indigo),var(--violet));display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;margin:0 auto 12px;">
                {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
            </div>
            <div style="font-size:18px;font-weight:700;color:var(--text-primary);">{{ $pelanggan->nama }}</div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="info-item">
                <div class="info-label">Jenis Identitas</div>
                <div class="info-value">{{ $pelanggan->jenis_identitas }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Nomor Identitas</div>
                <div class="info-value">{{ $pelanggan->no_identitas ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">No. Telepon</div>
                <div class="info-value">{{ $pelanggan->no_telepon ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Alamat</div>
                <div class="info-value">{{ $pelanggan->alamat ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Total Kunjungan</div>
                <div class="info-value"><span class="badge" style="background:var(--blue-bg);color:var(--blue)">{{ $riwayat->total() }} kali menginap</span></div>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;flex-direction:column;gap:8px;">
            <a href="{{ route('pelanggan.edit', $pelanggan) }}" class="btn btn-ghost btn-full">✏ Edit Data</a>
            <a href="{{ route('pemesanan.create', ['pelanggan_id' => $pelanggan->id]) }}" class="btn btn-primary btn-full">✚ Check-In</a>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <span class="table-title">Riwayat Menginap</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Kamar</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Durasi</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $p)
                    <tr>
                        <td class="td-primary">{{ $p->kode_booking }}</td>
                        <td>{{ $p->kamar->nomor_kamar ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->tanggal_checkin)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->tanggal_checkout)->format('d/m/Y') }}</td>
                        <td>{{ $p->durasi_malam }} malam</td>
                        <td class="money">Rp {{ number_format($p->total_harga, 0, ',', '.') }}</td>
                        <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                        <td><a href="{{ route('faktur.show', $p) }}" class="btn btn-xs btn-ghost">🧾</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state" style="padding:30px;">
                                <div class="empty-title">Belum ada riwayat menginap</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($riwayat->hasPages())
        <div class="pagination-wrap">{{ $riwayat->links('vendor.pagination.custom') }}</div>
        @endif
    </div>
</div>
@endsection
