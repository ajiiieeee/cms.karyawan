@extends('layouts.main')

@section('title', 'Detail Pengajuan Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Detail Pengajuan Cuti</h6>
        <p class="text-neutral-600 mt-4 mb-0">Pengajuan Cuti &raquo; Detail</p>
      </div>
      <a href="{{ route('pengajuan-cuti.index') }}" class="btn btn-outline-neutral-600 d-inline-flex align-items-center gap-2">
        <i class="ri-arrow-left-line"></i> Kembali
      </a>
    </div>

    @php
      $isRejected = $pengajuanCuti->status_pengajuan === 'Reject';
      $isApproved = $pengajuanCuti->status_pengajuan === 'Approve';
      $steps = [
        ['level' => 1, 'label' => 'HRD'],
        ['level' => 2, 'label' => 'Manager'],
        ['level' => 3, 'label' => 'Direktur'],
      ];
    @endphp

    <div class="row">
      <!-- Left: Informasi Form Pengajuan Cuti -->
      <div class="col-lg-8 mb-lg-0">
        <div class="card shadow-1 radius-8 h-100">
          <div class="card-body p-32">
            {{-- Header: title + status badge --}}
            <div class="d-flex align-items-center justify-content-between mb-28">
              <h6 class="fw-bold text-lg mb-0">Informasi Form Pengajuan Cuti</h6>
              @if($isApproved)
                <span class="detail-status-badge detail-status-approved">Disetujui</span>
              @elseif($isRejected)
                <span class="detail-status-badge detail-status-rejected">Ditolak</span>
              @else
                <span class="detail-status-badge detail-status-pending">Pending</span>
              @endif
            </div>

            {{-- Row 1: Tanggal Cuti + Tanggal Dibuat --}}
            <div class="row mb-24">
              <div class="col-md-6 mb-16 mb-md-0">
                <span class="detail-label">Tanggal Cuti :</span>
                <p class="detail-value">{{ $pengajuanCuti->tanggal_awal->translatedFormat('d F Y') }} - {{ $pengajuanCuti->tanggal_akhir->translatedFormat('d F Y') }}</p>
              </div>
              <div class="col-md-6">
                <span class="detail-label">Tanggal Dibuat :</span>
                <p class="detail-value">{{ $pengajuanCuti->created_date ? \Carbon\Carbon::parse($pengajuanCuti->created_date)->format('d/m/Y') : '-' }}</p>
              </div>
            </div>

            {{-- Row 2: Total Hari + Terakhir Diperbarui --}}
            <div class="row mb-24">
              <div class="col-md-6 mb-16 mb-md-0">
                <span class="detail-label">Total Hari :</span>
                <p class="detail-value">{{ $pengajuanCuti->jumlah_hari }} Hari</p>
              </div>
              <div class="col-md-6">
                <span class="detail-label">Terakhir Diperbarui :</span>
                <p class="detail-value">{{ $pengajuanCuti->updated_date ? \Carbon\Carbon::parse($pengajuanCuti->updated_date)->format('d/m/Y') : '-' }}</p>
              </div>
            </div>

            {{-- Row 3: Jenis Cuti --}}
            <div class="mb-24">
              <span class="detail-label">Jenis Cuti :</span>
              <div class="mt-8">
                <span class="detail-cuti-badge">{{ $pengajuanCuti->kategoriCuti->nama_kategori ?? '-' }}</span>
              </div>
            </div>

            {{-- Row 4: Keterangan --}}
            <div class="mb-24">
              <span class="detail-label">Keterangan :</span>
              <p class="detail-value">{{ $pengajuanCuti->keterangan ?: '-' }}</p>
            </div>

            {{-- Row 5: Karyawan --}}
            <div>
              <span class="detail-label">Karyawan :</span>
              <p class="detail-value">{{ $pengajuanCuti->karyawan->nama_karyawan ?? '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Status Persetujuan -->
      <div class="col-lg-4">
        <div class="card shadow-1 radius-8 h-100">
          <div class="card-body p-32">
            <h6 class="fw-bold text-lg mb-0" style="font-size: .9375rem;">Status Persetujuan</h6>
            <hr class="my-16" style="border-color: #e2e8f0;">

            <div class="approval-timeline">
              @foreach($steps as $index => $step)
                @php
                  $isCompleted = $pengajuanCuti->status_approval >= $step['level'] && !$isRejected;
                  $isCompletedBeforeReject = $isRejected && $pengajuanCuti->status_approval >= $step['level'];
                  $isRejectLevel = $isRejected && $pengajuanCuti->status_approval === ($step['level'] - 1);
                  $isWaiting = (!$isRejected && !$isApproved && $pengajuanCuti->status_approval < $step['level'])
                               || ($isRejected && $pengajuanCuti->status_approval < ($step['level'] - 1));
                  $isLast = $index === count($steps) - 1;

                  // Line color
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
                    {{-- Dot + line --}}
                    <div class="tl-indicator">
                      @if($isCompleted || $isCompletedBeforeReject)
                        <div class="tl-dot tl-dot--success">
                          <i class="ri-check-line"></i>
                        </div>
                      @elseif($isRejectLevel)
                        <div class="tl-dot tl-dot--danger">
                          <i class="ri-close-line"></i>
                        </div>
                      @else
                        <div class="tl-dot tl-dot--warning">
                          <i class="ri-time-line"></i>
                        </div>
                      @endif
                      @if(!$isLast)
                        <div class="tl-line" style="background: {{ $lineColor }};"></div>
                      @endif
                    </div>
                    {{-- Content --}}
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

                  {{-- Rejection reason box (full width) --}}
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
    </div>
@endsection

@push('styles')
<style>
/* ===== Detail Info Styles ===== */
.detail-label {
    display: block;
    font-size: .8125rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 4px;
}
.detail-value {
    font-size: .9375rem;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 0;
    margin-top: 4px;
}
.detail-status-badge {
    display: inline-block;
    padding: 6px 20px;
    border-radius: 50px;
    font-size: .8125rem;
    font-weight: 600;
    color: #fff;
}
.detail-status-approved { background: #22c55e; }
.detail-status-rejected { background: #ef4444; }
.detail-status-pending  { background: #f59e0b; }

.detail-cuti-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 50px;
    font-size: .8125rem;
    font-weight: 500;
    color: #6366f1;
    border: 1px solid #c7d2fe;
    background: #fff;
}

/* ===== Approval Timeline ===== */
.approval-timeline .tl-item {
    position: relative;
}
.tl-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    margin-right: 14px;
}
.tl-dot {
    width: 30px;
    height: 30px;
    min-width: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 13px;
    position: relative;
    z-index: 1;
}
.tl-dot--success { background: #22c55e; }
.tl-dot--danger  { background: #ef4444; }
.tl-dot--warning { background: #f59e0b; }

.tl-line {
    width: 2px;
    height: 100%;
    min-height: 36px;
    flex-grow: 1;
}
.tl-content {
    padding-top: 4px;
    padding-bottom: 20px;
}
.tl-title {
    display: block;
    font-size: .8125rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 3px;
    line-height: 1.3;
}
.tl-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 50px;
    font-size: .6875rem;
    font-weight: 600;
}
.tl-badge--success {
    color: #16a34a;
    background: #dcfce7;
    border: 1px solid #bbf7d0;
}
.tl-badge--danger {
    color: #dc2626;
    background: #fee2e2;
    border: 1px solid #fecaca;
}
.tl-badge--warning {
    color: #d97706;
    background: #fef3c7;
    border: 1px solid #fde68a;
}
.tl-date {
    font-size: .6875rem;
    color: #94a3b8;
    font-weight: 400;
}
.tl-reject-box {
    margin-top: 4px;
    margin-bottom: 4px;
    padding: 12px 16px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    font-size: .8125rem;
    color: #991b1b;
    line-height: 1.5;
    width: 100%;
}
.tl-reject-box strong {
    font-size: .8125rem;
    color: #7f1d1d;
}
.tl-reject-box p {
    /* font-size: .8125rem; */
    color: #b91c1c;
    line-height: 1.6;
}
.tl-reject-box i {
    color: #dc2626;
}

/* Dark mode */
[data-theme="dark"] .detail-label { color: #94a3b8; }
[data-theme="dark"] .detail-value { color: #e2e8f0; }
[data-theme="dark"] .detail-cuti-badge {
    color: #a5b4fc;
    border-color: #4338ca;
    background: #1e1b4b;
}
[data-theme="dark"] .tl-title { color: #e2e8f0; }
[data-theme="dark"] .tl-date { color: #64748b; }
[data-theme="dark"] .tl-reject-box {
    background: rgba(239,68,68,.15);
    color: #fca5a5;
}
[data-theme="dark"] .tl-reject-box strong { color: #fecaca; }
</style>
@endpush
