@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')

<div class="section-header">
    <div>
        <h2 class="section-title">Tamu yang Sedang Menginap</h2>
        <p style="font-size:13px;color:var(--text-tertiary);margin:4px 0 0;">
            {{ $pemesananAktif->count() }} tamu aktif saat ini
        </p>
    </div>
    <a href="{{ route('transaksi.create') }}" class="btn btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Check-In Tamu Baru
    </a>
</div>

<div class="table-card">
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
                    <th>Deposit</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pemesananAktif as $pem)
                <tr>
                    <td class="td-primary mono">{{ $pem->kode_booking }}</td>
                    <td>
                        <div style="font-weight:600;font-size:13px;color:var(--text-primary);">{{ $pem->pelanggan->nama }}</div>
                        <div style="font-size:11px;color:var(--text-tertiary);">{{ $pem->pelanggan->jenis_identitas }}: {{ $pem->pelanggan->no_identitas }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600;">No. {{ $pem->kamar->nomor_kamar }}</div>
                        <div style="font-size:11px;color:var(--text-tertiary);">{{ $pem->kamar->nama_kamar ?? $pem->kamar->tipe }}</div>
                    </td>
                    <td>{{ $pem->tanggal_checkin->format('d M Y') }}</td>
                    <td>
                        @php
                            $hariSisa = now()->startOfDay()->diffInDays($pem->tanggal_checkout, false);
                        @endphp
                        <div>{{ $pem->tanggal_checkout->format('d M Y') }}</div>
                        @if($hariSisa <= 1)
                            <div style="font-size:11px;color:var(--red);font-weight:500;">
                                {{ $hariSisa <= 0 ? 'Hari ini!' : 'Besok' }}
                            </div>
                        @elseif($hariSisa <= 3)
                            <div style="font-size:11px;color:var(--amber);font-weight:500;">{{ $hariSisa }} hari lagi</div>
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $pem->durasi_malam }} malam</td>
                    <td>
                        <span class="badge {{ $pem->deposit_badge }}">{{ $pem->deposit_label }}</span>
                    </td>
                    <td class="money">Rp {{ number_format($pem->total_harga, 0, ',', '.') }}</td>
                    <td>
                        {{-- Tombol Check-Out dengan konfirmasi modal --}}
                        <button
                            class="btn btn-xs btn-danger"
                            onclick="konfirmasiCheckout('{{ $pem->id }}', '{{ $pem->pelanggan->nama }}', 'No. {{ $pem->kamar->nomor_kamar }}')"
                        >
                            <i data-lucide="log-out" style="width:13px;height:13px;"></i> Check-Out
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i data-lucide="users" class="empty-icon"></i>
                            <div class="empty-title">Tidak ada tamu yang sedang menginap</div>
                            <div class="empty-desc">
                                <a href="{{ route('transaksi.create') }}" style="color:var(--accent);font-weight:500;">
                                    Proses check-in tamu baru
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL KONFIRMASI CHECK-OUT --}}
<div id="modal-checkout" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="width:36px;height:36px;border-radius:50%;background:var(--red-bg);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="log-out" style="width:18px;height:18px;color:var(--red);"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:15px;color:var(--text-primary);">Konfirmasi Check-Out</div>
                    <div style="font-size:12px;color:var(--text-tertiary);">Tindakan ini tidak dapat dibatalkan</div>
                </div>
            </div>
            <button onclick="tutupModal()" class="btn-logout" title="Tutup">
                <i data-lucide="x" style="width:16px;height:16px;"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--text-secondary);line-height:1.6;">
                Yakin ingin melakukan <strong>check-out</strong> untuk:
            </p>
            <div style="background:var(--bg-base);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px 16px;margin:12px 0;">
                <div style="font-size:13px;color:var(--text-tertiary);margin-bottom:2px;">Tamu</div>
                <div id="modal-nama-tamu" style="font-weight:700;font-size:15px;color:var(--text-primary);"></div>
                <div id="modal-kamar-info" style="font-size:13px;color:var(--text-secondary);margin-top:4px;"></div>
            </div>
            <p style="font-size:12px;color:var(--text-tertiary);">
                Setelah check-out, faktur akan otomatis ditampilkan untuk diunduh atau dicetak.
                Kamar akan ditandai perlu dibersihkan.
            </p>
        </div>
        <div class="modal-footer">
            <button onclick="tutupModal()" class="btn btn-ghost">Batal</button>
            <form id="form-checkout" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger">
                    <i data-lucide="log-out" style="width:15px;height:15px;"></i> Ya, Check-Out
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── MODAL ──────────────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(2px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.15s ease;
}
.modal-box {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    max-width: 440px;
    width: 100%;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    animation: slideUp 0.2s ease;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--border);
}
.modal-body {
    padding: 20px 24px;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid var(--border);
}
.btn-danger {
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid var(--red-border);
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-danger:hover {
    background: var(--red);
    color: white;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* Badge deposit */
.badge-deposit-tunai { background: var(--green-bg); color: var(--green); }
.badge-deposit-dok   { background: var(--amber-bg); color: var(--amber); }

/* Kode booking monospace */
.mono { font-family: 'JetBrains Mono', monospace; letter-spacing: 0.02em; font-size: 12px; }
</style>
@endpush

@push('scripts')
<script>
function konfirmasiCheckout(pemesananId, namaTamu, infoKamar) {
    document.getElementById('modal-nama-tamu').textContent  = namaTamu;
    document.getElementById('modal-kamar-info').textContent = infoKamar;
    document.getElementById('form-checkout').action = '/transaksi/' + pemesananId + '/checkout';
    document.getElementById('modal-checkout').style.display = 'flex';
    lucide.createIcons(); // Re-init icons di dalam modal
}

function tutupModal() {
    document.getElementById('modal-checkout').style.display = 'none';
}

// Tutup modal saat klik di luar box
document.getElementById('modal-checkout').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') tutupModal();
});
</script>
@endpush
