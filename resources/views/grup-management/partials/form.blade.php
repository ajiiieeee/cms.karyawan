<div class="row gy-16">
  <div class="col-md-6">
    <label for="nama_grup" class="form-label fw-semibold text-sm">Nama Grup <span class="text-danger">*</span></label>
    <input type="text" name="nama_grup" id="nama_grup" class="form-control @error('nama_grup') is-invalid @enderror"
           value="{{ old('nama_grup', $grup->nama_grup ?? '') }}" placeholder="Masukkan nama grup" required>
    @error('nama_grup')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="deskripsi" class="form-label fw-semibold text-sm">Deskripsi</label>
    <input type="text" name="deskripsi" id="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
           value="{{ old('deskripsi', $grup->deskripsi ?? '') }}" placeholder="Deskripsi singkat grup">
    @error('deskripsi')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>
