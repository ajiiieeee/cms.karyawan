@extends('layouts.main')

@section('title', 'Dashboard Karyawan')

@push('styles')
<style>
  /* Selaras dengan halaman KPI: radius 16px, token var(--*) agar ikut dark mode */
  :root {
    --dash-primary: #4338CA;
    --dash-success: #059669;
    --dash-warning: #B45309;
    --dash-purple: #6D28D9;
  }

  /* Universal Card */
  .modern-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  }
  .modern-card:hover {
    transform: translateY(-3px);
    border-color: var(--neutral-300);
    box-shadow: 0 10px 24px -4px rgba(15, 23, 42, 0.08);
  }
  [data-theme="dark"] .modern-card {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
  }

  /* Metric Stat Cards with Floating Icon Badge */
  .modern-stat-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-radius: 16px;
    padding: 24px 20px 14px 20px;
    position: relative;
    margin-top: 18px;
    height: calc(100% - 18px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  }
  .modern-stat-card:hover {
    transform: translateY(-4px);
    border-color: var(--neutral-300);
    box-shadow: 0 12px 28px -6px rgba(15, 23, 42, 0.09);
  }
  [data-theme="dark"] .modern-stat-card {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
  }

  .stat-floating-badge {
    position: absolute;
    top: -18px;
    left: 20px;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
    font-size: 22px;
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.12);
    z-index: 2;
  }
  .badge-blue {
    background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
    box-shadow: 0 6px 14px rgba(67, 56, 202, 0.35);
  }
  .badge-emerald {
    background: linear-gradient(135deg, #059669 0%, #0E7490 100%);
    box-shadow: 0 6px 14px rgba(5, 150, 105, 0.35);
  }
  .badge-amber {
    background: linear-gradient(135deg, #D97706 0%, #B45309 100%);
    box-shadow: 0 6px 14px rgba(180, 83, 9, 0.35);
  }
  .badge-purple {
    background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
    box-shadow: 0 6px 14px rgba(139, 92, 246, 0.35);
  }

  .stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 6px;
    margin-bottom: 8px;
  }
  .stat-card-label {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary-light);
  }
  .stat-pill {
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
  }
  .stat-pill.success {
    background-color: rgba(16,185,129,.14);
    color: #059669;
    border: 1px solid rgba(16,185,129,.35);
  }
  [data-theme="dark"] .stat-pill.success { color: #34D399; }
  .stat-pill.warning {
    background-color: rgba(245,158,11,.14);
    color: #B45309;
    border: 1px solid rgba(245,158,11,.35);
  }
  [data-theme="dark"] .stat-pill.warning { color: #FBBF24; }
  .stat-pill.info {
    background-color: rgba(59,130,246,.12);
    color: #2563EB;
    border: 1px solid rgba(59,130,246,.35);
  }
  [data-theme="dark"] .stat-pill.info { color: #93C5FD; }

  .stat-card-number {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary-light);
    letter-spacing: -0.5px;
    line-height: 1.2;
    margin-bottom: 2px;
  }
  .stat-card-sub {
    font-size: 14px;
    color: var(--text-secondary-light);
  }
  .stat-sparkline-box {
    margin: 4px -10px -10px -10px;
    height: 55px;
    overflow: hidden;
  }

  /* Performance Score Card — sama dengan .kpi-card di halaman KPI */
  .modern-perf-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-radius: 16px;
    padding: 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }
  .modern-perf-card:hover {
    transform: translateY(-3px);
    border-color: var(--neutral-300);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  }
  .perf-score-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary-light);
    letter-spacing: -0.5px;
    line-height: 1.2;
  }
  .perf-card-label {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary-light);
    flex: 1 1 auto;
    min-width: 0;
  }
  .modern-perf-card .badge {
    flex-shrink: 0;
    white-space: nowrap;
  }
  .modern-perf-card .d-flex.align-items-center.justify-content-between {
    gap: 8px;
  }
  .perf-progress-track {
    height: 8px;
    border-radius: 10px;
    background-color: var(--neutral-200);
    overflow: hidden;
    margin-top: 12px;
    margin-bottom: 8px;
  }
  .perf-progress-bar {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
  }

  /* Chart Card Navigation Tabs */
  .chart-filter-tabs {
    background-color: var(--neutral-100);
    border: 1px solid var(--neutral-200);
    border-radius: 10px;
    padding: 3px;
    display: inline-flex;
    gap: 3px;
    flex-wrap: wrap;
  }
  .chart-filter-tab {
    padding: 8px 14px;
    min-height: 40px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary-light);
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  .chart-filter-tab.active, .chart-filter-tab:hover {
    background-color: var(--white);
    color: var(--text-primary-light);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  }
  .chart-filter-tab:focus-visible {
    outline: 3px solid #A5B4FC;
    outline-offset: 2px;
  }

  /* Detailed Table Styles — sama dengan .kpi-table */
  .modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .modern-table th {
    background-color: var(--neutral-50);
    color: var(--text-secondary-light);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--neutral-200);
    white-space: nowrap;
  }
  .modern-table td {
    padding: 16px 20px;
    font-size: 14px;
    color: var(--text-primary-light);
    border-bottom: 1px solid var(--neutral-200);
    vertical-align: middle;
  }
  .modern-table tbody tr:hover td {
    background-color: var(--neutral-50);
  }
  .modern-table tr:last-child td {
    border-bottom: none;
  }

  .dash-action-btn {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 10px;
    text-decoration: none;
  }
  .dash-action-btn:focus-visible {
    outline: 3px solid #A5B4FC;
    outline-offset: 2px;
  }

  @media (max-width: 575.98px) {
    .stat-card-number, .perf-score-value { font-size: 24px; }
    .modern-stat-card { padding: 22px 16px 12px 16px; }
    .modern-table td { padding: 12px 14px; font-size: 13px; }
  }
</style>
@endpush

@section('content')
@php
    $karyawan = $karyawan ?? null;
    $userDisplayName = $karyawan->nama_karyawan ?? (Auth::user()->nama ?? 'Karyawan');
    
    // Statistics safe variables
    $totalSaldoCuti = $totalSaldoCuti ?? 12;
    $sisaCutiTahunan = $sisaCutiTahunan ?? 12;
    $terpakaiCutiTahunan = $terpakaiCutiTahunan ?? 0;
    $periodeCutiLabel = $periodeCutiLabel ?? ('Periode ' . ($tahunDashboard ?? date('Y')));

    $totalFimp = $totalFimp ?? 0;
    $fimpApproved = $fimpApproved ?? 0;
    $fimpPending = $fimpPending ?? 0;

    $totalOvertime = $totalOvertime ?? 0;
    $lemburMenunggu = $lemburMenunggu ?? 0;
    $lemburDisetujui = $lemburDisetujui ?? 0;

    $latestPerformance = $latestPerformance ?? 95.0;
    $averageScore = $averageScore ?? 85.9;
    $bestScore = $bestScore ?? 95.0;
    $lowestScore = $lowestScore ?? 78.0;
    $kpiLatestPeriod = $kpiLatestPeriod ?? 'Agustus 2026';
    $kpiLatestStatus = $kpiLatestStatus ?? 'Excellent';
    $kpiTotalEvaluations = $kpiTotalEvaluations ?? 8;

    $performanceMonths = $performanceMonths ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $performanceSeries = $performanceSeries ?? [82, 86, 78, 89, 86, 85, 86, 95, null, null, null, null];

    $activityMonths = $activityMonths ?? $performanceMonths;
    $activitySeries = $activitySeries ?? [2, 1, 3, 2, 4, 1, 5, 3, 2, 4, 2, 3];
    $approvedSeries = $approvedSeries ?? [2, 1, 2, 2, 3, 1, 4, 3, 2, 3, 2, 3];

    $myLeaveRequests = $myLeaveRequests ?? ($pengajuanCuti ?? collect());
@endphp

    {{-- Breadcrumb & Welcome Greeting --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h5 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 20px;">Dashboard Karyawan</h5>
        <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">
          Selamat datang kembali, <strong style="color: var(--text-primary-light);">{{ $userDisplayName }}</strong>! Berikut ringkasan status kerja, saldo cuti, dan evaluasi performa Anda.
        </p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('leave-requests.create') }}" class="btn btn-sm btn-primary radius-8 px-16 py-8 d-inline-flex align-items-center gap-2 shadow-sm dash-action-btn">
          <i class="ri-add-line font-bold" aria-hidden="true"></i> Ajukan Cuti
        </a>
        <a href="{{ route('fimp.create') }}" class="btn btn-sm btn-outline-secondary radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 dash-action-btn" style="background-color: var(--white);">
          <i class="ri-file-text-line" aria-hidden="true"></i> Form Izin (FIMP)
        </a>
        <a href="{{ route('overtime-requests.create') }}" class="btn btn-sm btn-outline-secondary radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 dash-action-btn" style="background-color: var(--white);">
          <i class="ri-time-line" aria-hidden="true"></i> Permohonan Lembur
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-24 radius-12 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
          <i class="ri-checkbox-circle-fill text-lg"></i>
          <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- ==================================================
         1. CARD UTAMA DASHBOARD KARYAWAN (4 CARDS DENGAN SPARKLINE)
         ================================================== --}}
    <div class="row g-4 mb-28">
      
      {{-- Card 1: Total Saldo Cuti --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="modern-stat-card">
          <div class="stat-floating-badge badge-blue">
            <i class="ri-calendar-check-fill"></i>
          </div>
          <div>
            <div class="stat-card-header">
              <span class="stat-card-label">Total Saldo Cuti</span>
              <span class="stat-pill info">
                <i class="ri-calendar-line"></i> {{ date('Y') }}
              </span>
            </div>
            <div class="stat-card-number">
              {{ $totalSaldoCuti }} <span class="fs-6 fw-semibold text-secondary">Hari</span>
            </div>
            <div class="stat-card-sub text-truncate">
              {{ $periodeCutiLabel }}
            </div>
          </div>
          <div class="stat-sparkline-box">
            <div id="sparkSaldoCuti"></div>
          </div>
        </div>
      </div>

      {{-- Card 2: Sisa Cuti Tahunan --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="modern-stat-card">
          <div class="stat-floating-badge badge-emerald">
            <i class="ri-sun-fill"></i>
          </div>
          <div>
            <div class="stat-card-header">
              <span class="stat-card-label">Sisa Cuti Tahunan</span>
              <span class="stat-pill success">
                <i class="ri-check-line"></i> {{ $sisaCutiTahunan > 0 ? 'Tersedia' : 'Habis' }}
              </span>
            </div>
            <div class="stat-card-number">
              {{ $sisaCutiTahunan }} <span class="fs-6 fw-semibold text-secondary">Hari</span>
            </div>
            <div class="stat-card-sub">
              Terpakai: <strong class="text-dark">{{ $terpakaiCutiTahunan }} Hari</strong>
            </div>
          </div>
          <div class="stat-sparkline-box">
            <div id="sparkSisaCuti"></div>
          </div>
        </div>
      </div>

      {{-- Card 3: Izin Form (FIMP) --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="modern-stat-card">
          <div class="stat-floating-badge badge-amber">
            <i class="ri-file-list-3-fill"></i>
          </div>
          <div>
            <div class="stat-card-header">
              <span class="stat-card-label">Izin Kerja (FIMP)</span>
              <span class="stat-pill warning">
                {{ $fimpPending }} Menunggu
              </span>
            </div>
            <div class="stat-card-number">
              {{ $totalFimp }} <span class="fs-6 fw-semibold text-secondary">Pengajuan</span>
            </div>
            <div class="stat-card-sub text-truncate">
              {{ $fimpApproved }} Disetujui • {{ $fimpPending }} Menunggu
            </div>
          </div>
          <div class="stat-sparkline-box">
            <div id="sparkFimp"></div>
          </div>
        </div>
      </div>

      {{-- Card 4: Overtime Request --}}
      <div class="col-xl-3 col-md-6 col-12">
        <div class="modern-stat-card">
          <div class="stat-floating-badge badge-purple">
            <i class="ri-time-fill"></i>
          </div>
          <div>
            <div class="stat-card-header">
              <span class="stat-card-label">Overtime Request</span>
              <span class="stat-pill info">
                {{ $lemburDisetujui }} Selesai
              </span>
            </div>
            <div class="stat-card-number">
              {{ $totalOvertime }} <span class="fs-6 fw-semibold text-secondary">Pengajuan</span>
            </div>
            <div class="stat-card-sub">
              {{ $lemburMenunggu }} Pengajuan menunggu approval
            </div>
          </div>
          <div class="stat-sparkline-box">
            <div id="sparkOvertime"></div>
          </div>
        </div>
      </div>

    </div>

    {{-- ==================================================
         2. SECTION PERFORMANCE KARYAWAN — SAMA DENGAN HALAMAN KPI
         ================================================== --}}
    <div class="mb-28">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-16">
        <div>
          <h6 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 16px;">Informasi Performance Karyawan</h6>
          <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Sama dengan halaman KPI — periode {{ $kpiLatestPeriod }}, {{ $kpiTotalEvaluations }} penilaian (Jan–Agu)</p>
        </div>
        <a href="{{ route('performance-indicator.index') }}" class="btn btn-sm btn-outline-primary radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 dash-action-btn">
          <i class="ri-line-chart-line" aria-hidden="true"></i> Lihat Detail KPI
        </a>
      </div>

      <div class="row g-4">

        {{-- Performance 1: Latest Performance --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="modern-perf-card">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-8">
                <span class="perf-card-label">Latest Performance</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle radius-6 px-8 py-3 fw-semibold" style="font-size: 12px;">
                  {{ $kpiLatestStatus }}
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="perf-score-value">{{ number_format($latestPerformance, 1) }}</span>
                <span class="fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">/ 100</span>
              </div>
              <div class="perf-progress-track" role="progressbar" aria-valuenow="{{ $latestPerformance }}" aria-valuemin="0" aria-valuemax="100" aria-label="Latest performance {{ number_format($latestPerformance, 1) }} dari 100">
                <div class="perf-progress-bar" style="width: {{ min(100, $latestPerformance) }}%; background: linear-gradient(90deg, #6366F1, #4338CA);"></div>
              </div>
            </div>
            <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">
              {{ $kpiLatestPeriod }} • Skor /100, 1 desimal
            </p>
          </div>
        </div>

        {{-- Performance 2: Average Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="modern-perf-card">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-8">
                <span class="perf-card-label">Average Score</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle radius-6 px-8 py-3 fw-semibold" style="font-size: 12px;">
                  Rata-Rata
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="perf-score-value">{{ number_format($averageScore, 1) }}</span>
                <span class="fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">/ 100</span>
              </div>
              <div class="perf-progress-track" role="progressbar" aria-valuenow="{{ $averageScore }}" aria-valuemin="0" aria-valuemax="100" aria-label="Average score {{ number_format($averageScore, 1) }} dari 100">
                <div class="perf-progress-bar" style="width: {{ min(100, $averageScore) }}%; background: linear-gradient(90deg, #059669, #0E7490);"></div>
              </div>
            </div>
            <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">
              Dari {{ $kpiTotalEvaluations }} periode penilaian
            </p>
          </div>
        </div>

        {{-- Performance 3: Best Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="modern-perf-card">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-8">
                <span class="perf-card-label">Best Score</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle radius-6 px-8 py-3 fw-semibold" style="font-size: 12px;">
                  Tertinggi
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="perf-score-value">{{ number_format($bestScore, 1) }}</span>
                <span class="fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">/ 100</span>
              </div>
              <div class="perf-progress-track" role="progressbar" aria-valuenow="{{ $bestScore }}" aria-valuemin="0" aria-valuemax="100" aria-label="Best score {{ number_format($bestScore, 1) }} dari 100">
                <div class="perf-progress-bar" style="width: {{ min(100, $bestScore) }}%; background: linear-gradient(90deg, #059669, #047857);"></div>
              </div>
            </div>
            <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">
              Pencapaian skor evaluasi terbaik
            </p>
          </div>
        </div>

        {{-- Performance 4: Lowest Score --}}
        <div class="col-xl-3 col-md-6 col-12">
          <div class="modern-perf-card">
            <div>
              <div class="d-flex align-items-center justify-content-between mb-8">
                <span class="perf-card-label">Lowest Score</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle radius-6 px-8 py-3 fw-semibold" style="font-size: 12px;">
                  Terendah
                </span>
              </div>
              <div class="d-flex align-items-baseline gap-2">
                <span class="perf-score-value">{{ number_format($lowestScore, 1) }}</span>
                <span class="fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">/ 100</span>
              </div>
              <div class="perf-progress-track" role="progressbar" aria-valuenow="{{ $lowestScore }}" aria-valuemin="0" aria-valuemax="100" aria-label="Lowest score {{ number_format($lowestScore, 1) }} dari 100">
                <div class="perf-progress-bar" style="width: {{ min(100, $lowestScore) }}%; background: linear-gradient(90deg, #94A3B8, #64748B);"></div>
              </div>
            </div>
            <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">
              Batas evaluasi skor terendah
            </p>
          </div>
        </div>

      </div>
    </div>

    {{-- ==================================================
         3. GRAFIK TERPISAH: (A) TREN KPI + (B) AKTIVITAS
         Satu metrik satu kartu — tanpa dual-axis yang membingungkan.
         ================================================== --}}
    @php
      $kpiPairs = collect($performanceMonths ?? [])->zip($performanceSeries ?? [])->filter(fn ($p) => ! is_null($p[1]))->values();
      $kpiPrev = $kpiPairs->count() >= 2 ? $kpiPairs[$kpiPairs->count() - 2] : null;
      $kpiLast = $kpiPairs->count() >= 1 ? $kpiPairs->last() : null;
      $kpiDelta = ($kpiLast && $kpiPrev) ? round($kpiLast[1] - $kpiPrev[1], 1) : null;
      $kpiBestPair = $kpiPairs->sortBy(fn ($p) => $p[1])->values()->last();
      $kpiWorstPair = $kpiPairs->sortBy(fn ($p) => $p[1])->values()->first();
      $totalActivityAll = collect($activitySeries ?? [])->sum();
      $totalApprovedAll = collect($approvedSeries ?? [])->sum();
    @endphp
    <div class="row g-4 mb-28">

      {{-- (A) Tren Skor KPI --}}
      <div class="col-xl-7 col-12">
        <div class="modern-card p-24 h-100">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-16 pb-16" style="border-bottom: 1px solid var(--neutral-200);">
            <div>
              <h6 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 16px;">Tren Skor KPI</h6>
              <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Jan–Agu 2026 • poin, skala 0–100 • Sep–Des belum ada data</p>
            </div>
            <a href="{{ route('performance-indicator.index') }}" class="btn btn-sm btn-outline-primary radius-8 px-14 py-8 dash-action-btn">Detail KPI</a>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-12">
            @if(! is_null($kpiDelta))
              <span class="stat-pill {{ $kpiDelta >= 0 ? 'success' : 'warning' }}">
                <i class="{{ $kpiDelta >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line' }}" aria-hidden="true"></i>
                {{ $kpiDelta >= 0 ? '+' : '' }}{{ number_format($kpiDelta, 1) }} poin vs {{ $kpiPrev[0] ?? '' }}
              </span>
            @endif
            @if($kpiBestPair)
              <span class="stat-pill success">Terbaik {{ $kpiBestPair[0] }}: {{ number_format($kpiBestPair[1], 1) }}</span>
            @endif
            @if($kpiWorstPair)
              <span class="stat-pill warning">Terendah {{ $kpiWorstPair[0] }}: {{ number_format($kpiWorstPair[1], 1) }}</span>
            @endif
          </div>
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-12">
            <span style="color: var(--text-secondary-light); font-size: 13px;">Filter berlaku untuk kedua grafik:</span>
            <div class="chart-filter-tabs" role="tablist" aria-label="Rentang grafik">
              <button type="button" role="tab" aria-selected="true" class="chart-filter-tab active" onclick="updateChartRange('all', this)">Semua</button>
              <button type="button" role="tab" aria-selected="false" class="chart-filter-tab" onclick="updateChartRange('sem2', this)">Jul–Des</button>
              <button type="button" role="tab" aria-selected="false" class="chart-filter-tab" onclick="updateChartRange('sem1', this)">Jan–Jun</button>
            </div>
          </div>
          <div id="chartKpiTrend" style="min-height: 300px;" role="img" aria-label="Grafik garis tren skor KPI Januari hingga Agustus, September hingga Desember belum ada data"></div>
        </div>
      </div>

      {{-- (B) Aktivitas Pengajuan --}}
      <div class="col-xl-5 col-12">
        <div class="modern-card p-24 h-100">
          <div class="mb-16 pb-16" style="border-bottom: 1px solid var(--neutral-200);">
            <h6 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 16px;">Aktivitas Pengajuan</h6>
            <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Total vs Disetujui per bulan • satuan pengajuan</p>
          </div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-12">
            <span class="stat-pill info">Total {{ $totalActivityAll }} pengajuan</span>
            <span class="stat-pill success">Disetujui {{ $totalApprovedAll }}</span>
          </div>
          <div id="chartActivity" style="min-height: 300px;" role="img" aria-label="Grafik batang total dan disetujui pengajuan per bulan"></div>
        </div>
      </div>

    </div>

    {{-- ==================================================
         4. LIST PENGAJUAN CUTI SAYA
         ================================================== --}}
    <div class="modern-card overflow-hidden mb-28">
      <div class="p-20 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-bottom: 1px solid var(--neutral-200);">
        <div>
          <h6 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 16px;">Pengajuan Cuti Saya</h6>
          <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Riwayat permohonan cuti dan status persetujuan akun Anda</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('leave-requests.create') }}" class="btn btn-sm btn-primary radius-8 px-14 py-7 d-inline-flex align-items-center gap-1 shadow-sm dash-action-btn">
            <i class="ri-add-line" aria-hidden="true"></i> Ajukan Cuti Baru
          </a>
        </div>
      </div>

      <div class="p-0">
        @if($myLeaveRequests->isNotEmpty())
          <div class="table-responsive">
            <table class="modern-table">
              <thead>
                <tr>
                  <th scope="col" style="width: 60px;" class="text-center">No</th>
                  <th scope="col">Jenis Cuti</th>
                  <th scope="col">Periode Cuti</th>
                  <th scope="col">Tgl Pengajuan</th>
                  <th scope="col" class="text-center">Durasi</th>
                  <th scope="col" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($myLeaveRequests as $index => $cuti)
                  @php
                    $jenisNama = $cuti->kategoriCuti->nama_kategori ?? 'Cuti Tahunan';
                    $durasi = $cuti->jumlah_hari ?? 0;

                    // Status Badge Styling
                    $statusBadge = match($cuti->status_pengajuan) {
                        'Approve' => '<span class="px-12 py-4 rounded-pill fw-semibold text-xs bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1"><i class="ri-checkbox-circle-fill"></i> Disetujui</span>',
                        'Reject'  => '<span class="px-12 py-4 rounded-pill fw-semibold text-xs bg-danger-subtle text-danger border border-danger-subtle d-inline-flex align-items-center gap-1"><i class="ri-close-circle-fill"></i> Ditolak</span>',
                        default   => '<span class="px-12 py-4 rounded-pill fw-semibold text-xs bg-warning-subtle text-warning border border-warning-subtle d-inline-flex align-items-center gap-1"><i class="ri-time-fill"></i> Menunggu</span>',
                    };
                  @endphp
                  <tr>
                    <td class="text-center text-secondary fw-semibold">{{ $index + 1 }}</td>
                    <td>
                      <span class="fw-bold text-dark d-block">{{ $jenisNama }}</span>
                      @if($cuti->keterangan)
                        <span class="text-xs text-secondary text-truncate d-inline-block" style="max-width: 280px;">
                          {{ $cuti->keterangan }}
                        </span>
                      @endif
                    </td>
                    <td>
                      <span class="text-sm fw-medium text-dark">
                        {{ $cuti->tanggal_awal ? $cuti->tanggal_awal->format('d M Y') : '-' }}
                        @if($cuti->tanggal_akhir && $cuti->tanggal_awal != $cuti->tanggal_akhir)
                          - {{ $cuti->tanggal_akhir->format('d M Y') }}
                        @endif
                      </span>
                    </td>
                    <td class="text-sm text-secondary">
                      {{ $cuti->created_at ? $cuti->created_at->format('d M Y') : '-' }}
                    </td>
                    <td class="text-center">
                      <span class="fw-bold text-dark fs-6">{{ $durasi }}</span>
                      <span class="text-xs text-secondary">Hari</span>
                    </td>
                    <td class="text-center">
                      {!! $statusBadge !!}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          {{-- Empty State --}}
          <div class="text-center py-48 px-24">
            <div class="w-64-px h-64-px bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-16 border">
              <i class="ri-calendar-event-line fs-2"></i>
            </div>
            <h6 class="fw-bold text-dark mb-6">Belum Ada Pengajuan Cuti</h6>
            <p class="text-secondary text-sm mb-20 max-w-400-px mx-auto">
              Anda belum memiliki riwayat permohonan cuti. Klik tombol di bawah untuk membuat pengajuan cuti baru dengan mudah.
            </p>
            <a href="{{ route('leave-requests.create') }}" class="btn btn-primary radius-8 px-20 py-10 d-inline-flex align-items-center gap-2 shadow-sm">
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
    const fullMonths = @json($performanceMonths);
    const fullPerformanceData = @json($performanceSeries);
    const fullActivityData = @json($activitySeries);
    const fullApprovedData = @json($approvedSeries ?? []);

    // ==========================================================
    // 1. MINI SPARKLINE CHARTS FOR THE 4 METRIC CARDS
    // ==========================================================
    function renderSparkline(elId, color, data) {
      const el = document.querySelector(elId);
      if (!el || typeof ApexCharts === 'undefined') return;

      const options = {
        series: [{ data: data }],
        chart: {
          type: 'area',
          height: 55,
          sparkline: { enabled: true }
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
        stroke: {
          curve: 'smooth',
          width: 2.2
        },
        colors: [color],
        tooltip: {
          enabled: false
        }
      };

      const chart = new ApexCharts(el, options);
      chart.render();
    }

    renderSparkline('#sparkSaldoCuti', '#4338CA', [12, 12, 12, 11, 10, 12, 12]);
    renderSparkline('#sparkSisaCuti', '#059669', [12, 12, 11, 10, 9, 8, 8]);
    renderSparkline('#sparkFimp', '#B45309', [1, 2, 1, 3, 2, 4, 3]);
    renderSparkline('#sparkOvertime', '#6D28D9', [0, 1, 2, 1, 3, 2, 2]);

    // ==========================================================
    // 2. DUA GRAFIK TERPISAH: (A) TREN KPI + (B) AKTIVITAS
    // ==========================================================
    let kpiChart = null;
    let activityChart = null;

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? '#323D4E' : '#E2E8F0';
    const labelColor = isDark ? '#9CA3AF' : '#64748B';

    // Tandai titik terbaik / terendah agar langsung terbaca staff umum
    const kpiValues = fullPerformanceData.filter(v => v !== null && v !== undefined);
    const kpiMax = kpiValues.length ? Math.max(...kpiValues) : null;
    const kpiMin = kpiValues.length ? Math.min(...kpiValues) : null;
    const kpiDiscrete = fullPerformanceData.map((v, i) => {
      if (v === null || v === undefined) return null;
      if (v === kpiMax) return { seriesIndex: 0, dataPointIndex: i, fillColor: '#059669', strokeColor: '#FFFFFF', size: 7 };
      if (v === kpiMin) return { seriesIndex: 0, dataPointIndex: i, fillColor: '#DC2626', strokeColor: '#FFFFFF', size: 7 };
      return null;
    }).filter(Boolean);

    // --- (A) Tren Skor KPI: garis lurus (data diskrit), target 85 ---
    const kpiOptions = {
      series: [{ name: 'Skor KPI (poin)', data: fullPerformanceData }],
      chart: { type: 'line', height: 300, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
      stroke: { curve: 'straight', width: 3 },
      markers: {
        size: 5, colors: ['#4338CA'], strokeColors: '#FFFFFF', strokeWidth: 2, hover: { size: 7 },
        discrete: kpiDiscrete
      },
      colors: ['#4338CA'],
      grid: { borderColor: gridColor, strokeDashArray: 4 },
      annotations: {
        yaxis: [{
          y: 85, borderColor: '#B45309', strokeDashArray: 6, opacity: 0.9,
          label: { text: 'Target 85', style: { color: '#fff', background: '#B45309', fontSize: '12px' } }
        }]
      },
      xaxis: {
        categories: fullMonths, axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: labelColor, fontSize: '13px' } }
      },
      yaxis: {
        min: 0, max: 100, tickAmount: 5,
        title: { text: 'Poin (0–100)', style: { color: labelColor, fontSize: '12px', fontWeight: 600 } },
        labels: { style: { colors: labelColor, fontSize: '13px' }, formatter: val => Number(val).toFixed(0) }
      },
      legend: { show: false },
      tooltip: {
        theme: isDark ? 'dark' : 'light', shared: false, intersect: true,
        y: { formatter: val => (val === null || val === undefined) ? 'Belum ada data' : Number(val).toFixed(1) + ' poin (/100)' }
      }
    };

    const kpiEl = document.querySelector("#chartKpiTrend");
    if (kpiEl && typeof ApexCharts !== 'undefined') {
      kpiChart = new ApexCharts(kpiEl, kpiOptions);
      kpiChart.render();
    }

    // --- (B) Aktivitas: bar Total vs Disetujui ---
    const activityOptions = {
      series: [
        { name: 'Total', data: fullActivityData },
        { name: 'Disetujui', data: fullApprovedData }
      ],
      chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
      plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: false },
      colors: ['#4338CA', '#059669'],
      grid: { borderColor: gridColor, strokeDashArray: 4 },
      xaxis: {
        categories: fullMonths, axisBorder: { show: false }, axisTicks: { show: false },
        labels: { style: { colors: labelColor, fontSize: '13px' } }
      },
      yaxis: {
        min: 0, forceNiceScale: true,
        title: { text: 'Pengajuan', style: { color: labelColor, fontSize: '12px', fontWeight: 600 } },
        labels: { style: { colors: labelColor, fontSize: '13px' }, formatter: val => Math.round(val) }
      },
      legend: { position: 'top', horizontalAlign: 'right', fontSize: '13px', fontWeight: 600, markers: { radius: 12 }, labels: { colors: labelColor } },
      tooltip: {
        theme: isDark ? 'dark' : 'light', shared: true, intersect: false,
        y: { formatter: val => (val === null || val === undefined) ? '0' : val + ' pengajuan' }
      }
    };

    const activityEl = document.querySelector("#chartActivity");
    if (activityEl && typeof ApexCharts !== 'undefined') {
      activityChart = new ApexCharts(activityEl, activityOptions);
      activityChart.render();
    }

    // Satu filter mengendalikan kedua grafik
    window.updateChartRange = function(range, btn) {
      document.querySelectorAll('.chart-filter-tab').forEach(el => {
        el.classList.remove('active');
        el.setAttribute('aria-selected', 'false');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');

      let cats = fullMonths, kpi = fullPerformanceData, act = fullActivityData, appr = fullApprovedData;
      if (range === 'sem1') {
        cats = fullMonths.slice(0, 6); kpi = fullPerformanceData.slice(0, 6);
        act = fullActivityData.slice(0, 6); appr = fullApprovedData.slice(0, 6);
      } else if (range === 'sem2') {
        cats = fullMonths.slice(6, 12); kpi = fullPerformanceData.slice(6, 12);
        act = fullActivityData.slice(6, 12); appr = fullApprovedData.slice(6, 12);
      }
      if (kpiChart) {
        kpiChart.updateOptions({ xaxis: { categories: cats } });
        kpiChart.updateSeries([{ name: 'Skor KPI (poin)', data: kpi }]);
      }
      if (activityChart) {
        activityChart.updateOptions({ xaxis: { categories: cats } });
        activityChart.updateSeries([
          { name: 'Total', data: act },
          { name: 'Disetujui', data: appr }
        ]);
      }
    };
  });
</script>
@endpush