@extends('layouts.main')

@section('title', ($requestItem->exists ? 'Ubah' : 'Ajukan') . ' Cuti')

@push('styles')
<style>
  .form-container-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  }
  .form-section-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .saldo-summary-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
  }
  .saldo-item-badge {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .upload-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 20px 16px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .upload-dropzone:hover {
    border-color: #487fff;
    background: #eff6ff;
  }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

  {{-- Navigasi Atas --}}
  <div class="mb-24">
    <div class="mb-2">
      <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-outline-neutral-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 fw-semibold">
        <i class="ri-arrow-left-line"></i> Kembali ke Riwayat Cuti
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show radius-12 p-16 mb-24 border-0 shadow-sm d-flex align-items-center justify-content-between" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="ri-checkbox-circle-fill text-success-600 text-xl"></i>
        <span class="text-neutral-800 fw-medium">{{ session('success') }}</span>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show radius-12 p-16 mb-24 border-0 shadow-sm" role="alert">
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="ri-error-warning-fill text-danger-600 text-xl"></i>
        <strong class="text-neutral-900">Periksa kembali data Anda:</strong>
      </div>
      <ul class="mb-0 ps-3 text-sm text-neutral-700">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Container Card Form --}}
  <div class="card form-container-card border-0 overflow-hidden mb-24">
    <div class="card-header bg-white border-bottom border-neutral-100 py-16 px-24">
      <div class="d-flex align-items-center gap-3">
        <div class="w-40-px h-40-px rounded-12 bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center">
          <i class="ri-edit-circle-line text-xl"></i>
        </div>
        <div>
          <h6 class="fw-bold text-neutral-900 mb-0">{{ $requestItem->exists ? 'Ubah Pengajuan Cuti' : 'Ajukan Cuti' }}</h6>
          <span class="text-xs text-neutral-500">Lengkapi formulir di bawah ini untuk mengajukan permohonan izin cuti</span>
        </div>
      </div>
    </div>

    <form 
      id="mainLeaveForm"
      method="POST" 
      action="{{ $requestItem->exists ? route('leave-requests.update', $requestItem) : route('leave-requests.store') }}"
      enctype="multipart/form-data"
    >
      @csrf
      @if($requestItem->exists)
        @method('PUT')
      @endif

      <div class="card-body p-24 p-md-32">
        
        {{-- Section 1: Informasi Permohonan --}}
        <div class="row g-4 mb-24">

          {{-- Jenis Cuti --}}
          <div class="col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Jenis Cuti <span class="text-danger">*</span>
            </label>
            <select name="jenis_cuti" id="form_jenis_cuti" class="form-select select2 radius-8 py-10" required>
              <option value="" disabled {{ !old('jenis_cuti', $requestItem->jenis_cuti) ? 'selected' : '' }}>-- Pilih Jenis Cuti --</option>
              @foreach($categories as $cat)
                <option 
                  value="{{ $cat->id }}" 
                  @selected(old('jenis_cuti', $requestItem->jenis_cuti) == $cat->id)
                  data-name="{{ $cat->nama_kategori }}"
                >
                  {{ $cat->nama_kategori }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Tanggal Mulai --}}
          <div class="col-md-6 col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Tanggal Mulai <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text bg-white text-neutral-500 border-end-0 radius-start-8">
                <i class="ri-calendar-event-line"></i>
              </span>
              <input 
                type="date" 
                class="form-control radius-end-8 py-10 border-start-0 ps-0" 
                id="form_tanggal_awal" 
                name="tanggal_awal" 
                value="{{ old('tanggal_awal', optional($requestItem->tanggal_awal)->format('Y-m-d')) }}" 
                required
              >
            </div>
          </div>

          {{-- Tanggal Selesai --}}
          <div class="col-md-6 col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Tanggal Selesai <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <span class="input-group-text bg-white text-neutral-500 border-end-0 radius-start-8">
                <i class="ri-calendar-check-line"></i>
              </span>
              <input 
                type="date" 
                class="form-control radius-end-8 py-10 border-start-0 ps-0" 
                id="form_tanggal_akhir" 
                name="tanggal_akhir" 
                value="{{ old('tanggal_akhir', optional($requestItem->tanggal_akhir)->format('Y-m-d')) }}" 
                required
              >
            </div>
          </div>

          {{-- Durasi Cuti --}}
          <div class="col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Durasi Cuti
            </label>
            <div class="d-flex align-items-center gap-2">
              <div class="p-12 px-18 radius-8 border border-neutral-300 fw-bold text-base" 
                  id="formDurasiDisplay" 
                  style="color: #0f172a !important; background-color: #f1f5f9 !important;">
                {{ $requestItem->jumlah_hari ? $requestItem->jumlah_hari . ' Hari' : '0 Hari' }}
              </div>
              <span class="text-xs text-neutral-500">Otomatis dihitung berdasarkan rentang tanggal mulai dan selesai.</span>
            </div>
          </div>

          {{-- Alasan Cuti --}}
          <div class="col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Alasan Cuti <span class="text-danger">*</span>
            </label>
            <textarea 
              name="keterangan" 
              id="form_keterangan" 
              class="form-control radius-8 p-12 text-sm" 
              rows="4" 
              placeholder="Tuliskan alasan atau keperluan izin cuti Anda secara rinci..."
              required
            >{{ old('keterangan', $requestItem->keterangan) }}</textarea>
          </div>

          {{-- Lampiran --}}
          <div class="col-12">
            <label class="form-label text-sm fw-semibold text-neutral-800">
              Lampiran Dokumen <span class="text-neutral-400 fw-normal">(Opsional)</span>
            </label>

            @if($requestItem->lampiran)
              <div class="mb-2 p-10 radius-8 bg-neutral-100 border border-neutral-200 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <i class="ri-attachment-2 text-primary-600"></i>
                  <span class="text-xs fw-medium text-neutral-800">Lampiran sebelumnya sudah terunggah</span>
                </div>
                <a href="{{ asset($requestItem->lampiran) }}" target="_blank" class="btn btn-sm btn-link text-primary-600 p-0 text-xs">
                  Lihat File
                </a>
              </div>
            @endif
            
            <div class="upload-dropzone" id="formDropzone" onclick="document.getElementById('form_lampiran').click();">
              <input type="file" name="lampiran" id="form_lampiran" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
              <i class="ri-upload-cloud-2-line text-3xl text-primary-600 mb-2 d-block"></i>
              <p class="text-sm fw-semibold text-neutral-800 mb-1" id="formFileLabel">
                Klik untuk memilih file dokumen pendukung (Upload File)
              </p>
              <span class="text-xs text-neutral-500 d-block">Format didukung: PDF, JPG, JPEG, PNG (Maksimal 2MB)</span>
            </div>

            <div id="formFilePreview" class="d-none mt-2 p-10 radius-8 bg-neutral-100 border border-neutral-200 d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2 overflow-hidden">
                <i class="ri-file-text-line text-primary-600 text-lg flex-shrink-0"></i>
                <span class="text-xs fw-medium text-neutral-800 text-truncate" id="formFileName">-</span>
              </div>
              <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" id="formBtnRemoveFile">
                <i class="ri-close-line text-lg"></i>
              </button>
            </div>
          </div>

        </div>

        {{-- Divider Garis --}}
        <hr class="border-neutral-200 my-24">

        {{-- Section 2: Saldo Cuti (Sesuai Permintaan Section 7 & 8) --}}
        <div class="saldo-summary-box mb-24">
          <div class="d-flex align-items-center justify-content-between mb-16">
            <h6 class="fw-bold text-neutral-900 text-sm mb-0 d-flex align-items-center gap-2">
              <i class="ri-wallet-3-line text-primary-600"></i> Informasi Saldo Cuti
            </h6>
            <span class="badge bg-white text-neutral-600 border border-neutral-200 px-10 py-4 text-xs fw-semibold radius-6">
              Periode {{ $periodeAktif }}
            </span>
          </div>

          <div class="row g-3">
            
            {{-- Saldo Awal --}}
            <div class="col-md-4 col-12">
              <div class="saldo-item-badge">
                <span class="text-xs text-neutral-600 fw-medium">Saldo Awal</span>
                <strong class="text-neutral-900 text-sm">{{ $sisaSaldoCuti }} Hari</strong>
              </div>
            </div>

            {{-- Pengajuan --}}
            <div class="col-md-4 col-12">
              <div class="saldo-item-badge border-primary-200 bg-primary-50">
                <span class="text-xs text-primary-700 fw-medium">Pengajuan</span>
                <strong class="text-primary-800 text-sm" id="boxPengajuanHari">
                  {{ $requestItem->jumlah_hari ? $requestItem->jumlah_hari . ' Hari' : '0 Hari' }}
                </strong>
              </div>
            </div>

            {{-- Sisa --}}
            <div class="col-md-4 col-12">
              <div class="saldo-item-badge border-success-200 bg-success-50" id="boxSisaContainer">
                <span class="text-xs text-success-700 fw-medium">Sisa Setelah Pengajuan</span>
                <strong class="text-success-800 text-sm" id="boxSisaHari">
                  {{ $requestItem->jumlah_hari ? max(0, $sisaSaldoCuti - (int)$requestItem->jumlah_hari) . ' Hari' : $sisaSaldoCuti . ' Hari' }}
                </strong>
              </div>
            </div>

          </div>

          {{-- Alert Peringatan Jika Saldo Kurang --}}
          <div id="formSaldoWarningAlert" class="alert alert-danger radius-10 p-14 text-xs mt-16 mb-0 d-none">
            <div class="d-flex align-items-start gap-2">
              <i class="ri-error-warning-fill text-danger-600 text-base flex-shrink-0"></i>
              <div>
                <strong>Saldo cuti tidak mencukupi!</strong>
                <div id="formSaldoWarningText" class="mt-1">
                  Durasi pengajuan melebihi sisa saldo cuti Anda. Silakan kurangi rentang tanggal pengajuan.
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      {{-- Action Buttons --}}
      <div class="card-footer bg-white border-top border-neutral-100 py-16 px-24 d-flex justify-content-end gap-2">
        <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-neutral-500 radius-8 px-20 py-10 fw-semibold text-sm">
          Batal
        </a>
        <button 
          type="submit" 
          id="btnSubmitMainForm" 
          class="btn btn-primary-600 radius-8 px-24 py-10 fw-semibold text-sm d-inline-flex align-items-center gap-2 shadow-sm"
        >
          <span class="spinner-border spinner-border-sm d-none" id="formSubmitSpinner" role="status" aria-hidden="true"></span>
          <i class="ri-send-plane-fill" id="formSubmitIcon"></i>
          <span id="formSubmitText">{{ $requestItem->exists ? 'Simpan Perubahan' : 'Ajukan Cuti' }}</span>
        </button>
      </div>

    </form>
  </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const sisaSaldoCurrent = {{ $sisaSaldoCuti }};
    const totalHakCuti = {{ $totalHakCuti }};

    const startDateInput = document.getElementById('form_tanggal_awal');
    const endDateInput = document.getElementById('form_tanggal_akhir');
    const durasiDisplay = document.getElementById('formDurasiDisplay');
    const boxPengajuan = document.getElementById('boxPengajuanHari');
    const boxSisa = document.getElementById('boxSisaHari');
    const boxSisaContainer = document.getElementById('boxSisaContainer');
    const warningAlert = document.getElementById('formSaldoWarningAlert');
    const warningText = document.getElementById('formSaldoWarningText');
    const submitBtn = document.getElementById('btnSubmitMainForm');
    const form = document.getElementById('mainLeaveForm');

    function calculateLeaveDuration() {
      const startVal = startDateInput.value;
      const endVal = endDateInput.value;

      if (!startVal || !endVal) {
        durasiDisplay.textContent = '0 Hari';
        boxPengajuan.textContent = '0 Hari';
        boxSisa.textContent = `${sisaSaldoCurrent} Hari`;
        boxSisaContainer.className = 'saldo-item-badge border-success-200 bg-success-50';
        boxSisa.className = 'text-success-800 text-sm';
        warningAlert.classList.add('d-none');
        submitBtn.disabled = false;
        return;
      }

      const startDate = new Date(startVal);
      const endDate = new Date(endVal);

      if (endDate < startDate) {
        warningAlert.classList.remove('d-none');
        warningText.textContent = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        durasiDisplay.textContent = '-';
        boxPengajuan.textContent = '-';
        boxSisa.textContent = '-';
        submitBtn.disabled = true;
        return;
      }

      const diffTime = Math.abs(endDate - startDate);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

      durasiDisplay.textContent = `${diffDays} Hari`;
      boxPengajuan.textContent = `${diffDays} Hari`;

      const remainingAfter = sisaSaldoCurrent - diffDays;
      boxSisa.textContent = `${remainingAfter} Hari`;

      if (diffDays > sisaSaldoCurrent) {
        warningAlert.classList.remove('d-none');
        warningText.textContent = `Saldo cuti Anda tidak mencukupi! Anda mengajukan ${diffDays} hari, sedangkan sisa saldo Anda hanya ${sisaSaldoCurrent} hari.`;
        boxSisaContainer.className = 'saldo-item-badge border-danger-200 bg-danger-50';
        boxSisa.className = 'text-danger-800 text-sm';
        submitBtn.disabled = true;
      } else {
        warningAlert.classList.add('d-none');
        boxSisaContainer.className = 'saldo-item-badge border-success-200 bg-success-50';
        boxSisa.className = 'text-success-800 text-sm';
        submitBtn.disabled = false;
      }
    }

    startDateInput.addEventListener('change', function () {
      if (startDateInput.value && !endDateInput.value) {
        endDateInput.value = startDateInput.value;
      }
      if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
      }
      calculateLeaveDuration();
    });

    endDateInput.addEventListener('change', calculateLeaveDuration);

    if (startDateInput.value && endDateInput.value) {
      calculateLeaveDuration();
    }

    // File Upload Preview
    const fileInput = document.getElementById('form_lampiran');
    const filePreview = document.getElementById('formFilePreview');
    const fileName = document.getElementById('formFileName');
    const btnRemoveFile = document.getElementById('formBtnRemoveFile');

    fileInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        if (file.size > 2 * 1024 * 1024) {
          alert('Ukuran file maksimal adalah 2MB.');
          this.value = '';
          filePreview.classList.add('d-none');
          return;
        }
        fileName.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        filePreview.classList.remove('d-none');
        filePreview.classList.add('d-flex');
      }
    });

    btnRemoveFile.addEventListener('click', function (e) {
      e.stopPropagation();
      fileInput.value = '';
      filePreview.classList.add('d-none');
      filePreview.classList.remove('d-flex');
    });

    // Form Submit Loading State
    form.addEventListener('submit', function (e) {
      const spinner = document.getElementById('formSubmitSpinner');
      const icon = document.getElementById('formSubmitIcon');
      const text = document.getElementById('formSubmitText');

      submitBtn.disabled = true;
      spinner.classList.remove('d-none');
      icon.classList.add('d-none');
      text.textContent = 'Memproses...';
    });
  });
</script>
@endpush
