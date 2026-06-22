@extends('layouts.app')
@section('title', 'Check-In Tamu')
@section('page-title', 'Transaksi')

@section('content')
<div class="section-header">
    <h2 class="section-title">Form Check-In Tamu</h2>
    <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">
        <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Kembali
    </a>
</div>

<div class="form-card">
    <form action="{{ route('transaksi.store') }}" method="POST" id="form-checkin">
        @csrf
        <div class="form-grid">

            {{-- PILIH KAMAR --}}
            <div class="form-group full">
                <label for="kamar_id">Pilih Kamar <span style="color:var(--red)">*</span></label>
                <select id="kamar_id" name="kamar_id"
                    class="form-control {{ $errors->has('kamar_id') ? 'invalid' : '' }}"
                    onchange="updateHarga(this)">
                    <option value="">— Pilih Kamar —</option>
                    @foreach($kamarTersedia as $kamar)
                        <option value="{{ $kamar->id }}"
                            data-harga="{{ $kamar->harga_per_malam }}"
                            {{ (old('kamar_id', $selectedKamar?->id) == $kamar->id) ? 'selected' : '' }}>
                            No. {{ $kamar->nomor_kamar }} — {{ $kamar->nama_kamar ?? $kamar->tipe }}
                            (Rp {{ number_format($kamar->harga_per_malam, 0, ',', '.') }}/malam)
                        </option>
                    @endforeach
                </select>
                @error('kamar_id') <span class="form-error">{{ $message }}</span> @enderror
                @if($kamarTersedia->isEmpty())
                    <span class="form-hint" style="color:var(--amber);display:flex;align-items:center;gap:4px;">
                        <i data-lucide="alert-triangle" style="width:14px;height:14px;"></i>
                        Tidak ada kamar tersedia saat ini.
                    </span>
                @endif
            </div>

            {{-- PELANGGAN (AUTOCOMPLETE) --}}
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
                    background:var(--bg-elevated);border:1px solid var(--border);
                    border-top:none;border-radius:0 0 var(--radius-sm) var(--radius-sm);
                    max-height:220px;overflow-y:auto;z-index:999;box-shadow:0 4px 16px rgba(0,0,0,0.08);
                "></div>

                @error('pelanggan_id') <span class="form-error">{{ $message }}</span> @enderror
                <span class="form-hint">
                    Belum terdaftar?
                    <a href="{{ route('pelanggan.create') }}" target="_blank" style="color:var(--primary)">Tambah pelanggan baru</a>
                </span>
            </div>

            {{-- TANGGAL CHECK-IN --}}
            <div class="form-group">
                <label for="tanggal_checkin">Tanggal Check-In <span style="color:var(--red)">*</span></label>
                <input type="date" id="tanggal_checkin" name="tanggal_checkin"
                    class="form-control {{ $errors->has('tanggal_checkin') ? 'invalid' : '' }}"
                    value="{{ old('tanggal_checkin', now()->toDateString()) }}"
                    onchange="hitungDurasi()">
                @error('tanggal_checkin') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- TANGGAL CHECK-OUT --}}
            <div class="form-group">
                <label for="tanggal_checkout">Tanggal Check-Out <span style="color:var(--red)">*</span></label>
                <input type="date" id="tanggal_checkout" name="tanggal_checkout"
                    class="form-control {{ $errors->has('tanggal_checkout') ? 'invalid' : '' }}"
                    value="{{ old('tanggal_checkout', now()->addDay()->toDateString()) }}"
                    onchange="hitungDurasi()">
                @error('tanggal_checkout') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- ESTIMASI TAGIHAN --}}
            <div class="form-group">
                <label>Estimasi Tagihan</label>
                <div style="background:var(--bg-base);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;">
                    <div style="font-size:12px;color:var(--text-tertiary);" id="infoMalam">— pilih kamar &amp; tanggal —</div>
                    <div style="font-size:20px;font-weight:700;color:var(--text-primary);letter-spacing:-0.01em;margin-top:2px;font-family:'JetBrains Mono',monospace;" id="totalHarga">—</div>
                </div>
            </div>

            {{-- METODE PEMBAYARAN (statis: tunai saja) --}}
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <div style="background:var(--bg-base);border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="banknote" style="width:16px;height:16px;color:var(--green);"></i>
                    <span style="font-weight:600;font-size:14px;color:var(--text-primary);">Tunai</span>
                    <span style="font-size:12px;color:var(--text-tertiary);margin-left:4px;">— pembayaran dilakukan saat check-in</span>
                </div>
            </div>

            {{-- PILIH DEPOSIT --}}
            <div class="form-group full">
                <label>Jenis Deposit <span style="color:var(--red)">*</span></label>
                <p class="form-hint" style="margin-bottom:10px;">Admin menyimpan deposit tamu sebagai jaminan selama menginap.</p>

                @error('deposit_jenis') <span class="form-error" style="display:block;margin-bottom:8px;">{{ $message }}</span> @enderror

                <div class="deposit-options">
                    {{-- Opsi Tunai --}}
                    <label class="deposit-card {{ old('deposit_jenis') == 'tunai' ? 'selected' : '' }}" for="deposit_tunai">
                        <input type="radio" id="deposit_tunai" name="deposit_jenis" value="tunai"
                            {{ old('deposit_jenis') == 'tunai' ? 'checked' : '' }}
                            onchange="updateDepositCard(this)">
                        <div class="deposit-icon" style="background:var(--green-bg);">
                            <i data-lucide="banknote" style="width:20px;height:20px;color:var(--green);"></i>
                        </div>
                        <div class="deposit-info">
                            <div class="deposit-label">Tunai</div>
                            <div class="deposit-desc">Rp 100.000 cash</div>
                        </div>
                        <span class="deposit-check"><i data-lucide="check" style="width:14px;height:14px;"></i></span>
                    </label>

                    {{-- Opsi KTP --}}
                    <label class="deposit-card {{ old('deposit_jenis', 'ktp') == 'ktp' ? 'selected' : '' }}" for="deposit_ktp">
                        <input type="radio" id="deposit_ktp" name="deposit_jenis" value="ktp"
                            {{ old('deposit_jenis', 'ktp') == 'ktp' ? 'checked' : '' }}
                            onchange="updateDepositCard(this)">
                        <div class="deposit-icon" style="background:var(--amber-bg);">
                            <i data-lucide="credit-card" style="width:20px;height:20px;color:var(--amber);"></i>
                        </div>
                        <div class="deposit-info">
                            <div class="deposit-label">KTP</div>
                            <div class="deposit-desc">Kartu Tanda Penduduk</div>
                        </div>
                        <span class="deposit-check"><i data-lucide="check" style="width:14px;height:14px;"></i></span>
                    </label>

                    {{-- Opsi SIM --}}
                    <label class="deposit-card {{ old('deposit_jenis') == 'sim' ? 'selected' : '' }}" for="deposit_sim">
                        <input type="radio" id="deposit_sim" name="deposit_jenis" value="sim"
                            {{ old('deposit_jenis') == 'sim' ? 'checked' : '' }}
                            onchange="updateDepositCard(this)">
                        <div class="deposit-icon" style="background:var(--blue-bg);">
                            <i data-lucide="car" style="width:20px;height:20px;color:var(--blue);"></i>
                        </div>
                        <div class="deposit-info">
                            <div class="deposit-label">SIM</div>
                            <div class="deposit-desc">Surat Izin Mengemudi</div>
                        </div>
                        <span class="deposit-check"><i data-lucide="check" style="width:14px;height:14px;"></i></span>
                    </label>
                </div>
            </div>

            {{-- CATATAN --}}
            <div class="form-group full">
                <label for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan" class="form-control"
                    placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="btn-submit">
                <i data-lucide="log-in" style="width:16px;height:16px;"></i> Proses Check-In
            </button>
            <a href="{{ route('transaksi.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
/* ── Deposit Card Options ────────────────────────────────── */
.deposit-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.deposit-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: center;
    background: var(--bg-surface);
    position: relative;
}
.deposit-card:hover {
    border-color: var(--border-hover);
    background: var(--bg-hover);
}
.deposit-card.selected {
    border-color: var(--primary);
    background: var(--bg-base);
    box-shadow: 0 0 0 2px rgba(0,0,0,0.06);
}
.deposit-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.deposit-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.deposit-label { font-weight: 700; font-size: 14px; color: var(--text-primary); }
.deposit-desc  { font-size: 12px; color: var(--text-tertiary); margin-top: 2px; }

.deposit-check {
    display: none;
    position: absolute;
    top: 8px;
    right: 8px;
    width: 20px;
    height: 20px;
    background: var(--primary);
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    color: white;
}
.deposit-card.selected .deposit-check {
    display: flex;
}

@media(max-width:600px) {
    .deposit-options { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
// ── Data pelanggan dari server ──
const pelangganData = <?php echo json_encode($pelangganList->map(fn($p) => [
    'id'   => $p->id,
    'nama' => $p->nama,
    'info' => ($p->jenis_identitas ?? '') . ': ' . ($p->no_identitas ?? '-') . ' | ' . ($p->no_telepon ?? '-'),
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
        suggestionBox.innerHTML = `<div style="padding:12px 14px;color:var(--text-tertiary);font-size:13px;">
            Tidak ada hasil.
            <a href="{{ route('pelanggan.create') }}" target="_blank" style="color:var(--primary)">Tambah baru</a>
        </div>`;
        suggestionBox.style.display = 'block';
        return;
    }

    suggestionBox.innerHTML = matches.map(p => `
        <div class="suggestion-item" data-id="${p.id}" data-nama="${p.nama}" style="
            padding:10px 14px;cursor:pointer;border-bottom:1px solid var(--border);
            transition:background .12s;
        ">
            <div style="font-weight:500;font-size:13px;color:var(--text-primary);">${p.nama}</div>
            <div style="font-size:11px;color:var(--text-tertiary);">${p.info}</div>
        </div>
    `).join('');

    suggestionBox.querySelectorAll('.suggestion-item').forEach(el => {
        el.addEventListener('mouseenter', () => el.style.background = 'var(--bg-base)');
        el.addEventListener('mouseleave', () => el.style.background = '');
        el.addEventListener('mousedown', (e) => {
            e.preventDefault();
            searchInput.value = el.dataset.nama;
            hiddenInput.value = el.dataset.id;
            suggestionBox.style.display = 'none';
        });
    });

    suggestionBox.style.display = 'block';
}

searchInput.addEventListener('input', () => {
    hiddenInput.value = '';
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

// ── Deposit Card Interaktif ──
function updateDepositCard(radio) {
    document.querySelectorAll('.deposit-card').forEach(card => {
        card.classList.remove('selected');
    });
    radio.closest('.deposit-card').classList.add('selected');
}

document.addEventListener('DOMContentLoaded', () => {
    // Init harga jika kamar sudah dipilih (misal saat ada old input)
    const sel = document.getElementById('kamar_id');
    if (sel.value) updateHarga(sel);
    hitungDurasi();

    // Reinitialize lucide icons untuk deposit
    lucide.createIcons();
});
</script>
@endpush
