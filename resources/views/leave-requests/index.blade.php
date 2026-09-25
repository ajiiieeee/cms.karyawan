@extends('layouts.main')

@section('title', 'Form Izin Cuti')

@push('styles')
<style>
  /* Custom SweetAlert Delete Modal Styling */
.delete-popup {
    border-radius: 20px !important;
    padding: 28px 24px !important;
    max-width: 440px !important;
}

.delete-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background-color: #fee2e2; /* Soft Red / Danger-50 */
    color: #ef4444; /* Danger-600 */
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px auto;
    font-size: 28px;
}

.delete-title {
    color: #0f172a;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.delete-text {
    color: #64748b;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 24px;
}

.delete-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-delete-cancel {
    background-color: #f1f5f9;
    color: #475569;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-delete-cancel:hover {
    background-color: #e2e8f0;
    color: #1e293b;
}

.btn-delete-confirm {
    background-color: #ef4444;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.btn-delete-confirm:hover {
    background-color: #dc2626;
    color: #ffffff;
}
  /* 1. Metric Cards Colorful Styles */
  .duration-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 16px;
    min-width: 78px;
    height: 28px;
    border-radius: 999px;
    background-color: #e9ecef;
    color: #343a40 !important;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
}
  .leave-metric-card {
    transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.24s cubic-bezier(0.16, 1, 0.3, 1);
    border-radius: 16px;
    position: relative;
    overflow: hidden;
  }
  .leave-metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(15, 23, 42, 0.1) !important;
  }

  .metric-total {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
  }
  .metric-menunggu {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fde68a;
  }
  .metric-disetujui {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #a7f3d0;
  }
  .metric-ditolak {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border: 1px solid #fecdd3;
  }

  .icon-box-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    box-shadow: 0 6px 12px rgba(37, 99, 235, 0.25);
  }
  .icon-box-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 6px 12px rgba(217, 119, 6, 0.25);
  }
  .icon-box-success {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    box-shadow: 0 6px 12px rgba(5, 150, 105, 0.25);
  }
  .icon-box-danger {
    background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%);
    box-shadow: 0 6px 12px rgba(190, 18, 60, 0.25);
  }

  /* 2. Saldo Cards Styles */
  .saldo-outer-card {
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  }
  .saldo-box-item {
    border-radius: 14px;
    padding: 16px 18px;
    transition: all 0.2s ease;
  }
  .saldo-box-item:hover {
    transform: translateY(-2px);
  }
  
  .saldo-theme-blue {
    background: #f0f7ff;
    border: 1px solid #dbeafe;
  }
  .saldo-theme-teal {
    background: #f0fdfa;
    border: 1px solid #ccfbf1;
  }
  .saldo-theme-amber {
    background: #fffbeb;
    border: 1px solid #fef3c7;
  }
  .saldo-theme-indigo {
    background: #eef2ff;
    border: 1px solid #e0e7ff;
  }
  .saldo-theme-purple {
    background: #faf5ff;
    border: 1px solid #f3e8ff;
  }

  /* Sisa Saldo Cuti Paling Menonjol */
  .saldo-hero-card {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    border-radius: 14px;
    padding: 20px;
    color: #ffffff;
    box-shadow: 0 10px 24px -4px rgba(37, 99, 235, 0.35);
    position: relative;
    overflow: hidden;
  }
  .saldo-hero-card::after {
    content: '';
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    pointer-events: none;
  }

  /* 3. Riwayat Card & Tabs */
  .riwayat-outer-card {
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  }
  .leave-tabs {
    border-bottom: 0;
    gap: 8px;
    background: #f1f5f9;
    padding: 6px;
    border-radius: 12px;
    display: inline-flex;
  }
  .leave-tabs .nav-link {
    border: none;
    color: #64748b;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 8px 18px;
    border-radius: 8px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .leave-tabs .nav-link:hover {
    color: #1e293b;
    background: rgba(255, 255, 255, 0.7);
  }
  .leave-tabs .nav-link.active {
    color: #2563eb;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }
  .leave-tabs .nav-link .badge-count {
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 10px;
  }

  /* Internal Table Container Spacing */
  .table-inner-container {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
  }
  .leave-table th {
    font-size: 0.775rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 700;
    color: #475569;
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 18px;
    white-space: nowrap;
  }
  .leave-table td {
    padding: 14px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.875rem;
  }
  .leave-table tbody tr {
    transition: background-color 0.15s ease;
  }
  .leave-table tbody tr:hover {
    background-color: #f8fafc;
  }

  /* Empty state */
  .empty-state-box {
    padding: 56px 24px;
    text-align: center;
  }
  .empty-state-icon {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    margin-bottom: 16px;
  }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

  {{-- Header Halaman & Tombol Ajukan Cuti --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
      <h5 class="fw-bold text-neutral-900 mb-1">Form Izin Cuti</h5>
      <p class="text-neutral-500 mb-0 text-sm">
        Pantau sisa saldo, ajukan permohonan cuti baru, dan tinjau status persetujuan secara real-time.
      </p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a 
        href="{{ route('leave-requests.create') }}" 
        class="btn btn-outline-primary-600 radius-8 px-18 py-10 d-inline-flex align-items-center gap-2 fw-semibold shadow-sm"
      >
        <i class="ri-add-line text-lg"></i>
        <span>Ajukan Cuti</span>
      </a>
    </div>
  </div>

  {{-- Notifikasi Sukses / Peringatan --}}
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

  {{-- ==================================================
       1. SUMMARY CARDS (LEBIH BERWARNA & BERKARAKTER)
       ================================================== --}}
  <div class="row g-3 g-xl-4 mb-24">
    
    {{-- Card 1: Total Formulir Izin --}}
    <div class="col-xl-3 col-sm-6 col-12">
      <div class="card leave-metric-card metric-total p-20 h-100 shadow-none">
        <div class="d-flex align-items-center justify-content-between mb-12">
          <span class="text-neutral-700 fw-semibold text-sm">Total Formulir Izin</span>
          <div class="w-44-px h-44-px rounded-12 icon-box-primary text-white d-flex justify-content-center align-items-center flex-shrink-0">
            <i class="ri-file-text-line text-2xl"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h3 class="mb-0 fw-bold text-primary-800">{{ $totalPengajuan }}</h3>
          <span class="text-xs text-primary-600 fw-medium">Pengajuan</span>
        </div>
        <p class="text-xs text-primary-700 mt-8 mb-0 d-flex align-items-center gap-1 fw-medium">
          <i class="ri-history-line"></i>
          <span>Seluruh permohonan diajukan</span>
        </p>
      </div>
    </div>

    {{-- Card 2: Menunggu --}}
    <div class="col-xl-3 col-sm-6 col-12">
      <div class="card leave-metric-card metric-menunggu p-20 h-100 shadow-none">
        <div class="d-flex align-items-center justify-content-between mb-12">
          <span class="text-neutral-700 fw-semibold text-sm">Menunggu</span>
          <div class="w-44-px h-44-px rounded-12 icon-box-warning text-white d-flex justify-content-center align-items-center flex-shrink-0">
            <i class="ri-time-line text-2xl"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h3 class="mb-0 fw-bold text-warning-800">{{ $totalMenunggu }}</h3>
          <span class="text-xs text-warning-700 fw-medium">Menunggu</span>
        </div>
        <p class="text-xs text-warning-700 mt-8 mb-0 d-flex align-items-center gap-1 fw-medium">
          <i class="ri-hourglass-2-line"></i>
          <span>Dalam proses persetujuan atasan</span>
        </p>
      </div>
    </div>

    {{-- Card 3: Disetujui --}}
    <div class="col-xl-3 col-sm-6 col-12">
      <div class="card leave-metric-card metric-disetujui p-20 h-100 shadow-none">
        <div class="d-flex align-items-center justify-content-between mb-12">
          <span class="text-neutral-700 fw-semibold text-sm">Disetujui</span>
          <div class="w-44-px h-44-px rounded-12 icon-box-success text-white d-flex justify-content-center align-items-center flex-shrink-0">
            <i class="ri-checkbox-circle-line text-2xl"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h3 class="mb-0 fw-bold text-success-800">{{ $totalDisetujui }}</h3>
          <span class="text-xs text-success-700 fw-medium">Disetujui</span>
        </div>
        <p class="text-xs text-success-700 mt-8 mb-0 d-flex align-items-center gap-1 fw-medium">
          <i class="ri-check-double-line"></i>
          <span>Telah diverifikasi & disetujui</span>
        </p>
      </div>
    </div>

    {{-- Card 4: Ditolak --}}
    <div class="col-xl-3 col-sm-6 col-12">
      <div class="card leave-metric-card metric-ditolak p-20 h-100 shadow-none">
        <div class="d-flex align-items-center justify-content-between mb-12">
          <span class="text-neutral-700 fw-semibold text-sm">Ditolak</span>
          <div class="w-44-px h-44-px rounded-12 icon-box-danger text-white d-flex justify-content-center align-items-center flex-shrink-0">
            <i class="ri-close-circle-line text-2xl"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h3 class="mb-0 fw-bold text-danger-800">{{ $totalDitolak }}</h3>
          <span class="text-xs text-danger-700 fw-medium">Ditolak</span>
        </div>
        <p class="text-xs text-danger-700 mt-8 mb-0 d-flex align-items-center gap-1 fw-medium">
          <i class="ri-error-warning-line"></i>
          <span>Permohonan tidak disetujui</span>
        </p>
      </div>
    </div>

  </div>

  {{-- ==================================================
       2. CARD INFORMASI SALDO CUTI (LEBIH BERWARNA & ICON LENGKAP)
       ================================================== --}}
  <div class="card saldo-outer-card mb-24 overflow-hidden border-0">
    <div class="card-header bg-white border-bottom border-neutral-100 py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div class="d-flex align-items-center gap-3">
        <div class="w-38-px h-38-px rounded-10 bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center">
          <i class="ri-wallet-3-line text-xl"></i>
        </div>
        <div>
          <h6 class="fw-bold text-neutral-900 mb-0">Informasi Saldo Cuti</h6>
          <span class="text-xs text-neutral-500">Ringkasan hak cuti dan pemakaian cuti tahunan pegawai</span>
        </div>
      </div>
      <div>
        <span class="badge bg-primary-50 text-primary-700 border border-primary-200 px-12 py-6 radius-8 fw-semibold text-xs">
          <i class="ri-calendar-line me-1"></i> Periode {{ $periodeAktif }}
        </span>
      </div>
    </div>

    <div class="card-body p-20 p-md-24">
      <div class="row g-3 g-md-4">

        {{-- 1. Periode Aktif --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-box-item saldo-theme-blue h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="w-28-px h-28-px rounded-6 bg-blue-100 text-blue-700 d-flex align-items-center justify-content-center">
                <i class="ri-calendar-2-line text-sm"></i>
              </div>
              <span class="text-xs text-blue-700 fw-bold text-uppercase tracking-wider">Periode Aktif</span>
            </div>
            <div>
              <h4 class="fw-bold text-neutral-900 mb-0">{{ $periodeAktif }}</h4>
              <span class="text-xs text-neutral-500 mt-1 d-block">Tahun kalender kerja berjalan</span>
            </div>
          </div>
        </div>

        {{-- 2. Jumlah Saldo Cuti --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-box-item saldo-theme-teal h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="w-28-px h-28-px rounded-6 bg-teal-100 text-teal-700 d-flex align-items-center justify-content-center">
                <i class="ri-sun-line text-sm"></i>
              </div>
              <span class="text-xs text-teal-700 fw-bold text-uppercase tracking-wider">Jumlah Saldo Cuti</span>
            </div>
            <div>
              <h4 class="fw-bold text-neutral-900 mb-0">{{ $totalHakCuti }} <span class="text-sm fw-normal text-neutral-600">Hari</span></h4>
              <span class="text-xs text-neutral-500 mt-1 d-block">Hak cuti tahunan reguler</span>
            </div>
          </div>
        </div>

        {{-- 3. Saldo Terpakai --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-box-item saldo-theme-amber h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="w-28-px h-28-px rounded-6 bg-amber-100 text-amber-700 d-flex align-items-center justify-content-center">
                <i class="ri-pie-chart-2-line text-sm"></i>
              </div>
              <span class="text-xs text-amber-700 fw-bold text-uppercase tracking-wider">Saldo Terpakai</span>
            </div>
            <div>
              <h4 class="fw-bold text-neutral-900 mb-0">{{ $saldoTerpakai }} <span class="text-sm fw-normal text-neutral-600">Hari</span></h4>
              <span class="text-xs text-neutral-500 mt-1 d-block">Cuti disetujui periode ini</span>
            </div>
          </div>
        </div>

        {{-- 4. SISA SALDO CUTI (INFORMASI PALING MENONJOL) --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-hero-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2">
                <div class="w-28-px h-28-px rounded-6 bg-white text-primary-700 d-flex align-items-center justify-content-center">
                  <i class="ri-check-line text-sm fw-bold"></i>
                </div>
                <span class="text-xs text-white text-opacity-90 fw-bold text-uppercase tracking-wider">Sisa Saldo Cuti</span>
              </div>
              <span class="badge bg-white text-primary-700 px-8 py-3 text-2xs fw-bold radius-pill">
                Tersedia
              </span>
            </div>

            <div>
              <div class="d-flex align-items-baseline gap-2 mb-2">
                <h3 class="fw-bold text-white mb-0">{{ $sisaSaldoCuti }}</h3>
                <span class="text-sm fw-medium text-white text-opacity-90">Hari Tersisa</span>
              </div>

              {{-- Progress Bar Menarik --}}
              @php
                $persentaseSisa = $totalHakCuti > 0 ? min(100, round(($sisaSaldoCuti / $totalHakCuti) * 100)) : 0;
              @endphp
              <div class="mb-1">
                <div class="d-flex justify-content-between text-2xs text-white text-opacity-90 fw-medium mb-1">
                  <span>Terpakai: {{ $saldoTerpakai }} Hari</span>
                  <span>Total: {{ $totalHakCuti }} Hari</span>
                </div>
                <div class="progress" style="height: 7px; border-radius: 6px; background-color: rgba(255, 255, 255, 0.25);">
                  <div class="progress-bar bg-white" role="progressbar" style="width: {{ $persentaseSisa }}%; border-radius: 6px;" aria-valuenow="{{ $sisaSaldoCuti }}" aria-valuemin="0" aria-valuemax="{{ $totalHakCuti }}"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- 5. Tanggal Bergabung --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-box-item saldo-theme-indigo h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="w-28-px h-28-px rounded-6 bg-indigo-100 text-indigo-700 d-flex align-items-center justify-content-center">
                <i class="ri-user-shared-line text-sm"></i>
              </div>
              <span class="text-xs text-indigo-700 fw-bold text-uppercase tracking-wider">Tanggal Bergabung</span>
            </div>
            <div>
              <h5 class="fw-bold text-neutral-900 mb-0">
                {{ $tanggalBergabung ? $tanggalBergabung->translatedFormat('d F Y') : '-' }}
              </h5>
              <span class="text-xs text-neutral-500 mt-1 d-block">Mulai aktif bekerja di perusahaan</span>
            </div>
          </div>
        </div>

        {{-- 6. Sisa Cuti Periode Sebelumnya --}}
        <div class="col-lg-4 col-md-6 col-12">
          <div class="saldo-box-item saldo-theme-purple h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="w-28-px h-28-px rounded-6 bg-purple-100 text-purple-700 d-flex align-items-center justify-content-center">
                <i class="ri-history-line text-sm"></i>
              </div>
              <span class="text-xs text-purple-700 fw-bold text-uppercase tracking-wider">Sisa Periode Sebelumnya</span>
            </div>
            <div>
              <h4 class="fw-bold text-neutral-900 mb-0">{{ $sisaPeriodeSebelumnya }} <span class="text-sm fw-normal text-neutral-600">Hari</span></h4>
              <span class="text-xs text-neutral-500 mt-1 d-block">Carryover cuti tahun sebelumnya</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ==================================================
       3, 4, 5. CARD RIWAYAT PENGAJUAN CUTI & TABEL
       (DENGAN INTERNAL PADDING & MARGIN PROPORSIAL)
       ================================================== --}}
  <div class="card riwayat-outer-card border-0 mb-24 overflow-hidden">
    <div class="card-header bg-white border-bottom border-neutral-100 py-18 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h6 class="fw-bold text-neutral-900 mb-0">Riwayat Pengajuan Cuti</h6>
        <span class="text-xs text-neutral-500">Kelola dan telusuri permohonan cuti Anda berdasarkan status</span>
      </div>

      {{-- Modern Tab Navigation --}}
      <ul class="nav leave-tabs" id="leaveTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-menunggu-btn" data-bs-toggle="pill" data-bs-target="#tab-menunggu" type="button" role="tab" aria-controls="tab-menunggu" aria-selected="true">
            <i class="ri-time-line"></i>
            <span>Menunggu</span>
            <span class="badge-count bg-warning-100 text-warning-800">{{ $totalMenunggu }}</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-disetujui-btn" data-bs-toggle="pill" data-bs-target="#tab-disetujui" type="button" role="tab" aria-controls="tab-disetujui" aria-selected="false">
            <i class="ri-check-line"></i>
            <span>Disetujui</span>
            <span class="badge-count bg-success-100 text-success-800">{{ $totalDisetujui }}</span>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-ditolak-btn" data-bs-toggle="pill" data-bs-target="#tab-ditolak" type="button" role="tab" aria-controls="tab-ditolak" aria-selected="false">
            <i class="ri-close-line"></i>
            <span>Ditolak</span>
            <span class="badge-count bg-danger-100 text-danger-800">{{ $totalDitolak }}</span>
          </button>
        </li>
      </ul>
    </div>

    {{-- Card Body dengan Padding 20-24px agar Tabel Tidak Menempel ke Tepi Card --}}
    <div class="card-body p-20 p-md-24 p-lg-28">
      
      <div class="tab-content" id="leaveTabsContent">

        {{-- TAB 1: MENUNGGU --}}
        <div class="tab-pane fade show active" id="tab-menunggu" role="tabpanel" aria-labelledby="tab-menunggu-btn">
          @php
            $menungguRequests = $requests->where('status_pengajuan', 'Pending');
          @endphp

          @if($menungguRequests->count() > 0)
            <div class="table-inner-container table-responsive">
              <table class="table leave-table mb-0">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center">Cuti</th>
                    <th class="text-center">Tanggal Mulai</th>
                    <th class="text-center">Tanggal Selesai</th>
                    <th class="text-center">Durasi</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 140px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($menungguRequests as $index => $item)
                    <tr>
                      <td class="text-center text-neutral-500 fw-semibold">{{ $loop->iteration }}</td>
                      <td class="text-center" style="white-space: nowrap;">
                          <span class="text-xs text-neutral-400">
                              #FIC-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                          </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_awal ? $item->tanggal_awal->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_akhir ? $item->tanggal_akhir->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="duration-badge">
                            {{ $item->jumlah_hari }} Hari
                        </span>
                    </td>
                      <td>
                        <span class="text-neutral-700 text-truncate d-inline-block" style="max-width: 220px;" title="{{ $item->keterangan }}">
                          {{ $item->keterangan ?: '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        {{-- Modern Pill Badge --}}
                        <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-warning-100 text-warning-800 border border-warning-200 d-inline-flex align-items-center gap-1 shadow-2xs">
                          <i class="ri-time-line text-sm"></i> Menunggu
                        </span>
                      </td>
                      <td class="text-center">
    <div class="d-inline-flex align-items-center gap-1">

        {{-- Detail --}}
        <a href="{{ route('leave-requests.show', $item) }}"
           class="btn btn-sm btn-outline-primary-600 text-white radius-8 px-8 py-6 d-inline-flex align-items-center justify-content-center"
           title="Lihat Detail Pengajuan">
            <i class="ri-eye-line text-sm"></i>
        </a>

        {{-- Edit --}}
        <a href="{{ route('leave-requests.edit', $item) }}"
           class="btn btn-sm btn-outline-neutral-500 radius-8 px-8 py-6 d-inline-flex align-items-center justify-content-center text-neutral-600 hover-bg-neutral-100"
           title="Ubah Pengajuan">
            <i class="ri-edit-line"></i>
        </a>

        {{-- Hapus --}}
        <form class="d-inline"
              method="POST"
              action="{{ route('leave-requests.destroy', $item) }}"
              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus pengajuan cuti ini?');">
            @csrf
            @method('DELETE')

           {{-- Hapus --}}
            <button type="button" 
                    class="btn btn-sm btn-outline-danger-500 radius-8 px-8 py-6 d-inline-flex align-items-center justify-content-center text-danger-600 hover-bg-danger-100 btn-delete"
                    data-id="{{ $item->id }}"
                    data-name="#FIC-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}"
                    title="Hapus Pengajuan">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>

    </div>
</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="table-inner-container empty-state-box">
              <div class="empty-state-icon">
                <i class="ri-inbox-2-line"></i>
              </div>
              <h6 class="fw-bold text-neutral-800 mb-1">Belum ada pengajuan cuti pada status ini.</h6>
              <p class="text-neutral-500 text-sm mb-16">Tidak ada pengajuan cuti yang sedang menunggu persetujuan saat ini.</p>
              <a 
                href="{{ route('leave-requests.create') }}" 
                class="btn btn-sm btn-primary-600 radius-8 px-16 py-8 d-inline-flex align-items-center gap-2 fw-semibold"
              >
                <i class="ri-add-line"></i> Ajukan Cuti Sekarang
              </a>
            </div>
          @endif
        </div>

        {{-- TAB 2: DISETUJUI --}}
        <div class="tab-pane fade" id="tab-disetujui" role="tabpanel" aria-labelledby="tab-disetujui-btn">
          @php
            $disetujuiRequests = $requests->where('status_pengajuan', 'Approve');
          @endphp

          @if($disetujuiRequests->count() > 0)
            <div class="table-inner-container table-responsive">
              <table class="table leave-table mb-0">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center">Cuti</th>
                    <th class="text-center">Tanggal Mulai</th>
                    <th class="text-center">Tanggal Selesai</th>
                    <th class="text-center">Durasi</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 120px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($disetujuiRequests as $index => $item)
                    <tr>
                      <td class="text-center text-neutral-500 fw-semibold">{{ $loop->iteration }}</td>
                     <td class="text-center" style="white-space: nowrap;">
                          <span class="text-xs text-neutral-400">
                              #FIC-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                          </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_awal ? $item->tanggal_awal->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_akhir ? $item->tanggal_akhir->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="duration-badge">
                            {{ $item->jumlah_hari }} Hari
                        </span>
                    </td>
                      <td>
                        <span class="text-neutral-700 text-truncate d-inline-block" style="max-width: 250px;" title="{{ $item->keterangan }}">
                          {{ $item->keterangan ?: '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        {{-- Modern Pill Badge --}}
                        <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-success-100 text-success-800 border border-success-200 d-inline-flex align-items-center gap-1 shadow-2xs">
                          <i class="ri-check-line text-sm"></i> Disetujui
                        </span>
                      </td>
                      <td class="text-center">
                        <a 
                          href="{{ route('leave-requests.show', $item) }}" 
                          class="btn btn-sm btn-outline-primary-600 text-white radius-8 px-12 py-6 d-inline-flex align-items-center gap-1 fw-semibold shadow-xs"
                          title="Lihat Detail Pengajuan"
                        >
                          <i class="ri-eye-line text-sm"></i> 
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="table-inner-container empty-state-box">
              <div class="empty-state-icon text-success-600 bg-success-50">
                <i class="ri-checkbox-circle-line"></i>
              </div>
              <h6 class="fw-bold text-neutral-800 mb-1">Belum ada pengajuan cuti pada status ini.</h6>
              <p class="text-neutral-500 text-sm mb-0">Belum ada pengajuan cuti yang disetujui pada periode ini.</p>
            </div>
          @endif
        </div>

        {{-- TAB 3: DITOLAK --}}
        <div class="tab-pane fade" id="tab-ditolak" role="tabpanel" aria-labelledby="tab-ditolak-btn">
          @php
            $ditolakRequests = $requests->where('status_pengajuan', 'Reject');
          @endphp

          @if($ditolakRequests->count() > 0)
            <div class="table-inner-container table-responsive">
              <table class="table leave-table mb-0">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th class="text-center">Cuti</th>
                    <th class="text-center">Tanggal Mulai</th>
                    <th class="text-center">Tanggal Selesai</th>
                    <th class="text-center">Durasi</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 120px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($ditolakRequests as $index => $item)
                    <tr>
                      <td class="text-neutral-500 fw-semibold">{{ $loop->iteration }}</td>
                      <td class="text-center" style="white-space: nowrap;">
                          <span class="text-xs text-neutral-400">
                              #FIC-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                          </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_awal ? $item->tanggal_awal->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="fw-medium text-neutral-800">
                          {{ $item->tanggal_akhir ? $item->tanggal_akhir->translatedFormat('d M Y') : '-' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="duration-badge">
                            {{ $item->jumlah_hari }} Hari
                        </span>
                    </td>
                     <td>
                        <span class="text-neutral-700 text-truncate d-inline-block"
                              style="max-width: 250px;"
                              title="{{ $item->keterangan }}">
                            {{ $item->keterangan ?: '-' }}
                        </span>

                        @if($item->reject_statement)
                            <div class="text-xs text-danger-600"
                                style="max-width: 250px; margin-top: 2px; line-height: 1.3;">
                                Catatan: {{ $item->reject_statement }}
                            </div>
                        @endif
                    </td>
                      <td class="text-center">
                        {{-- Modern Pill Badge --}}
                        <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-danger-100 text-danger-800 border border-danger-200 d-inline-flex align-items-center gap-1 shadow-2xs">
                          <i class="ri-close-line text-sm"></i> Ditolak
                        </span>
                      </td>
                      <td class="text-center">
                        <a 
                          href="{{ route('leave-requests.show', $item) }}" 
                          class="btn btn-sm btn-outline-primary-600 text-white radius-8 px-12 py-6 d-inline-flex align-items-center gap-1 fw-semibold shadow-xs"
                          title="Lihat Detail Pengajuan"
                        >
                          <i class="ri-eye-line text-sm"></i> 
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="table-inner-container empty-state-box">
              <div class="empty-state-icon text-danger-600 bg-danger-50">
                <i class="ri-close-circle-line"></i>
              </div>
              <h6 class="fw-bold text-neutral-800 mb-1">Belum ada pengajuan cuti pada status ini.</h6>
              <p class="text-neutral-500 text-sm mb-0">Tidak ada permohonan cuti yang ditolak.</p>
            </div>
          @endif
        </div>

      </div>

    </div>
  </div>

</div>

{{-- Hidden Form for SweetAlert Delete Confirmation --}}
<form id="delete-form" method="POST" class="d-none">
  @csrf
  @method('DELETE')
</form>

@endsection
@push('scripts')
<script>
    $(document).on('click', '.btn-delete', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');

    Swal.fire({
        html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
            + '<div class="delete-title">Hapus Pengajuan Cuti</div>'
            + '<div class="delete-text">Anda yakin ingin menghapus data pengajuan <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
            + '<div class="delete-actions">'
            + '  <button type="button" class="btn-delete-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
            + '  <button type="button" class="btn-delete-confirm" id="swal-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>'
            + '</div>',
        showConfirmButton: false,
        showCancelButton: false,
        showCloseButton: false,
        customClass: { popup: 'delete-popup' },
        didOpen: function(popup) {
            popup.querySelector('#swal-cancel').addEventListener('click', function() { 
                Swal.close(); 
            });
            
            popup.querySelector('#swal-confirm').addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...';
                
                var form = $('#delete-form');
                form.attr('action', '{{ url("leave-requests") }}/' + id);
                form.submit();
            });
        }
    });
});
</script>
@endpush
