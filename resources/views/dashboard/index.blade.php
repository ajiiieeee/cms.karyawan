@extends('layouts.main')

@section('title', 'Dashboard Karyawan')

@push('styles')
<style>
  /* Card Utama Hover Effect */
  .karyawan-metric-card {
    transition: transform 0.22s ease, box-shadow 0.22s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
  }
  .karyawan-metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px -6px rgba(15, 23, 42, 0.08) !important;
  }

  /* Performance Card Styling */
  .perf-score-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }
  .perf-score-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 18px -4px rgba(15, 23, 42, 0.05);
  }

  /* Compact Table Badges & Action Buttons */
  .hover-bg-primary-600:hover {
    background-color: var(--primary-600, #487fff) !important;
    color: #ffffff !important;
  }
</style>
@endpush

@section('content')
@php
    // Safe Fallbacks & Variables
    $karyawan = $karyawan ?? null;
    $totalSaldoCuti = $totalSaldoCuti ?? ($karyawan?->saldoCuti?->total_cuti ?? null);
    $sisaCutiTahunan = $sisaCutiTahunan ?? ($karyawan?->saldoCuti?->saldo_sisa ?? null);
    $terpakaiCutiTahunan = $terpakaiCutiTahunan ?? ($karyawan?->saldoCuti?->saldo_terpakai ?? 0);
    $periodeCutiLabel = $periodeCutiLabel ?? (
        ($karyawan && $karyawan->saldoCuti && $karyawan->saldoCuti->periode_mulai && $karyawan->saldoCuti->periode_selesai)
        ? $karyawan->saldoCuti->periode_mulai->format('d/m/Y') . ' - ' . $karyawan->saldoCuti->periode_selesai->format('d/m/Y')
        : 'Periode ' . date('Y')
    );

    $totalFimp = $totalFimp ?? ($karyawan ? \App\Models\Fimp::where('karyawan_id', $karyawan->id)->count() : 0);
    $fimpApproved = $fimpApproved ?? ($karyawan ? \App\Models\Fimp::where('karyawan_id', $karyawan->id)->where('status_approval', 'Approve')->count() : 0);
    $fimpPending = $fimpPending ?? ($karyawan ? \App\Models\Fimp::where('karyawan_id', $karyawan->id)->where('status_approval', 'Pending')->count() : 0);

    $totalOvertime = $totalOvertime ?? null;

    $latestPerformance = $latestPerformance ?? null;
    $averageScore = $averageScore ?? null;
    $bestScore = $bestScore ?? null;
    $lowestScore = $lowestScore ?? null;

    $performanceMonths = $performanceMonths ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $performanceSeries = $performanceSeries ?? [];

    $myLeaveRequests = $myLeaveRequests ?? (
        $karyawan
        ? \App\Models\PengajuanCuti::with('kategoriCuti')->where('karyawan_id', $karyawan->id)->latest('id')->take(10)->get()
        : collect()
    );

    $userDisplayName = $karyawan->nama_karyawan ?? (Auth::user()->nama ?? 'Karyawan');
@endphp

    {{-- Breadcrumb & Header Karyawan --}}
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Dashboard Karyawan</h6>
        <p class="text-neutral-600 mt-4 mb-0">
          Selamat datang kembali, <strong class="text-neutral-900">{{ $userDisplayName }}</strong>! Berikut ringkasan status kerja dan cuti Anda.
        </p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- Jika user adalah super admin/manajemen yang melihat preview karyawan, sediakan tombol kembali --}}
        @if(Auth::user()->grup && in_array(strtolower(Auth::user()->grup->nama_grup), ['super admin', 'manager', 'direktur', 'hrd']))
          <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-neutral-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-1">
            <i class="ri-dashboard-line"></i> Dashboard Utama
          </a>
        @endif

        <a href="{{ route('pengajuan-cuti.create') }}" class="btn btn-sm btn-primary-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-2">
          <i class="ri-add-line"></i> Ajukan Cuti
        </a>
        <a href="{{ route('pengajuan-fimp.create') }}" class="btn btn-sm btn-outline-primary radius-8 px-14 py-8 d-inline-flex align-items-center gap-2">
          <i class="ri-file-text-line"></i> Form Izin (FIMP)
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-24 radius-8" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="ri-checkbox-circle-fill text-lg"></i>
          <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- ==================================================
         1. CARD UTAMA DASHBOARD KARYAWAN (4 Card 1 Row Desktop)
         ================================================== --}}
    <div class="row gy-4 mb-24">
      
      {{-- Card 1: Total Saldo Cuti --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card karyawan-metric-card radius-12 bg-primary-50 border border-primary-100 shadow-none h-100">
          <div class="card-body p-20 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
              <span class="fw-semibold text-neutral-700 text-sm">Total Saldo Cuti</span>
              <div class="w-44-px h-44-px bg-primary-600 text-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div>
              <h3 class="mb-0 fw-bold text-neutral-900">
                @if($totalSaldoCuti !== null)
                  {{ $totalSaldoCuti }} <span class="text-lg fw-semibold text-neutral-600">Hari</span>
                @else
                  -
                @endif
              </h3>
              <p class="text-xs text-neutral-500 mt-8 mb-0 d-flex align-items-center gap-1 text-truncate">
                <iconify-icon icon="solar:clock-circle-bold" class="text-primary-600 text-sm flex-shrink-0"></iconify-icon>
                <span>{{ $periodeCutiLabel }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      {{-- Card 2: Sisa Cuti Tahunan --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card karyawan-metric-card radius-12 bg-success-50 border border-success-100 shadow-none h-100">
          <div class="card-body p-20 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
              <span class="fw-semibold text-neutral-700 text-sm">Sisa Cuti Tahunan</span>
              <div class="w-44-px h-44-px bg-success-600 text-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                <iconify-icon icon="solar:sun-2-bold-duotone" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div>
              <h3 class="mb-0 fw-bold text-neutral-900">
                @if($sisaCutiTahunan !== null)
                  {{ $sisaCutiTahunan }} <span class="text-lg fw-semibold text-neutral-600">Hari</span>
                @else
                  -
                @endif
              </h3>
              <p class="text-xs text-neutral-500 mt-8 mb-0 d-flex align-items-center gap-1">
                <iconify-icon icon="solar:check-circle-bold" class="text-success-600 text-sm flex-shrink-0"></iconify-icon>
                <span>Terpakai: <strong class="text-neutral-800">{{ $terpakaiCutiTahunan }} Hari</strong></span>
              </p>
            </div>
          </div>
        </div>
      </div>

      {{-- Card 3: Izin Meninggalkan Pekerjaan --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card karyawan-metric-card radius-12 bg-warning-50 border border-warning-100 shadow-none h-100">
          <div class="card-body p-20 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
              <span class="fw-semibold text-neutral-700 text-sm">Izin Meninggalkan Pekerjaan</span>
              <div class="w-44-px h-44-px bg-warning-600 text-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                <iconify-icon icon="solar:exit-bold-duotone" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div>
              <h3 class="mb-0 fw-bold text-neutral-900">
                {{ $totalFimp }} <span class="text-lg fw-semibold text-neutral-600">Pengajuan</span>
              </h3>
              <p class="text-xs text-neutral-500 mt-8 mb-0 d-flex align-items-center gap-1 text-truncate">
                <iconify-icon icon="solar:document-text-bold" class="text-warning-600 text-sm flex-shrink-0"></iconify-icon>
                <span>{{ $fimpApproved }} Disetujui • {{ $fimpPending }} Menunggu</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      {{-- Card 4: Overtime Request --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="card karyawan-metric-card radius-12 bg-purple-50 border border-purple-100 shadow-none h-100">
          <div class="card-body p-20 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-16">
              <span class="fw-semibold text-neutral-700 text-sm">Overtime Request</span>
              <div class="w-44-px h-44-px bg-purple-600 text-white rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-2xl"></iconify-icon>
              </div>
            </div>
            <div>
              <h3 class="mb-0 fw-bold text-neutral-900">
                @if($totalOvertime !== null)
                  {{ $totalOvertime }} <span class="text-lg fw-semibold text-neutral-600">Pengajuan</span>
                @else
                  0 <span class="text-lg fw-semibold text-neutral-600">Pengajuan</span>
                @endif
              </h3>
              <p class="text-xs text-neutral-500 mt-8 mb-0 d-flex align-items-center gap-1">
                <iconify-icon icon="solar:history-bold" class="text-purple-600 text-sm flex-shrink-0"></iconify-icon>
                <span>Permohonan lembur kerja</span>
              </p>
            </div>
          </div>
        </div>
      </div>

    </div>

    {{-- ==================================================
         2. CARD PERFORMANCE (Section Kinerja Karyawan)
         ================================================== --}}
    <div class="mb-24">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-16">
        <div>
          <h6 class="fw-semibold text-md mb-0 text-neutral-900">Informasi Performance Karyawan</h6>
          <p class="text-xs text-secondary-light mb-0">Ringkasan evaluasi dan capaian metrik kinerja berkala</p>
        </div>
      </div>

      <div class="row gy-4">

        {{-- Performance 1: Latest Performance --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="perf-score-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-12">
              <span class="text-xs fw-semibold text-neutral-600 text-uppercase tracking-wider">Latest Performance</span>
              <span class="badge bg-info-50 text-info-600 border border-info-200 radius-6 px-8 py-3 text-xs fw-semibold">
                Terbaru
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2 mb-4">
                <h3 class="mb-0 fw-bold text-neutral-900">
                  {{ $latestPerformance !== null ? number_format($latestPerformance, 1) : '-' }}
                </h3>
                @if($latestPerformance !== null)
                  <span class="text-xs text-neutral-500 fw-medium">/ 100</span>
                @endif
              </div>
              <p class="text-xs text-neutral-400 mb-0">
                {{ $latestPerformance !== null ? 'Skor evaluasi periode berjalan' : 'Belum ada penilaian' }}
              </p>
            </div>
          </div>
        </div>

        {{-- Performance 2: Average Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="perf-score-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-12">
              <span class="text-xs fw-semibold text-neutral-600 text-uppercase tracking-wider">Average Score</span>
              <span class="badge bg-primary-50 text-primary-600 border border-primary-200 radius-6 px-8 py-3 text-xs fw-semibold">
                Rata-Rata
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2 mb-4">
                <h3 class="mb-0 fw-bold text-neutral-900">
                  {{ $averageScore !== null ? number_format($averageScore, 1) : '-' }}
                </h3>
                @if($averageScore !== null)
                  <span class="text-xs text-neutral-500 fw-medium">/ 100</span>
                @endif
              </div>
              <p class="text-xs text-neutral-400 mb-0">
                {{ $averageScore !== null ? 'Rata-rata akumulasi tahun ini' : 'Belum ada data' }}
              </p>
            </div>
          </div>
        </div>

        {{-- Performance 3: Best Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="perf-score-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-12">
              <span class="text-xs fw-semibold text-neutral-600 text-uppercase tracking-wider">Best Score</span>
              <span class="badge bg-success-50 text-success-600 border border-success-200 radius-6 px-8 py-3 text-xs fw-semibold">
                Tertinggi
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2 mb-4">
                <h3 class="mb-0 fw-bold text-neutral-900">
                  {{ $bestScore !== null ? number_format($bestScore, 1) : '-' }}
                </h3>
                @if($bestScore !== null)
                  <span class="text-xs text-neutral-500 fw-medium">/ 100</span>
                @endif
              </div>
              <p class="text-xs text-neutral-400 mb-0">
                {{ $bestScore !== null ? 'Pencapaian skor tertinggi' : 'Belum ada data' }}
              </p>
            </div>
          </div>
        </div>

        {{-- Performance 4: Lowest Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="perf-score-card h-100 d-flex flex-column justify-content-between">
            <div class="d-flex align-items-center justify-content-between mb-12">
              <span class="text-xs fw-semibold text-neutral-600 text-uppercase tracking-wider">Lowest Score</span>
              <span class="badge bg-neutral-100 text-neutral-600 border border-neutral-200 radius-6 px-8 py-3 text-xs fw-semibold">
                Terendah
              </span>
            </div>
            <div>
              <div class="d-flex align-items-baseline gap-2 mb-4">
                <h3 class="mb-0 fw-bold text-neutral-900">
                  {{ $lowestScore !== null ? number_format($lowestScore, 1) : '-' }}
                </h3>
                @if($lowestScore !== null)
                  <span class="text-xs text-neutral-500 fw-medium">/ 100</span>
                @endif
              </div>
              <p class="text-xs text-neutral-400 mb-0">
                {{ $lowestScore !== null ? 'Evaluasi batas skor terendah' : 'Belum ada data' }}
              </p>
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- ==================================================
         3. CHART PERFORMANCE INDICATOR
         ================================================== --}}
    <div class="card radius-12 border border-neutral-200 shadow-none mb-24">
      <div class="card-header bg-transparent border-bottom border-neutral-200 py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h6 class="text-lg fw-semibold mb-0">Performance Indicator</h6>
          <p class="text-xs text-secondary-light mb-0">Perkembangan indikator kinerja karyawan selama 1 tahun ({{ date('Y') }})</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-primary-50 text-primary-600 border border-primary-200 radius-pill px-12 py-6 text-xs fw-semibold d-inline-flex align-items-center gap-1">
            <i class="ri-line-chart-line"></i> Performance selama 1 tahun
          </span>
        </div>
      </div>
      <div class="card-body p-24">
        <div id="chartPerformanceIndicator" style="min-height: 280px;"></div>
      </div>
    </div>

    {{-- ==================================================
         4. LIST PENGAJUAN CUTI SAYA
         ================================================== --}}
    <div class="card radius-12 border border-neutral-200 shadow-none mb-24">
      <div class="card-header bg-transparent border-bottom border-neutral-200 py-16 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h6 class="text-lg fw-semibold mb-0">Pengajuan Cuti Saya</h6>
          <p class="text-xs text-secondary-light mb-0">Riwayat pengajuan cuti khusus akun Anda</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('pengajuan-cuti.create') }}" class="btn btn-sm btn-primary-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-1">
            <i class="ri-add-line"></i> Ajukan Cuti
          </a>
          <a href="{{ route('pengajuan-cuti.index') }}" class="btn btn-sm btn-outline-neutral-600 radius-8 px-14 py-8 d-inline-flex align-items-center gap-1">
            Lihat Semua <i class="ri-arrow-right-s-line"></i>
          </a>
        </div>
      </div>

      <div class="card-body p-0">
        @if($myLeaveRequests->isNotEmpty())
          <div class="table-responsive">
            <table class="table bordered-table mb-0 align-middle">
              <thead>
                <tr>
                  <th scope="col" style="width: 60px;" class="text-center">No</th>
                  <th scope="col">Jenis Cuti</th>
                  <th scope="col">Periode Cuti</th>
                  <th scope="col">Tgl Pengajuan</th>
                  <th scope="col" class="text-center">Durasi</th>
                  <th scope="col" class="text-center">Status</th>
                  <th scope="col" class="text-center" style="width: 90px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach($myLeaveRequests as $index => $cuti)
                  @php
                    $encId = \App\Helpers\IdEncryptor::encrypt($cuti->id);
                    $jenisNama = $cuti->kategoriCuti->nama_kategori ?? 'Cuti Umum';
                    $durasi = $cuti->jumlah_hari ?? 0;

                    // Status Pengajuan Badge
                    $statusBadge = match($cuti->status_pengajuan) {
                        'Approve' => '<span class="px-12 py-4 rounded-pill fw-medium text-xs bg-success-100 text-success-600 d-inline-flex align-items-center gap-1"><i class="ri-checkbox-circle-fill"></i> Disetujui</span>',
                        'Reject'  => '<span class="px-12 py-4 rounded-pill fw-medium text-xs bg-danger-100 text-danger-600 d-inline-flex align-items-center gap-1"><i class="ri-close-circle-fill"></i> Ditolak</span>',
                        default   => '<span class="px-12 py-4 rounded-pill fw-medium text-xs bg-warning-100 text-warning-600 d-inline-flex align-items-center gap-1"><i class="ri-time-fill"></i> Menunggu</span>',
                    };
                  @endphp
                  <tr>
                    <td class="text-center text-secondary-light fw-medium">{{ $index + 1 }}</td>
                    <td>
                      <span class="fw-semibold text-neutral-800 d-block">{{ $jenisNama }}</span>
                      @if($cuti->keterangan)
                        <span class="text-xs text-secondary-light text-truncate d-inline-block" style="max-width: 250px;">
                          {{ $cuti->keterangan }}
                        </span>
                      @endif
                    </td>
                    <td>
                      <span class="text-sm fw-medium text-neutral-800">
                        {{ $cuti->tanggal_awal ? $cuti->tanggal_awal->format('d M Y') : '-' }}
                        @if($cuti->tanggal_akhir && $cuti->tanggal_awal != $cuti->tanggal_akhir)
                          - {{ $cuti->tanggal_akhir->format('d M Y') }}
                        @endif
                      </span>
                    </td>
                    <td class="text-sm text-secondary-light">
                      {{ $cuti->created_at ? $cuti->created_at->format('d M Y') : ($cuti->created_date ? $cuti->created_date->format('d M Y') : '-') }}
                    </td>
                    <td class="text-center">
                      <span class="fw-semibold text-neutral-900">{{ $durasi }}</span>
                      <span class="text-xs text-secondary-light">Hari</span>
                    </td>
                    <td class="text-center">
                      {!! $statusBadge !!}
                    </td>
                    <td class="text-center">
                      <a href="{{ route('pengajuan-cuti.show', $encId) }}" 
                         class="w-32-px h-32-px bg-primary-50 text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center hover-bg-primary-600 hover-text-white transition-2"
                         title="Lihat Detail Pengajuan">
                        <i class="ri-eye-line text-sm"></i>
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          {{-- Empty State --}}
          <div class="text-center py-48 px-24">
            <div class="w-64-px h-64-px bg-neutral-100 text-neutral-400 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-16">
              <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-3xl"></iconify-icon>
            </div>
            <h6 class="fw-semibold text-neutral-800 mb-6">Belum Ada Pengajuan Cuti</h6>
            <p class="text-secondary-light text-sm mb-20 max-w-400-px mx-auto">
              Anda belum memiliki riwayat permohonan cuti. Klik tombol di bawah untuk membuat pengajuan cuti baru secara mudah.
            </p>
            <a href="{{ route('pengajuan-cuti.create') }}" class="btn btn-primary-600 radius-8 px-20 py-10 d-inline-flex align-items-center gap-2">
              <i class="ri-add-line"></i> Buat Pengajuan Cuti Baru
            </a>
          </div>
        @endif
      </div>
    </div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Render ApexCharts Performance Indicator
    var seriesData = @json($performanceSeries);
    var monthCategories = @json($performanceMonths);

    var optionsPerformance = {
      series: [{
        name: 'Performance Score',
        data: seriesData
      }],
      chart: {
        type: 'area',
        height: 280,
        toolbar: { show: false },
        zoom: { enabled: false },
        fontFamily: 'inherit'
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.35,
          opacityTo: 0.05,
          stops: [0, 90, 100]
        }
      },
      colors: ["#487fff"],
      stroke: {
        curve: 'smooth',
        width: 3
      },
      markers: {
        size: 4,
        colors: ["#487fff"],
        strokeColors: "#ffffff",
        strokeWidth: 2
      },
      grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4
      },
      xaxis: {
        categories: monthCategories,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px'
          }
        }
      },
      yaxis: {
        min: 0,
        max: 100,
        tickAmount: 5,
        labels: {
          style: {
            colors: '#64748b',
            fontSize: '12px'
          },
          formatter: function (val) {
            return Math.round(val);
          }
        }
      },
      tooltip: {
        theme: 'light',
        y: {
          formatter: function (val) {
            return (val !== null && val !== undefined) ? val + " Poin" : "Belum ada data";
          }
        }
      },
      noData: {
        text: "Belum ada data indikator kinerja untuk periode ini",
        align: "center",
        verticalAlign: "middle",
        style: {
          color: "#94a3b8",
          fontSize: "14px",
          fontFamily: "inherit"
        }
      }
    };

    var chartEl = document.querySelector("#chartPerformanceIndicator");
    if (chartEl && typeof ApexCharts !== 'undefined') {
      var chartPerformance = new ApexCharts(chartEl, optionsPerformance);
      chartPerformance.render();
    }
  });
</script>
@endpush