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

<!-- Data Bidang Studi -->
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-1 radius-8 mb-24 h-100">
          <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
            <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
              <i class="ri-calendar-check-line text-lg"></i> Data Bidang Studi
            </h6>
          </div>
          <div class="card-body p-24">
            <div class="row gy-lg-3">
              <div class="col-md-6">
                <label for="nama_bidang_studi" class="form-label fw-semibold text-sm">Nama Bidang Studi <span class="text-danger">*</span></label>
                <input type="text" name="nama_bidang_studi" id="nama_bidang_studi" class="form-control @error('nama_bidang_studi') is-invalid @enderror"
                       placeholder="Masukkan nama bidang studi" maxlength="255" value="{{ old('nama_bidang_studi', $bidangStudi->nama_bidang_studi ?? '') }}">
                @error('nama_bidang_studi')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="deskripsi" class="form-label fw-semibold text-sm">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" class="form-control @error('deskripsi') is-invalid @enderror"
                          placeholder="Masukkan deskripsi bidang studi (opsional)" maxlength="1000">{{ old('deskripsi', $bidangStudi->deskripsi ?? '') }}</textarea>
                @error('deskripsi')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
    </div>
</div>