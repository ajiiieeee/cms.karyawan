<div class="row gy-16">
  <div class="col-md-6">
    <label for="nama" class="form-label fw-semibold text-sm">Nama Lengkap <span class="text-danger">*</span></label>
    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $user->nama ?? '') }}" placeholder="Masukkan nama lengkap" required>
    @error('nama')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="username" class="form-label fw-semibold text-sm">Username <span class="text-danger">*</span></label>
    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
           value="{{ old('username', $user->username ?? '') }}" placeholder="Masukkan username" required>
    @error('username')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="email" class="form-label fw-semibold text-sm">Email <span class="text-danger">*</span></label>
    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $user->email ?? '') }}" placeholder="Masukkan email" required>
    @error('email')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3">
    <label for="grup_id" class="form-label fw-semibold text-sm">Grup <span class="text-danger">*</span></label>
    <select name="grup_id" id="grup_id" class="form-select @error('grup_id') is-invalid @enderror" required>
      <option value="">— Pilih Grup —</option>
      @foreach($grups as $grup)
        <option value="{{ $grup->id }}" {{ old('grup_id', $user->grup_id ?? '') == $grup->id ? 'selected' : '' }}>
          {{ $grup->nama_grup }}
        </option>
      @endforeach
    </select>
    @error('grup_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3">
    <label for="is_active" class="form-label fw-semibold text-sm">Status</label>
    <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
      <option value="1" {{ old('is_active', $user->is_active ?? true) == true ? 'selected' : '' }}>Aktif</option>
      <option value="0" {{ old('is_active', $user->is_active ?? true) == false ? 'selected' : '' }}>Nonaktif</option>
    </select>
    @error('is_active')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="password" class="form-label fw-semibold text-sm">
      Password <span class="text-danger">{{ $user ? '' : '*' }}</span>
    </label>
    <div class="position-relative">
      <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
             placeholder="{{ $user ? 'Kosongkan jika tidak ingin mengubah' : 'Masukkan password' }}"
             {{ $user ? '' : 'required' }} minlength="6">
      <button type="button" id="toggle-password"
          class="toggle-password btn p-0 border-0 bg-transparent position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light cursor-pointer ri-eye-line"
          data-toggle="#password" aria-label="Toggle password visibility">
      </button>
    </div>
    @error('password')
      <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6">
    <label for="password_confirmation" class="form-label fw-semibold text-sm">
      Konfirmasi Password <span class="text-danger">{{ $user ? '' : '*' }}</span>
    </label>
    <div class="position-relative">
      <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
             placeholder="Ulangi password" {{ $user ? '' : 'required' }}>
      <button type="button" id="toggle-password-confirmation"
            class="toggle-password btn p-0 border-0 bg-transparent position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light cursor-pointer ri-eye-line"
            data-toggle="#password_confirmation" aria-label="Toggle password visibility">
        </button>
    </div>
  </div>

  <div class="col-md-6">
    <label for="foto" class="form-label fw-semibold text-sm">Foto</label>
    <input type="file" name="foto" id="foto" class="form-control @error('foto') is-invalid @enderror"
           accept="image/jpg,image/jpeg,image/png,image/webp">
    <small class="text-neutral-500">Format: jpg, jpeg, png, webp. Maks: 2MB</small>
    @error('foto')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  @if($user && $user->foto)
  <div class="col-md-6">
    <label class="form-label fw-semibold text-sm">Foto Saat Ini</label>
    <div>
      <img src="{{ asset('upload/foto-user/' . $user->foto) }}" alt="Foto User"
           class="rounded-3 object-fit-cover" style="width:80px;height:80px">
    </div>
  </div>
  @endif
</div>
