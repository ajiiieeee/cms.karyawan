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

<!-- Data Pengajuan Cuti -->
<div class="row">
    <div class="@if(isset($pengajuanCuti)) col-lg-8 @else col-lg-12 @endif">
        <div class="card shadow-1 radius-8 mb-24 h-100">
          <div class="card-header bg-neutral-50 py-16 px-24 radius-8">
            <h6 class="fw-semibold mb-0 text-primary-light d-flex align-items-center gap-8">
              <i class="ri-calendar-check-line text-lg"></i> Data Pengajuan Cuti
            </h6>
          </div>
          <div class="card-body p-24">
            <div class="row gy-lg-3">
              <div class="col-md-6">
                <label for="karyawan_id" class="form-label fw-semibold text-sm">Karyawan <span class="text-danger">*</span></label>
                <select name="karyawan_id" id="karyawan_id" class="form-select @error('karyawan_id') is-invalid @enderror" required>
                  <option value="">— Pilih Karyawan —</option>
                  @foreach($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}" {{ old('karyawan_id', $pengajuanCuti->karyawan_id ?? '') == $karyawan->id ? 'selected' : '' }}>
                      {{ $karyawan->nama_karyawan }} ({{ $karyawan->nik }})
                    </option>
                  @endforeach
                </select>
                @error('karyawan_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
        
              <div class="col-md-6">
                <label for="jenis_cuti" class="form-label fw-semibold text-sm">Jenis Cuti <span class="text-danger">*</span></label>
                <select name="jenis_cuti" id="jenis_cuti" class="form-select @error('jenis_cuti') is-invalid @enderror" required>
                  <option value="">— Pilih Jenis Cuti —</option>
                  @foreach($kategoriCutis as $kategori)
                    <option value="{{ $kategori->id }}" {{ old('jenis_cuti', $pengajuanCuti->jenis_cuti ?? '') == $kategori->id ? 'selected' : '' }}>
                      {{ $kategori->nama_kategori }}
                    </option>
                  @endforeach
                </select>
                @error('jenis_cuti')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
        
              <div class="col-md-4">
                <label for="tanggal_awal" class="form-label fw-semibold text-sm">Tanggal Awal <span class="text-danger">*</span></label>
                <div class="datepicker-wrapper position-relative" id="datepicker-awal">
                  <input type="text" name="tanggal_awal" id="tanggal_awal" class="form-control pe-40 @error('tanggal_awal') is-invalid @enderror" data-input
                         value="{{ old('tanggal_awal', isset($pengajuanCuti) && $pengajuanCuti->tanggal_awal ? \Carbon\Carbon::parse($pengajuanCuti->tanggal_awal)->format('d/m/Y') : '') }}"
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
                         value="{{ old('tanggal_akhir', isset($pengajuanCuti) && $pengajuanCuti->tanggal_akhir ? \Carbon\Carbon::parse($pengajuanCuti->tanggal_akhir)->format('d/m/Y') : '') }}"
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
                <label for="jumlah_hari" class="form-label fw-semibold text-sm">Jumlah Hari</label>
                <input type="text" id="jumlah_hari" class="form-control bg-neutral-50" 
                       value="{{ isset($pengajuanCuti) ? $pengajuanCuti->jumlah_hari . ' hari' : '' }}" 
                       placeholder="Otomatis dihitung" readonly>
              </div>
        
              <div class="col-12">
                <label for="keterangan" class="form-label fw-semibold text-sm">Keterangan</label>
                <textarea name="keterangan" id="keterangan" rows="3" class="form-control @error('keterangan') is-invalid @enderror"
                          placeholder="Masukkan keterangan cuti (opsional)" maxlength="1000">{{ old('keterangan', $pengajuanCuti->keterangan ?? '') }}</textarea>
                @error('keterangan')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- Saldo quota check (CUTI TAHUNAN only) --}}
              <div class="col-12">
                <div id="saldo-available" class="d-none">
                  <div class="saldo-check-card saldo-check-card--ok">
                    <i class="ri-shield-check-line flex-shrink-0"></i>
                    <span>
                      Saldo tersisa: <strong id="saldo-sisa-val">—</strong> hari
                      &nbsp;·&nbsp; Terpakai: <span id="saldo-terpakai-val">—</span> / <span id="saldo-total-val">—</span> hari
                      &nbsp;·&nbsp; Periode: <span id="saldo-periode-val">—</span>
                    </span>
                  </div>
                </div>
                <div id="saldo-exceeded" class="d-none">
                  <div class="saldo-check-card saldo-check-card--danger">
                    <i class="ri-error-warning-line flex-shrink-0"></i>
                    <span>
                      <strong>Saldo tidak cukup:</strong>
                      Sisa <strong id="saldo-sisa-exc">—</strong> hari, pengajuan <strong id="saldo-hari-exc">—</strong> hari.
                      Kurangi jumlah hari atau sesuaikan periode.
                    </span>
                  </div>
                </div>
                <div id="saldo-not-found" class="d-none">
                  <div class="saldo-check-card saldo-check-card--warning">
                    <i class="ri-information-line flex-shrink-0"></i>
                    <span>Data saldo cuti tahunan belum tersedia untuk karyawan ini pada periode yang dipilih.</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>
    {{-- Status Persetujuan Card --}}
    @if($pengajuanCuti)
      <div class="col-lg-4">
          <div class="card shadow-1 radius-8 h-100">
              <div class="card-body p-24">
                  <h6 class="fw-semibold mb-0" style="font-size: .9375rem;">Status Persetujuan</h6>
                  <hr class="my-16" style="border-color: #e2e8f0;">

                  <div class="approval-timeline">
                  @foreach($steps as $index => $step)
                      @php
                      $isCompleted = $pengajuanCuti->status_approval >= $step['level'] && !$isRejected;
                      $isCompletedBeforeReject = $isRejected && $pengajuanCuti->status_approval >= $step['level'];
                      $isRejectLevel = $isRejected && $pengajuanCuti->status_approval === ($step['level'] - 1);
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
                              <span class="tl-date">{{ $pengajuanCuti->updated_date ? \Carbon\Carbon::parse($pengajuanCuti->updated_date)->format('d/m/Y H:i') : '' }}</span>
                              @elseif($isRejectLevel)
                              <span class="tl-badge tl-badge--danger">Ditolak</span>
                              <span class="tl-date">{{ $pengajuanCuti->updated_date ? \Carbon\Carbon::parse($pengajuanCuti->updated_date)->format('d/m/Y H:i') : '' }}</span>
                              @else
                              <span class="tl-badge tl-badge--warning">Menunggu</span>
                              @endif
                          </div>
                          </div>
                      </div>

                      @if($isRejectLevel && $pengajuanCuti->reject_statement)
                          <div class="tl-reject-box">
                          <div class="d-flex align-items-center gap-8 mb-6">
                              <i class="ri-error-warning-fill" style="font-size: 15px;"></i>
                              <strong>Alasan Penolakan</strong>
                          </div>
                          <p class="mb-0">{{ $pengajuanCuti->reject_statement }}</p>
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
