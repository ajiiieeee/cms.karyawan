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

<div class="card shadow-1 radius-8 mb-24">
  <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
    <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
      <i class="ri-calendar-2-line text-lg"></i> Data Saldo Cuti
    </h6>
  </div>
  <div class="card-body p-24">
    <div class="row gy-lg-3">

      {{-- Karyawan --}}
      <div class="col-md-6">
        <label for="karyawan_id" class="form-label fw-semibold text-sm">
          Karyawan <span class="text-danger">*</span>
        </label>
        <select name="karyawan_id" id="karyawan_id"
                class="form-select @error('karyawan_id') is-invalid @enderror" required>
          <option value="">— Pilih Karyawan —</option>
          @foreach($karyawans as $karyawan)
            <option value="{{ $karyawan->id }}"
              {{ old('karyawan_id', $saldoCuti->karyawan_id ?? '') == $karyawan->id ? 'selected' : '' }}>
              {{ $karyawan->nama_karyawan }} ({{ $karyawan->nik }})
            </option>
          @endforeach
        </select>
        @error('karyawan_id')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      {{-- Total Cuti --}}
      <div class="col-md-6">
        <label for="total_cuti" class="form-label fw-semibold text-sm">
          Total Hari Cuti <span class="text-danger">*</span>
        </label>
        <div class="input-group">
          <input type="number" name="total_cuti" id="total_cuti" min="1" max="365"
                 class="form-control @error('total_cuti') is-invalid @enderror"
                 value="{{ old('total_cuti', $saldoCuti->total_cuti ?? '') }}"
                 placeholder="Contoh: 12" required>
          <span class="input-group-text bg-neutral-50 text-neutral-500 text-sm">hari</span>
          @error('total_cuti')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-text text-neutral-400 mt-4">Maksimal 12 hari dalam satu periode.</div>
      </div>

      {{-- Periode Mulai --}}
      <div class="col-md-6">
        <label for="periode_mulai" class="form-label fw-semibold text-sm">
          Tanggal Mulai Periode <span class="text-danger">*</span>
        </label>
        <div class="datepicker-wrapper position-relative" id="datepicker-mulai">
          <input type="text" name="periode_mulai" id="periode_mulai"
                 class="form-control pe-40 @error('periode_mulai') is-invalid @enderror" data-input
                 value="{{ old('periode_mulai', isset($saldoCuti) && $saldoCuti->periode_mulai ? $saldoCuti->periode_mulai->format('d/m/Y') : '') }}"
                 placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
          <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
            <i class="ri-calendar-line text-lg"></i>
          </span>
        </div>
        @error('periode_mulai')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      {{-- Periode Selesai --}}
      <div class="col-md-6">
        <label for="periode_selesai" class="form-label fw-semibold text-sm">
          Tanggal Selesai Periode <span class="text-danger">*</span>
        </label>
        <div class="datepicker-wrapper position-relative" id="datepicker-selesai">
          <input type="text" name="periode_selesai" id="periode_selesai"
                 class="form-control pe-40 @error('periode_selesai') is-invalid @enderror" data-input
                 value="{{ old('periode_selesai', isset($saldoCuti) && $saldoCuti->periode_selesai ? $saldoCuti->periode_selesai->format('d/m/Y') : '') }}"
                 placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
          <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
            <i class="ri-calendar-line text-lg"></i>
          </span>
        </div>
        @error('periode_selesai')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      {{-- Durasi info --}}
      <div class="col-md-12">
        <div id="periode-info" class="d-none">
          <div class="saldo-info-card">
            <i class="ri-information-line text-primary-600"></i>
            <span class="saldo-info-text">Durasi periode: <strong id="durasi-periode">—</strong></span>
          </div>
        </div>
      </div>

      {{-- Overlap warning --}}
      <div class="col-md-12">
        <div id="overlap-warning" class="d-none">
          <div class="saldo-info-card saldo-info-card--danger">
            <i class="ri-error-warning-line flex-shrink-0"></i>
            <span class="saldo-info-text">
              <strong>Periode sudah ada:</strong> <span id="overlap-message">—</span>
            </span>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
