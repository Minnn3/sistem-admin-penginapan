@extends('layouts.app')
@section('title', 'Check-In Tamu')
@section('page-title', 'Check-In')

@section('content')
<div class="section-header">
    <h2 class="section-title">Form Check-In Tamu</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-ghost"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Dashboard</a>
</div>

<div class="form-card">
    <form action="{{ route('pemesanan.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group full">
                <label for="kamar_id">Pilih Kamar <span style="color:var(--red)">*</span></label>
                <select id="kamar_id" name="kamar_id" class="form-control {{ $errors->has('kamar_id') ? 'invalid' : '' }}" onchange="updateHarga(this)">
                    <option value="">— Pilih Kamar —</option>
                    @foreach($kamarTersedia as $kamar)
                        <option value="{{ $kamar->id }}"
                            data-harga="{{ $kamar->harga_per_malam }}"
                            {{ (old('kamar_id', $selectedKamar?->id) == $kamar->id) ? 'selected' : '' }}>
                            No. {{ $kamar->nomor_kamar }} — {{ $kamar->nama_kamar ?? $kamar->tipe }} (Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}/malam)
                        </option>
                    @endforeach
                </select>
                @error('kamar_id') <span class="form-error">{{ $message }}</span> @enderror
                @if($kamarTersedia->isEmpty())
                    <span class="form-hint" style="color:var(--amber); display:flex; align-items:center; gap:4px;">
                        <i data-lucide="alert-triangle" style="width:14px; height:14px;"></i> Tidak ada kamar yang tersedia saat ini.
                    </span>
                @endif
            </div>

            <div class="form-group full" style="position:relative;">
                <label for="pelanggan_search">Nama Pelanggan <span style="color:var(--red)">*</span></label>
                <input
                    type="text"
                    id="pelanggan_search"
                    class="form-control {{ $errors->has('pelanggan_id') ? 'invalid' : '' }}"
                    placeholder="Ketik nama pelanggan..."
                    autocomplete="off"
                >
                <input type="hidden" id="pelanggan_id" name="pelanggan_id" value="{{ old('pelanggan_id') }}">

                <div id="pelanggan_suggestions" style="
                    display:none;position:absolute;top:calc(100% - 6px);left:0;right:0;
                    background:var(--bg-elevated);border:1px solid var(--border-light);
                    border-top:none;border-radius:0 0 var(--radius-sm) var(--radius-sm);
                    max-height:220px;overflow-y:auto;z-index:999;box-shadow:var(--shadow-md);
                "></div>

                @error('pelanggan_id') <span class="form-error">{{ $message }}</span> @enderror
                <span class="form-hint">
                    Belum terdaftar? <a href="{{ route('pelanggan.create') }}" target="_blank" style="color:var(--primary)">Tambah pelanggan baru</a>
                </span>
            </div>

            <div class="form-group">
                <label for="tanggal_checkin">Tanggal Check-In <span style="color:var(--red)">*</span></label>
                <input type="date" id="tanggal_checkin" name="tanggal_checkin" class="form-control {{ $errors->has('tanggal_checkin') ? 'invalid' : '' }}"
                    value="{{ old('tanggal_checkin', now()->toDateString()) }}" onchange="hitungDurasi()">
                @error('tanggal_checkin') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="tanggal_checkout">Tanggal Check-Out <span style="color:var(--red)">*</span></label>
                <input type="date" id="tanggal_checkout" name="tanggal_checkout" class="form-control {{ $errors->has('tanggal_checkout') ? 'invalid' : '' }}"
                    value="{{ old('tanggal_checkout', now()->addDay()->toDateString()) }}" onchange="hitungDurasi()">
                @error('tanggal_checkout') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="metode_bayar">Metode Pembayaran <span style="color:var(--red)">*</span></label>
                <select id="metode_bayar" name="metode_bayar" class="form-control {{ $errors->has('metode_bayar') ? 'invalid' : '' }}">
                    <option value="tunai"    {{ old('metode_bayar') == 'tunai'    ? 'selected' : '' }}>Tunai</option>
                    <option value="transfer" {{ old('metode_bayar') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                    <option value="qris"     {{ old('metode_bayar') == 'qris'     ? 'selected' : '' }}>QRIS</option>
                </select>
                @error('metode_bayar') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Estimasi Tagihan</label>
                <div style="background:var(--bg-base);border:1px solid var(--border-light);border-radius:var(--radius-sm);padding:12px 14px;">
                    <div style="font-size:12px;color:var(--text-tertiary);" id="infoMalam">— pilih kamar & tanggal —</div>
                    <div style="font-size:20px;font-weight:700;color:var(--text-primary);letter-spacing:-0.01em;margin-top:2px;" id="totalHarga">—</div>
                </div>
            </div>

            <div class="form-group full">
                <label for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan" class="form-control" placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Proses Check-In</button>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Data pelanggan dari server ──
const pelangganData = <?php echo json_encode($pelangganList->map(fn($p) => [
    'id'    => $p->id,
    'nama'  => $p->nama,
    'info'  => ($p->jenis_identitas ?? '') . ': ' . ($p->no_identitas ?? '-') . ' | ' . ($p->no_telepon ?? '-'),
])->values()); ?>;

// ── Autocomplete Pelanggan ──
const searchInput   = document.getElementById('pelanggan_search');
const hiddenInput   = document.getElementById('pelanggan_id');
const suggestionBox = document.getElementById('pelanggan_suggestions');

function renderSuggestions(query) {
    const q = query.toLowerCase().trim();
    if (!q) { suggestionBox.style.display = 'none'; return; }

    const matches = pelangganData.filter(p => p.nama.toLowerCase().includes(q));

    if (!matches.length) {
        suggestionBox.innerHTML = '<div style="padding:12px 14px;color:var(--text-tertiary);font-size:13px;">Tidak ada hasil. <a href="{{ route("pelanggan.create") }}" target="_blank" style="color:var(--primary)">Tambah baru</a></div>';
        suggestionBox.style.display = 'block';
        return;
    }

    suggestionBox.innerHTML = matches.map(p => `
        <div class="suggestion-item" data-id="${p.id}" data-nama="${p.nama}" style="
            padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border-light);
            transition:background .15s;
        ">
            <div style="font-weight:500;font-size:13px;color:var(--text-primary);">${p.nama}</div>
            <div style="font-size:11px;color:var(--text-tertiary);">${p.info}</div>
        </div>
    `).join('');

    // Hover effect + click handler
    suggestionBox.querySelectorAll('.suggestion-item').forEach(el => {
        el.addEventListener('mouseenter', () => el.style.background = 'var(--bg-base)');
        el.addEventListener('mouseleave', () => el.style.background = '');
        el.addEventListener('mousedown', (e) => {
            e.preventDefault(); // Prevent blur firing before click
            searchInput.value  = el.dataset.nama;
            hiddenInput.value  = el.dataset.id;
            suggestionBox.style.display = 'none';
        });
    });

    suggestionBox.style.display = 'block';
}

searchInput.addEventListener('input', () => {
    hiddenInput.value = ''; // Reset ID saat user mengetik ulang
    renderSuggestions(searchInput.value);
});

searchInput.addEventListener('blur', () => {
    setTimeout(() => { suggestionBox.style.display = 'none'; }, 150);
});

searchInput.addEventListener('focus', () => {
    if (searchInput.value) renderSuggestions(searchInput.value);
});

// ── Hitung Harga ──
let hargaPerMalam = 0;

function updateHarga(sel) {
    const opt = sel.options[sel.selectedIndex];
    hargaPerMalam = parseFloat(opt.getAttribute('data-harga') || 0);
    hitungDurasi();
}

function hitungDurasi() {
    const ci = document.getElementById('tanggal_checkin').value;
    const co = document.getElementById('tanggal_checkout').value;
    if (!ci || !co) return;
    const diff  = Math.max(1, Math.round((new Date(co) - new Date(ci)) / 86400000));
    const total = hargaPerMalam * diff;
    document.getElementById('infoMalam').textContent  = diff + ' malam × Rp ' + hargaPerMalam.toLocaleString('id-ID');
    document.getElementById('totalHarga').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('kamar_id');
    if (sel.value) updateHarga(sel);
    hitungDurasi();
});
</script>
@endpush
