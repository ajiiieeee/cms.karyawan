@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-24 radius-8" role="alert" id="validation-alert">
  <div class="d-flex align-items-start gap-10">
    <i class="ri-error-warning-line text-xl flex-shrink-0 mt-2"></i>
    <div>
      <h6 class="fw-semibold mb-4 text-sm">Terdapat {{ $errors->count() }} kesalahan pada form:</h6>
      <ul class="mb-0 ps-16 text-sm" style="list-style: disc;">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Data Pribadi -->
<div class="card shadow-1 radius-8 mb-24">
  <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
    <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
      <i class="ri-user-3-line text-lg"></i> Data Pribadi
    </h6>
  </div>
  <div class="card-body p-24">
    <div class="row gy-lg-3">
      <div class="col-md-6">
        <label for="nama_karyawan" class="form-label fw-semibold text-sm">Nama Karyawan <span class="text-danger">*</span></label>
        <input type="text" name="nama_karyawan" id="nama_karyawan" class="form-control @error('nama_karyawan') is-invalid @enderror"
               value="{{ old('nama_karyawan', $karyawan->nama_karyawan ?? '') }}" placeholder="Masukkan nama karyawan" required>
        @error('nama_karyawan')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="nik" class="form-label fw-semibold text-sm">NIK <span class="text-danger">*</span></label>
        <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror"
               value="{{ old('nik', $karyawan->nik ?? '') }}" placeholder="Masukkan NIK" maxlength="18" required>
        @error('nik')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="jenis_kelamin" class="form-label fw-semibold text-sm">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
          <option value="">— Pilih Jenis Kelamin —</option>
          <option value="laki-laki" {{ old('jenis_kelamin', $karyawan->jenis_kelamin ?? '') == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
          <option value="perempuan" {{ old('jenis_kelamin', $karyawan->jenis_kelamin ?? '') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @error('jenis_kelamin')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3">
        <label for="tempat_lahir" class="form-label fw-semibold text-sm">Tempat Lahir <span class="text-danger">*</span></label>
        <input type="text" name="tempat_lahir" id="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
               value="{{ old('tempat_lahir', $karyawan->tempat_lahir ?? '') }}" placeholder="Masukkan tempat lahir" required>
        @error('tempat_lahir')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3">
        <label for="tanggal_lahir" class="form-label fw-semibold text-sm">Tanggal Lahir <span class="text-danger">*</span></label>
        <div class="datepicker-wrapper position-relative" id="datepicker-lahir">
          <input type="text" name="tanggal_lahir" id="tanggal_lahir" class="form-control pe-40 @error('tanggal_lahir') is-invalid @enderror" data-input
                 value="{{ old('tanggal_lahir', isset($karyawan) && $karyawan->tanggal_lahir ? \Carbon\Carbon::parse($karyawan->tanggal_lahir)->format('d/m/Y') : '') }}"
                 placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
          <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
            <i class="ri-calendar-line text-lg"></i>
          </span>
        </div>
        @error('tanggal_lahir')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="email" class="form-label fw-semibold text-sm">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $karyawan->email ?? '') }}" placeholder="Masukkan email" required>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="alamat" class="form-label fw-semibold text-sm">Alamat <span class="text-danger">*</span></label>
        <textarea name="alamat" id="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror"
                  placeholder="Masukkan alamat lengkap" required>{{ old('alamat', $karyawan->alamat ?? '') }}</textarea>
        @error('alamat')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3">
        <label for="kota" class="form-label fw-semibold text-sm">Kota</label>
        <input type="text" name="kota" id="kota" class="form-control @error('kota') is-invalid @enderror"
               value="{{ old('kota', $karyawan->kota ?? '') }}" placeholder="Masukkan kota">
        @error('kota')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-3">
        <label for="provinsi" class="form-label fw-semibold text-sm">Provinsi</label>
        <input type="text" name="provinsi" id="provinsi" class="form-control @error('provinsi') is-invalid @enderror"
               value="{{ old('provinsi', $karyawan->provinsi ?? '') }}" placeholder="Masukkan provinsi">
        @error('provinsi')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="telefon" class="form-label fw-semibold text-sm">Telepon <span class="text-danger">*</span></label>
        <input type="text" name="telefon" id="telefon" class="form-control @error('telefon') is-invalid @enderror"
               value="{{ old('telefon', $karyawan->telefon ?? '') }}" placeholder="Masukkan nomor telepon" maxlength="20" required>
        @error('telefon')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="telefon_alternatif" class="form-label fw-semibold text-sm">Telepon Alternatif</label>
        <input type="text" name="telefon_alternatif" id="telefon_alternatif" class="form-control @error('telefon_alternatif') is-invalid @enderror"
               value="{{ old('telefon_alternatif', $karyawan->telefon_alternatif ?? '') }}" placeholder="Masukkan nomor telepon alternatif" maxlength="20">
        @error('telefon_alternatif')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-6">
        <label class="form-label fw-semibold text-sm">Foto</label>
        <div class="foto-upload-area @error('foto') has-error @enderror" id="foto-upload-area">
          <input type="file" name="foto" id="foto" class="foto-upload-input" accept="image/jpg,image/jpeg,image/png,image/webp">

          {{-- Preview state (has existing or new photo) --}}
          <div class="foto-preview-state {{ (isset($karyawan) && $karyawan->foto) ? '' : 'd-none' }}" id="foto-preview-state">
            <div class="foto-preview-card">
              <img src="{{ (isset($karyawan) && $karyawan->foto) ? asset('storage/foto-karyawan/' . $karyawan->foto) : '' }}"
                   alt="" class="foto-preview-img" id="foto-preview-img">
              <div class="foto-preview-info">
                <span class="foto-preview-name text-sm fw-medium text-primary-light" id="foto-preview-name">
                  {{ (isset($karyawan) && $karyawan->foto) ? $karyawan->foto : '' }}
                </span>
                <span class="foto-preview-hint text-xs text-neutral-500">Klik untuk mengganti foto</span>
              </div>
              <button type="button" class="foto-remove-btn" id="foto-remove-btn" title="Hapus foto">
                <i class="ri-close-line"></i>
              </button>
            </div>
          </div>

          {{-- Empty state (no photo) --}}
          <div class="foto-empty-state {{ (isset($karyawan) && $karyawan->foto) ? 'd-none' : '' }}" id="foto-empty-state">
            <div class="foto-empty-icon">
              <i class="ri-image-add-line"></i>
            </div>
            <div class="foto-empty-text">
              <span class="fw-semibold text-primary-light">Klik untuk upload</span>
              <span class="text-neutral-500">atau seret file ke sini</span>
            </div>
            <span class="foto-empty-hint text-xs text-neutral-400">JPG, PNG, WebP &bull; Maks 2MB</span>
          </div>
        </div>
        @error('foto')
          <div class="text-danger text-sm mt-6">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</div>

<!-- Pendidikan & Pekerjaan -->
<div class="card shadow-1 radius-8 mb-24">
  <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
    <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
      <i class="ri-graduation-cap-line text-lg"></i> Pendidikan & Pekerjaan
    </h6>
  </div>
  <div class="card-body p-24">
    <div class="row gy-lg-3">
      <div class="col-md-4">
        <label for="pendidikan_terakhir" class="form-label fw-semibold text-sm">Pendidikan Terakhir <span class="text-danger">*</span></label>
        <input type="text" name="pendidikan_terakhir" id="pendidikan_terakhir" class="form-control @error('pendidikan_terakhir') is-invalid @enderror"
               value="{{ old('pendidikan_terakhir', $karyawan->pendidikan_terakhir ?? '') }}" placeholder="Contoh: S1, D3, SMA" required>
        @error('pendidikan_terakhir')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label for="lembaga_pendidikan" class="form-label fw-semibold text-sm">Lembaga Pendidikan</label>
        <input type="text" name="lembaga_pendidikan" id="lembaga_pendidikan" class="form-control @error('lembaga_pendidikan') is-invalid @enderror"
               value="{{ old('lembaga_pendidikan', $karyawan->lembaga_pendidikan ?? '') }}" placeholder="Nama universitas/sekolah">
        @error('lembaga_pendidikan')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label for="tahun_lulus" class="form-label fw-semibold text-sm">Tahun Lulus <span class="text-danger">*</span></label>
        <select name="tahun_lulus" id="tahun_lulus" class="form-select @error('tahun_lulus') is-invalid @enderror" required>
          <option value="">— Pilih Tahun —</option>
          @for($y = date('Y'); $y >= 1950; $y--)
            <option value="{{ $y }}" {{ old('tahun_lulus', $karyawan->tahun_lulus ?? '') == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
        @error('tahun_lulus')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label for="jabatan" class="form-label fw-semibold text-sm">Jabatan <span class="text-danger">*</span></label>
        <select name="jabatan" id="jabatan" class="form-select @error('jabatan') is-invalid @enderror" required>
          <option value="">— Pilih Jabatan —</option>
          @foreach($jabatanOptions as $key => $label)
            <option value="{{ $key }}" {{ old('jabatan', $karyawan->jabatan ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('jabatan')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-4">
        <label for="tanggal_masuk" class="form-label fw-semibold text-sm">Tanggal Masuk <span class="text-danger">*</span></label>
        <div class="datepicker-wrapper position-relative" id="datepicker-masuk">
          <input type="text" name="tanggal_masuk" id="tanggal_masuk" class="form-control pe-40 @error('tanggal_masuk') is-invalid @enderror" data-input
                 value="{{ old('tanggal_masuk', isset($karyawan) && $karyawan->tanggal_masuk ? \Carbon\Carbon::parse($karyawan->tanggal_masuk)->format('d/m/Y') : '') }}"
                 placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
          <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
            <i class="ri-calendar-line text-lg"></i>
          </span>
        </div>
        @error('tanggal_masuk')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</div>

<!-- Data Keluarga / Kontak Darurat -->
<div class="card shadow-1 radius-8 mb-24">
  <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
    <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
      <i class="ri-parent-line text-lg"></i> Kontak Darurat / Keluarga
    </h6>
  </div>
  <div class="card-body p-24">
    <div class="row gy-lg-3">
      <div class="col-md-6">
        <label for="nama_keluarga" class="form-label fw-semibold text-sm">Nama Keluarga <span class="text-danger">*</span></label>
        <input type="text" name="nama_keluarga" id="nama_keluarga" class="form-control @error('nama_keluarga') is-invalid @enderror"
               value="{{ old('nama_keluarga', $karyawan->nama_keluarga ?? '') }}" placeholder="Masukkan nama keluarga" required>
        @error('nama_keluarga')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="hubungan_keluarga" class="form-label fw-semibold text-sm">Hubungan Keluarga <span class="text-danger">*</span></label>
        <input type="text" name="hubungan_keluarga" id="hubungan_keluarga" class="form-control @error('hubungan_keluarga') is-invalid @enderror"
               value="{{ old('hubungan_keluarga', $karyawan->hubungan_keluarga ?? '') }}" placeholder="Contoh: Orang Tua, Suami/Istri" required>
        @error('hubungan_keluarga')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="telefon_keluarga" class="form-label fw-semibold text-sm">Telepon Keluarga</label>
        <input type="text" name="telefon_keluarga" id="telefon_keluarga" class="form-control @error('telefon_keluarga') is-invalid @enderror"
               value="{{ old('telefon_keluarga', $karyawan->telefon_keluarga ?? '') }}" placeholder="Masukkan nomor telepon keluarga">
        @error('telefon_keluarga')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="pendidikan_keluarga" class="form-label fw-semibold text-sm">Pendidikan Keluarga</label>
        <input type="text" name="pendidikan_keluarga" id="pendidikan_keluarga" class="form-control @error('pendidikan_keluarga') is-invalid @enderror"
               value="{{ old('pendidikan_keluarga', $karyawan->pendidikan_keluarga ?? '') }}" placeholder="Pendidikan terakhir keluarga">
        @error('pendidikan_keluarga')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-12">
        <label for="alamat_keluarga" class="form-label fw-semibold text-sm">Alamat Keluarga</label>
        <textarea name="alamat_keluarga" id="alamat_keluarga" rows="2" class="form-control @error('alamat_keluarga') is-invalid @enderror"
                  placeholder="Masukkan alamat keluarga">{{ old('alamat_keluarga', $karyawan->alamat_keluarga ?? '') }}</textarea>
        @error('alamat_keluarga')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</div>

<!-- Akun Login -->
<div class="card shadow-1 radius-8 mb-0">
  <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
    <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
      <i class="ri-lock-line text-lg"></i> Akun Login
    </h6>
  </div>
  <div class="card-body p-24">
    <div class="row gy-lg-3">
      <div class="col-md-6">
        <label for="username" class="form-label fw-semibold text-sm">Username <span class="text-danger">*</span></label>
        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username', $karyawan->username ?? '') }}" placeholder="Masukkan username" required>
        @error('username')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="col-md-6">
        <label for="password" class="form-label fw-semibold text-sm">
          Password <span class="text-danger">{{ $karyawan ? '' : '*' }}</span>
        </label>
        <div class="position-relative">
          <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                 placeholder="{{ $karyawan ? 'Kosongkan jika tidak diubah' : 'Masukkan password' }}"
                 {{ $karyawan ? '' : 'required' }} minlength="6">
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
        <label for="status_akun" class="form-label fw-semibold text-sm">Status Akun <span class="text-danger">*</span></label>
        <select name="status_akun" id="status_akun" class="form-select @error('status_akun') is-invalid @enderror" required>
          <option value="aktif" {{ old('status_akun', $karyawan->status_akun ?? 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
          <option value="tidak_aktif" {{ old('status_akun', $karyawan->status_akun ?? 'aktif') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        </select>
        @error('status_akun')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>
  </div>
</div>
