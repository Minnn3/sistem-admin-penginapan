@extends('layouts.app')
@section('title', 'Edit Pelanggan')
@section('page-title', 'Edit Pelanggan')

@section('content')
<div class="section-header">
    <h2 class="section-title">Edit Pelanggan: {{ $pelanggan->nama }}</h2>
    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
</div>

<div class="form-card">
    <form action="{{ route('pelanggan.update', $pelanggan) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-grid">
            <div class="form-group full">
                <label for="nama">Nama Lengkap <span style="color:var(--red)">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control {{ $errors->has('nama') ? 'invalid' : '' }}" value="{{ old('nama', $pelanggan->nama) }}">
                @error('nama') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="jenis_identitas">Jenis Identitas <span style="color:var(--red)">*</span></label>
                <select id="jenis_identitas" name="jenis_identitas" class="form-control" onchange="updateHintIdentitas(this.value)">
                    <option value="KTP"      {{ old('jenis_identitas', $pelanggan->jenis_identitas) == 'KTP'      ? 'selected' : '' }}>KTP</option>
                    <option value="SIM"      {{ old('jenis_identitas', $pelanggan->jenis_identitas) == 'SIM'      ? 'selected' : '' }}>SIM</option>
                    <option value="Passport" {{ old('jenis_identitas', $pelanggan->jenis_identitas) == 'Passport' ? 'selected' : '' }}>Passport</option>
                </select>
            </div>
            <div class="form-group">
                <label for="no_identitas">Nomor Identitas <span style="color:var(--red)">*</span></label>
                <input type="text" id="no_identitas" name="no_identitas"
                    class="form-control {{ $errors->has('no_identitas') ? 'invalid' : '' }}"
                    value="{{ old('no_identitas', $pelanggan->no_identitas) }}">
                @error('no_identitas') <span class="form-error">{{ $message }}</span> @enderror
                <span class="form-hint" id="hint_identitas" style="color:var(--text-tertiary);">KTP/SIM: 16 digit angka</span>
            </div>
            <div class="form-group">
                <label for="no_telepon">No. Telepon</label>
                <input type="text" id="no_telepon" name="no_telepon" class="form-control" value="{{ old('no_telepon', $pelanggan->no_telepon) }}">
            </div>
            <div class="form-group full">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" class="form-control">{{ old('alamat', $pelanggan->alamat) }}</textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Pelanggan</button>
            <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateHintIdentitas(jenis) {
    const hint  = document.getElementById('hint_identitas');
    const input = document.getElementById('no_identitas');
    if (jenis === 'KTP' || jenis === 'SIM') {
        hint.textContent  = jenis + ': 16 digit angka (contoh: 3273010101900001)';
        input.maxLength   = 16;
        input.inputMode   = 'numeric';
    } else {
        hint.textContent  = 'Passport: 6-15 karakter huruf dan angka (contoh: A1234567)';
        input.maxLength   = 15;
        input.inputMode   = 'text';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    updateHintIdentitas(document.getElementById('jenis_identitas').value);
});
</script>
@endpush
