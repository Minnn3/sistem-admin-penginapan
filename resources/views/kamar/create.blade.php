@extends('layouts.app')
@section('title', 'Tambah Kamar')
@section('page-title', 'Tambah Kamar')

@section('content')
<div class="section-header">
    <h2 class="section-title">Form Tambah Kamar</h2>
    <a href="{{ route('kamar.index') }}" class="btn btn-ghost"><i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i> Kembali</a>
</div>

<div class="form-card">
    <form action="{{ route('kamar.store') }}" method="POST">
        @csrf
        <div class="form-grid">
            <div class="form-group">
                <label for="nomor_kamar">Nomor Kamar <span style="color:var(--red)">*</span></label>
                <input type="text" id="nomor_kamar" name="nomor_kamar" class="form-control {{ $errors->has('nomor_kamar') ? 'invalid' : '' }}" value="{{ old('nomor_kamar') }}" placeholder="101">
                @error('nomor_kamar') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="nama_kamar">Nama Kamar</label>
                <input type="text" id="nama_kamar" name="nama_kamar" class="form-control" value="{{ old('nama_kamar') }}" placeholder="Kamar Lantai ...">
            </div>
            <div class="form-group">
                <label for="tipe">Tipe Kamar <span style="color:var(--red)">*</span></label>
                <input type="text" id="tipe" name="tipe" class="form-control {{ $errors->has('tipe') ? 'invalid' : '' }}" value="{{ old('tipe') }}" placeholder="Standard / Deluxe / Family">
                @error('tipe') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="harga_per_malam">Harga per Malam (Rp) <span style="color:var(--red)">*</span></label>
                <input type="number" id="harga_per_malam" name="harga_per_malam" class="form-control {{ $errors->has('harga_per_malam') ? 'invalid' : '' }}" value="{{ old('harga_per_malam') }}" placeholder="150000" min="0">
                @error('harga_per_malam') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group full">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" class="form-control" placeholder="Deskripsi fasilitas kamar...">{{ old('deskripsi') }}</textarea>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Kamar</button>
            <a href="{{ route('kamar.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection
