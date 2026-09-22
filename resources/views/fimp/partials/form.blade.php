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

<!-- Data Pengajuan FIMP -->
<div class="row">
    <div class="@if(isset($fimp)) col-lg-8 @else col-lg-12 @endif">
        <div class="card shadow-1 radius-8 mb-24 h-100">
          <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
            <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
              <i class="ri-file-list-3-line text-lg"></i> Data Pengajuan FIMP
            </h6>
          </div>
          <div class="card-body p-24">
            <div class="row gy-lg-3">
              <div class="col-md-6">
                <label for="karyawan_id" class="form-label fw-semibold text-sm">Karyawan <span class="text-danger">*</span></label>
                <select name="karyawan_id" id="karyawan_id" class="form-select @error('karyawan_id') is-invalid @enderror" required>
                  <option value="">— Pilih Karyawan —</option>
                  @foreach($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}" {{ old('karyawan_id', $fimp->karyawan_id ?? '') == $karyawan->id ? 'selected' : '' }}>
                      {{ $karyawan->nama_karyawan }} ({{ $karyawan->nik }})
                    </option>
                  @endforeach
                </select>
                @error('karyawan_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="karyawan_pengganti" class="form-label fw-semibold text-sm">Karyawan Pengganti <span class="text-danger">*</span></label>
                <select name="karyawan_pengganti" id="karyawan_pengganti" class="form-select @error('karyawan_pengganti') is-invalid @enderror" required>
                  <option value="">— Pilih Karyawan Pengganti —</option>
                  @foreach($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}" {{ old('karyawan_pengganti', $fimp->karyawan_pengganti ?? '') == $karyawan->id ? 'selected' : '' }}>
                      {{ $karyawan->nama_karyawan }} ({{ $karyawan->nik }})
                    </option>
                  @endforeach
                </select>
                @error('karyawan_pengganti')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="tanggal_awal" class="form-label fw-semibold text-sm">Tanggal Awal <span class="text-danger">*</span></label>
                <div class="datepicker-wrapper position-relative" id="datepicker-awal">
                  <input type="text" name="tanggal_awal" id="tanggal_awal" class="form-control pe-40 @error('tanggal_awal') is-invalid @enderror" data-input
                         value="{{ old('tanggal_awal', isset($fimp) && $fimp->tanggal_awal ? \Carbon\Carbon::parse($fimp->tanggal_awal)->format('d/m/Y') : '') }}"
                         placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
                  <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
                    <i class="ri-calendar-line text-lg"></i>
                  </span>
                </div>
                @error('tanggal_awal')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="tanggal_akhir" class="form-label fw-semibold text-sm">Tanggal Akhir <span class="text-danger">*</span></label>
                <div class="datepicker-wrapper position-relative" id="datepicker-akhir">
                  <input type="text" name="tanggal_akhir" id="tanggal_akhir" class="form-control pe-40 @error('tanggal_akhir') is-invalid @enderror" data-input
                         value="{{ old('tanggal_akhir', isset($fimp) && $fimp->tanggal_akhir ? \Carbon\Carbon::parse($fimp->tanggal_akhir)->format('d/m/Y') : '') }}"
                         placeholder="dd/mm/yyyy" required autocomplete="off" readonly>
                  <span class="datepicker-icon position-absolute top-50 end-0 translate-middle-y me-12 text-neutral-500 d-flex align-items-center" data-toggle>
                    <i class="ri-calendar-line text-lg"></i>
                  </span>
                </div>
                @error('tanggal_akhir')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="total_hari" class="form-label fw-semibold text-sm">Total Hari</label>
                <input type="text" id="total_hari" class="form-control bg-neutral-50"
                       value="{{ isset($fimp) ? $fimp->total_hari . ' hari' : '' }}"
                       placeholder="Otomatis dihitung" readonly>
              </div>

              <div class="col-12">
                <label for="keperluan" class="form-label fw-semibold text-sm">Keperluan <span class="text-danger">*</span></label>
                <textarea name="keperluan" id="keperluan" rows="3" class="form-control @error('keperluan') is-invalid @enderror"
                          placeholder="Masukkan keperluan izin meninggalkan pekerjaan" maxlength="2000" required>{{ old('keperluan', $fimp->keperluan ?? '') }}</textarea>
                @error('keperluan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <div class="d-flex flex-column gap-12 mt-8">
                  <div class="form-check">
                    <input class="form-check-input @error('pengganti_mengetahui') is-invalid @enderror" type="checkbox" name="pengganti_mengetahui" id="pengganti_mengetahui" value="1"
                           {{ old('pengganti_mengetahui', isset($fimp) && $fimp->pengganti_mengetahui ? '1' : '') ? 'checked' : '' }}>
                    <label class="form-check-label text-sm" for="pengganti_mengetahui">
                      Karyawan pengganti sudah <strong>mengetahui</strong> perihal izin meninggalkan pekerjaan ini
                    </label>
                    @error('pengganti_mengetahui')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="form-check">
                    <input class="form-check-input @error('pengganti_bersedia') is-invalid @enderror" type="checkbox" name="pengganti_bersedia" id="pengganti_bersedia" value="1"
                           {{ old('pengganti_bersedia', isset($fimp) && $fimp->pengganti_bersedia ? '1' : '') ? 'checked' : '' }}>
                    <label class="form-check-label text-sm" for="pengganti_bersedia">
                      Karyawan pengganti <strong>bersedia</strong> menggantikan selama izin meninggalkan pekerjaan
                    </label>
                    @error('pengganti_bersedia')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
    {{-- Status Persetujuan Card --}}
    @if($fimp)
      <div class="col-lg-4">
          <div class="card shadow-1 radius-8 h-100">
              <div class="card-body p-24">
                  <h6 class="fw-semibold mb-0" style="font-size: .9375rem;">Status Persetujuan</h6>
                  <hr class="my-16" style="border-color: #e2e8f0;">

                  <div class="approval-timeline">
                  @foreach($steps as $index => $step)
                      @php
                      $isCompleted = $fimp->status_pengajuan >= $step['level'] && !$isRejected;
                      $isCompletedBeforeReject = $isRejected && $fimp->status_pengajuan >= $step['level'];
                      $isRejectLevel = $isRejected && $fimp->status_pengajuan === ($step['level'] - 1);
                      $isLast = $index === count($steps) - 1;

                      if ($isCompleted || $isCompletedBeforeReject) {
                          $lineColor = '#22c55e';
                      } elseif ($isRejectLevel) {
                          $lineColor = '#ef4444';
                      } else {
                          $lineColor = '#e2e8f0';
                      }
                      @endphp
                      <div class="tl-item">
                      <div class="d-flex align-items-start">
                          <div class="tl-indicator">
                          @if($isCompleted || $isCompletedBeforeReject)
                              <div class="tl-dot tl-dot--success"><i class="ri-check-line"></i></div>
                          @elseif($isRejectLevel)
                              <div class="tl-dot tl-dot--danger"><i class="ri-close-line"></i></div>
                          @else
                              <div class="tl-dot tl-dot--warning"><i class="ri-time-line"></i></div>
                          @endif
                          @if(!$isLast)
                              <div class="tl-line" style="background: {{ $lineColor }};"></div>
                          @endif
                          </div>
                          <div class="tl-content">
                          <span class="tl-title">{{ $step['label'] }}</span>
                          <div class="d-flex align-items-center gap-6 flex-wrap">
                              @if($isCompleted || $isCompletedBeforeReject)
                              <span class="tl-badge tl-badge--success">Disetujui</span>
                              <span class="tl-date">{{ $fimp->updated_date ? \Carbon\Carbon::parse($fimp->updated_date)->format('d/m/Y H:i') : '' }}</span>
                              @elseif($isRejectLevel)
                              <span class="tl-badge tl-badge--danger">Ditolak</span>
                              <span class="tl-date">{{ $fimp->updated_date ? \Carbon\Carbon::parse($fimp->updated_date)->format('d/m/Y H:i') : '' }}</span>
                              @else
                              <span class="tl-badge tl-badge--warning">Menunggu</span>
                              @endif
                          </div>
                          </div>
                      </div>

                      @if($isRejectLevel && $fimp->reject_statement)
                          <div class="tl-reject-box">
                          <div class="d-flex align-items-center gap-8 mb-6">
                              <i class="ri-error-warning-fill" style="font-size: 15px;"></i>
                              <strong>Alasan Penolakan</strong>
                          </div>
                          <p class="mb-0">{{ $fimp->reject_statement }}</p>
                          </div>
                      @endif
                      </div>
                  @endforeach
                  </div>
              </div>
          </div>
      </div>
    @endif
</div>
