@extends('layouts.main')

@section('title', 'Detail Pengajuan Cuti')

@push('styles')
<style>
  .detail-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  }
  .info-box {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 16px 18px;
    transition: all 0.2s ease;
  }
  .info-box:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
  }
  
  /* Timeline History */
  .timeline-approval {
    position: relative;
    padding-left: 36px;
  }
  .timeline-approval::before {
    content: '';
    position: absolute;
    top: 14px;
    bottom: 14px;
    left: 15px;
    width: 2px;
    background: #e2e8f0;
  }
  .timeline-step {
    position: relative;
    margin-bottom: 28px;
  }
  .timeline-step:last-child {
    margin-bottom: 0;
  }
  .timeline-dot {
    position: absolute;
    left: -36px;
    top: 2px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    border: 2px solid #cbd5e1;
    z-index: 1;
    font-size: 15px;
  }
  .timeline-dot.done {
    border-color: #10b981;
    background: #ecfdf5;
    color: #10b981;
  }
  .timeline-dot.waiting {
    border-color: #f59e0b;
    background: #fffbeb;
    color: #f59e0b;
  }
  .timeline-dot.rejected {
    border-color: #ef4444;
    background: #fef2f2;
    color: #ef4444;
  }
  .timeline-dot.pending {
    border-color: #94a3b8;
    background: #f8fafc;
    color: #94a3b8;
  }
</style>
@endpush

@section('content')
<div class="container-fluid p-0">

  {{-- Navigasi Atas --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <div>
      <div class="mb-2">
        <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-outline-neutral-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 fw-semibold">
          <i class="ri-arrow-left-line"></i> Kembali ke Riwayat Cuti
        </a>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-3">
        <h5 class="fw-bold text-neutral-900 mt-2">Detail Pengajuan Cuti</h5>
        
        {{-- Status Badge Header --}}
        @if($leaveRequest->status_pengajuan === 'Approve')
          <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-success-100 text-success-800 border border-success-200 d-inline-flex align-items-center gap-1 shadow-2xs">
            <i class="ri-check-line text-sm"></i> Disetujui
          </span>
        @elseif($leaveRequest->status_pengajuan === 'Reject')
          <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-danger-100 text-danger-800 border border-danger-200 d-inline-flex align-items-center gap-1 shadow-2xs">
            <i class="ri-close-line text-sm"></i> Ditolak
          </span>
        @else
          <span class="badge rounded-pill px-14 py-6 fw-semibold text-xs bg-warning-100 text-warning-800 border border-warning-200 d-inline-flex align-items-center gap-1 shadow-2xs">
            <i class="ri-time-line text-sm"></i> Menunggu
          </span>
        @endif

        <span class="text-xs text-neutral-500 fw-medium">#PC-{{ str_pad($leaveRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
      </div>
    </div>

    {{-- Aksi Tambahan Jika Masih Pending --}}
    @if($leaveRequest->status_pengajuan === 'Pending')
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-sm btn-primary-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-1 fw-semibold">
          <i class="ri-edit-line"></i> Ubah Pengajuan
        </a>
        <form method="POST" action="{{ route('leave-requests.destroy', $leaveRequest) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus pengajuan ini?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger radius-8 px-14 py-8 d-inline-flex align-items-center gap-1 fw-semibold">
            <i class="ri-delete-bin-line"></i> Batalkan Pengajuan
          </button>
        </form>
      </div>
    @endif
  </div>

  {{-- Alert Penolakan Jika Status Ditolak --}}
  @if($leaveRequest->status_pengajuan === 'Reject')
    <div class="alert alert-danger radius-14 p-20 mb-24 border-0 shadow-sm">
      <div class="d-flex align-items-start gap-3">
        <div class="w-40-px h-40-px rounded-circle bg-danger-100 text-danger-600 d-flex align-items-center justify-content-center flex-shrink-0 mt-1">
          <i class="ri-error-warning-fill text-xl"></i>
        </div>
        <div>
          <h6 class="fw-bold text-danger-800 mb-1">Pengajuan Cuti Ditolak</h6>
          <p class="text-sm text-danger-700 mb-0">
            <strong>Alasan Penolakan:</strong> {{ $leaveRequest->reject_statement ?: 'Tidak ada catatan penolakan spesifik.' }}
          </p>
          @if($leaveRequest->updated_by)
            <span class="text-xs text-danger-600 d-block mt-2">Ditinjau oleh: <strong>{{ $leaveRequest->updated_by }}</strong></span>
          @endif
        </div>
      </div>
    </div>
  @endif

  <div class="row g-4">
    
    {{-- Kolom Kiri: Informasi Pengajuan Cuti --}}
    <div class="col-lg-7 col-12">
      <div class="card detail-card border-0 mb-24 overflow-hidden">
        <div class="card-header bg-white border-bottom border-neutral-100 py-16 px-24 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <div class="w-36-px h-36-px rounded-8 bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center">
              <i class="ri-file-text-line text-lg"></i>
            </div>
            <h6 class="fw-bold text-neutral-900 mb-0">Informasi Pengajuan</h6>
          </div>
          <span class="badge bg-primary-50 text-primary-700 border border-primary-200 px-10 py-4 radius-6 fw-semibold text-xs">
            {{ $leaveRequest->jumlah_hari }} Hari Cuti
          </span>
        </div>

        <div class="card-body p-24">
          <div class="row g-3">

            {{-- Nomor Pengajuan --}}
            <div class="col-sm-6 col-12">
              <div class="info-box h-100">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-1">Nomor Pengajuan</span>
                <span class="fw-bold text-neutral-900 text-sm">#PC-{{ str_pad($leaveRequest->id, 5, '0', STR_PAD_LEFT) }}</span>
              </div>
            </div>

            {{-- Jenis Cuti --}}
            <div class="col-sm-6 col-12">
              <div class="info-box h-100">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-1">Jenis Cuti</span>
                <span class="fw-bold text-primary-700 text-sm">{{ $leaveRequest->kategoriCuti->nama_kategori ?? 'Cuti' }}</span>
              </div>
            </div>

            {{-- Tanggal Pengajuan Dibuat --}}
            <div class="col-sm-6 col-12">
              <div class="info-box h-100">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-1">Tanggal Pengajuan</span>
                <span class="fw-bold text-neutral-900 text-sm">
                  {{ $leaveRequest->created_at ? $leaveRequest->created_at->translatedFormat('d F Y, H:i') : ($leaveRequest->created_date ? \Carbon\Carbon::parse($leaveRequest->created_date)->translatedFormat('d F Y') : '-') }}
                </span>
              </div>
            </div>

            {{-- Durasi --}}
            <div class="col-sm-6 col-12">
              <div class="info-box h-100">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-1">Durasi Cuti</span>
                <span class="fw-bold text-neutral-900 text-sm">{{ $leaveRequest->jumlah_hari }} Hari Kerja</span>
              </div>
            </div>

            {{-- Periode Tanggal Mulai & Tanggal Selesai --}}
            <div class="col-12">
              <div class="info-box">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-2">Periode Pelaksanaan Cuti</span>
                <div class="d-flex flex-wrap align-items-center gap-3">
                  <div class="d-flex align-items-center gap-2">
                    <div class="w-32-px h-32-px rounded-6 bg-white text-primary-600 border border-neutral-200 d-flex align-items-center justify-content-center">
                      <i class="ri-calendar-event-line"></i>
                    </div>
                    <div>
                      <span class="text-2xs text-neutral-500 d-block">Tanggal Mulai</span>
                      <strong class="text-sm text-neutral-900">{{ $leaveRequest->tanggal_awal ? $leaveRequest->tanggal_awal->translatedFormat('d F Y') : '-' }}</strong>
                    </div>
                  </div>

                  <i class="ri-arrow-right-line text-neutral-400 d-none d-sm-inline-block"></i>

                  <div class="d-flex align-items-center gap-2">
                    <div class="w-32-px h-32-px rounded-6 bg-white text-primary-600 border border-neutral-200 d-flex align-items-center justify-content-center">
                      <i class="ri-calendar-check-line"></i>
                    </div>
                    <div>
                      <span class="text-2xs text-neutral-500 d-block">Tanggal Selesai</span>
                      <strong class="text-sm text-neutral-900">{{ $leaveRequest->tanggal_akhir ? $leaveRequest->tanggal_akhir->translatedFormat('d F Y') : '-' }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Alasan Cuti --}}
            <div class="col-12">
              <div class="info-box">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-1">Alasan Cuti</span>
                <p class="text-neutral-800 text-sm mb-0 lh-base">
                  {{ $leaveRequest->keterangan ?: 'Tidak ada keterangan tambahan.' }}
                </p>
              </div>
            </div>

            {{-- Dokumen Lampiran --}}
            <div class="col-12">
              <div class="info-box">
                <span class="text-xs text-neutral-500 fw-semibold text-uppercase tracking-wider d-block mb-2">Dokumen Lampiran</span>
                @if($leaveRequest->lampiran)
                  <div class="p-12 radius-8 bg-white border border-neutral-200 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                      <i class="ri-file-text-line text-primary-600 text-xl"></i>
                      <span class="text-sm fw-medium text-neutral-800">Dokumen Pendukung Cuti</span>
                    </div>
                    <a href="{{ asset($leaveRequest->lampiran) }}" target="_blank" class="btn btn-sm btn-primary-600 radius-8 px-14 py-6 d-inline-flex align-items-center gap-1 text-xs fw-semibold">
                      <i class="ri-download-2-line"></i> Buka / Unduh File
                    </a>
                  </div>
                @else
                  <span class="text-neutral-400 text-xs italic">Tidak ada dokumen pendukung dilampirkan.</span>
                @endif
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    {{-- Kolom Kanan: 10. Status Pengajuan & History Persetujuan (Timeline) --}}
    <div class="col-lg-5 col-12">
      
      {{-- Card Status Pengajuan --}}
      <div class="card detail-card border-0 mb-24 overflow-hidden">
        <div class="card-header bg-white border-bottom border-neutral-100 py-16 px-24">
          <div class="d-flex align-items-center gap-2">
            <div class="w-36-px h-36-px rounded-8 bg-primary-50 text-primary-600 d-flex align-items-center justify-content-center">
              <i class="ri-git-commit-line text-lg"></i>
            </div>
            <h6 class="fw-bold text-neutral-900 mb-0">Status & Alur Persetujuan</h6>
          </div>
        </div>

        <div class="card-body p-24">
          
          {{-- Modern Timeline --}}
          <div class="timeline-approval">
            
            {{-- Step 1: Pengajuan Dibuat --}}
            <div class="timeline-step">
              <div class="timeline-dot done">
                <i class="ri-check-line"></i>
              </div>
              <div class="ps-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                  <span class="fw-bold text-neutral-900 text-sm">Pengajuan Dibuat</span>
                  <span class="text-xs text-neutral-400">
                    {{ $leaveRequest->created_at ? $leaveRequest->created_at->translatedFormat('d M Y, H:i') : ($leaveRequest->created_date ? \Carbon\Carbon::parse($leaveRequest->created_date)->translatedFormat('d M Y') : '-') }}
                  </span>
                </div>
                <p class="text-xs text-neutral-600 mb-1">
                  Pengajuan cuti dibuat oleh <strong>{{ $leaveRequest->created_by ?: ($karyawan->nama_karyawan ?? 'Karyawan') }}</strong>
                </p>
                <span class="badge bg-success-50 text-success-700 border border-success-200 px-8 py-2 radius-6 text-2xs">
                  Berhasil dikirim ke sistem
                </span>
              </div>
            </div>

            {{-- Step 2: Persetujuan Atasan --}}
            <div class="timeline-step">
              @if($leaveRequest->status_pengajuan === 'Approve')
                <div class="timeline-dot done">
                  <i class="ri-check-line"></i>
                </div>
                <div class="ps-2">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                    <span class="fw-bold text-neutral-900 text-sm">Disetujui Atasan</span>
                    <span class="text-xs text-neutral-400">
                      {{ $leaveRequest->updated_at ? $leaveRequest->updated_at->translatedFormat('d M Y, H:i') : '-' }}
                    </span>
                  </div>
                  <p class="text-xs text-neutral-600 mb-0">
                    Oleh: <strong>{{ $leaveRequest->updated_by ?: 'Kepala Bagian / Manager' }}</strong>
                  </p>
                  <span class="badge bg-success-50 text-success-700 border border-success-200 px-8 py-2 radius-6 text-2xs mt-1">
                    Pengajuan disetujui
                  </span>
                </div>
              @elseif($leaveRequest->status_pengajuan === 'Reject')
                <div class="timeline-dot rejected">
                  <i class="ri-close-line"></i>
                </div>
                <div class="ps-2">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                    <span class="fw-bold text-danger-700 text-sm">Ditolak</span>
                    <span class="text-xs text-neutral-400">
                      {{ $leaveRequest->updated_at ? $leaveRequest->updated_at->translatedFormat('d M Y, H:i') : '-' }}
                    </span>
                  </div>
                  <p class="text-xs text-neutral-600 mb-1">
                    Oleh: <strong>{{ $leaveRequest->updated_by ?: 'Atasan / Manajemen' }}</strong>
                  </p>
                  @if($leaveRequest->reject_statement)
                    <span class="badge bg-danger-50 text-danger-700 border border-danger-200 px-8 py-3 radius-6 text-2xs text-wrap d-inline-block text-start">
                      Alasan: {{ $leaveRequest->reject_statement }}
                    </span>
                  @endif
                </div>
              @else
                <div class="timeline-dot waiting">
                  <i class="ri-time-line"></i>
                </div>
                <div class="ps-2">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                    <span class="fw-bold text-neutral-900 text-sm">Menunggu Persetujuan</span>
                    <span class="text-xs text-warning-600 fw-semibold">Dalam Proses</span>
                  </div>
                  <p class="text-xs text-neutral-600 mb-0">
                    Menunggu verifikasi dan persetujuan dari Kepala Bagian / Atasan langsung.
                  </p>
                </div>
              @endif
            </div>

            {{-- Step 3: Selesai / Verifikasi HRD --}}
            <div class="timeline-step">
              @if($leaveRequest->status_pengajuan === 'Approve')
                <div class="timeline-dot done">
                  <i class="ri-check-double-line"></i>
                </div>
                <div class="ps-2">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                    <span class="fw-bold text-neutral-900 text-sm">Selesai & Dicatat HRD</span>
                    <span class="text-xs text-neutral-400">
                      {{ $leaveRequest->updated_at ? $leaveRequest->updated_at->translatedFormat('d M Y, H:i') : '-' }}
                    </span>
                  </div>
                  <p class="text-xs text-neutral-600 mb-0">
                    Pencatatan saldo cuti resmi telah dikonfirmasi oleh sistem dan HRD.
                  </p>
                </div>
              @elseif($leaveRequest->status_pengajuan === 'Reject')
                <div class="timeline-dot pending">
                  <i class="ri-indeterminate-circle-line"></i>
                </div>
                <div class="ps-2">
                  <span class="fw-bold text-neutral-500 text-sm">Selesai (Dihentikan)</span>
                  <p class="text-xs text-neutral-400 mb-0">
                    Proses pengajuan dihentikan karena penolakan.
                  </p>
                </div>
              @else
                <div class="timeline-dot pending">
                  <i class="ri-user-star-line"></i>
                </div>
                <div class="ps-2">
                  <span class="fw-bold text-neutral-500 text-sm">Pencatatan HRD</span>
                  <p class="text-xs text-neutral-400 mb-0">
                    Akan otomatis diproses setelah atasan memberikan persetujuan.
                  </p>
                </div>
              @endif
            </div>

          </div>

        </div>
      </div>

    </div>

  </div>

</div>
@endsection
