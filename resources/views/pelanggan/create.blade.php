@extends('layouts.app')
@section('title', 'Tambah Pelanggan')
@section('page-title', 'Tambah Pelanggan')

@section('content')
<div class="section-header">
    <h2 class="section-title">Form Tambah Pelanggan</h2>
    <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
</div>

<div class="form-card">
    <form action="{{ route('pelanggan.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group full">
                <label for="nama">Nama Lengkap <span style="color:var(--red)">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control {{ $errors->has('nama') ? 'invalid' : '' }}" value="{{ old('nama') }}" placeholder="Nama lengkap pelanggan">
                @error('nama') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="jenis_identitas">Jenis Identitas</label>
                <select id="jenis_identitas" name="jenis_identitas" class="form-control">
                    <option value="KTP" {{ old('jenis_identitas') == 'KTP' ? 'selected' : '' }}>KTP</option>
                    <option value="SIM" {{ old('jenis_identitas') == 'SIM' ? 'selected' : '' }}>SIM</option>
                    <option value="Passport" {{ old('jenis_identitas') == 'Passport' ? 'selected' : '' }}>Passport</option>
                </select>
            </div>
            <div class="form-group">
                <label for="no_identitas">Nomor Identitas</label>
                <input type="text" id="no_identitas" name="no_identitas" class="form-control" value="{{ old('no_identitas') }}" placeholder="Nomor KTP / SIM / Passport">
            </div>
            <div class="form-group">
                <label for="no_telepon">No. Telepon</label>
                <input type="text" id="no_telepon" name="no_telepon" class="form-control" value="{{ old('no_telepon') }}" placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group full">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" class="form-control" placeholder="Alamat lengkap pelanggan">{{ old('alamat') }}</textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
            <a href="{{ route('pelanggan.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection
