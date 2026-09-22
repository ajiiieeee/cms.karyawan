<div class="row gy-16">
  <div class="col-md-6">
    <label for="nama_menu" class="form-label fw-semibold text-sm">Nama Menu <span class="text-danger">*</span></label>
    <input type="text" name="nama_menu" id="nama_menu" class="form-control @error('nama_menu') is-invalid @enderror"
           value="{{ old('nama_menu', $menu->nama_menu ?? '') }}" placeholder="Masukkan nama menu" required>
    @error('nama_menu')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="link" class="form-label fw-semibold text-sm">Link</label>
    <input type="text" name="link" id="link" class="form-control @error('link') is-invalid @enderror"
           value="{{ old('link', $menu->link ?? '') }}" placeholder="Contoh: user-management">
    <small class="text-neutral-500">Kosongkan jika menu ini adalah parent / kategori</small>
    @error('link')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="icon" class="form-label fw-semibold text-sm">Icon</label>
    <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror"
           value="{{ old('icon', $menu->icon ?? '') }}" placeholder="Contoh: ri-settings-3-line">
    <small class="text-neutral-500">Gunakan class icon dari Remix Icon</small>
    @error('icon')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3">
    <label for="parent" class="form-label fw-semibold text-sm">Parent <span class="text-danger">*</span></label>
    <select name="parent" id="parent" class="form-select @error('parent') is-invalid @enderror" required>
      <option value="0" {{ old('parent', $menu->parent ?? 0) == 0 ? 'selected' : '' }}>— Tanpa Parent (Root) —</option>
      @foreach($parents as $p)
        <option value="{{ $p->id }}" {{ old('parent', $menu->parent ?? 0) == $p->id ? 'selected' : '' }}>
          {{ $p->nama_menu }}
        </option>
      @endforeach
    </select>
    @error('parent')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3">
    <label for="urutan" class="form-label fw-semibold text-sm">Urutan <span class="text-danger">*</span></label>
    <input type="number" name="urutan" id="urutan" class="form-control @error('urutan') is-invalid @enderror"
           value="{{ old('urutan', $menu->urutan ?? 0) }}" min="0" required>
    @error('urutan')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
</div>
