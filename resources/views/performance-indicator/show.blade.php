@extends('layouts.main')

@section('title', 'Detail Performance Indicator - ' . $item['periode_label'])

@push('styles')
<style>
  .btn-back-kpi {
    border: 1px solid #4F46E5;
    color: #4F46E5;
    background-color: var(--white);
    border-radius: 10px;
    padding: 12px 20px;
    min-height: 44px;
    font-size: 14px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
  }
  .btn-back-kpi:hover {
    background-color: #4F46E5;
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
  }
  .btn-back-kpi:focus-visible {
    outline: 3px solid #A5B4FC;
    outline-offset: 2px;
  }

  .kpi-hero-banner {
    background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
    border-radius: 16px;
    padding: 40px 24px;
    text-align: center;
    color: #FFFFFF;
    box-shadow: 0 4px 20px rgba(79, 70, 229, 0.25);
    margin-bottom: 28px;
  }
  [data-theme="dark"] .kpi-hero-banner {
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.45);
    border: 1px solid rgba(255,255,255,.12);
  }
  .kpi-hero-score {
    font-size: 52px;
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -1px;
    margin-bottom: 8px;
  }
  .kpi-hero-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
  }
  .kpi-hero-period {
    font-size: 15px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 999px;
    padding: 8px 16px;
  }

  .aspect-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-radius: 14px;
    padding: 20px 22px;
    height: 100%;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .aspect-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
  }
  [data-theme="dark"] .aspect-card {
    box-shadow: 0 2px 12px rgba(0,0,0,.35);
  }

  .aspect-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background-color: rgba(79,70,229,.1);
    color: #4F46E5;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  [data-theme="dark"] .aspect-icon-wrap {
    background-color: rgba(129,140,248,.16);
    color: #A5B4FC;
  }

  .aspect-progress-track {
    height: 8px;
    border-radius: 10px;
    background-color: var(--neutral-200);
    overflow: hidden;
    margin-top: 14px;
    margin-bottom: 8px;
  }
  .aspect-progress-bar {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, #6366F1, #4338CA);
    transition: width 0.6s ease;
  }

  .kpi-notes-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-left: 4px solid #4F46E5;
    border-radius: 12px;
    padding: 20px 24px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    margin-top: 24px;
  }
  .kpi-notes-card p {
    font-size: 15px;
    line-height: 1.7;
    color: var(--text-primary-light);
  }
  @media (max-width: 575.98px) {
    .kpi-hero-score { font-size: 40px; }
    .kpi-hero-banner { padding: 32px 16px; }
  }
</style>
@endpush

@section('content')
{{-- Kembali ke Daftar Button --}}
<div class="mb-24">
  <a href="{{ route('performance-indicator.index') }}" class="btn-back-kpi">
    <i class="ri-arrow-left-line"></i>
    <span>Kembali ke Daftar</span>
  </a>
</div>

{{-- Big Purple Hero Banner --}}
<div class="kpi-hero-banner" role="region" aria-label="Ringkasan skor {{ $item['periode_label'] }}: {{ $item['total_score'] }} dari {{ $item['total_max'] }}, {{ $item['performance_status'] }}">
  <div class="kpi-hero-score">
    {{ $item['total_score'] }}<span class="fs-4 fw-semibold opacity-75">/{{ $item['total_max'] }}</span>
  </div>
  <div class="kpi-hero-title">
    {{ $item['performance_status'] }}
  </div>
  <div>
    <span class="kpi-hero-period">
      <i class="ri-calendar-line" aria-hidden="true"></i>
      <span>Periode: {{ $item['periode_label'] }}</span>
    </span>
  </div>
</div>

{{-- Section: Detail Penilaian --}}
<div class="mb-24">
  <div class="mb-16">
    <h6 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: var(--text-primary-light); font-size: 16px;">
      <i class="ri-bar-chart-2-line text-primary-600" aria-hidden="true"></i> Detail Penilaian
    </h6>
    <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Breakdown nilai per aspek penilaian</p>
  </div>

  <div class="row g-4">

    {{-- Aspek 1: Kedisiplinan --}}
    <div class="col-xl-3 col-md-6 col-12">
      <div class="aspect-card">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="aspect-icon-wrap"><i class="ri-time-line" aria-hidden="true"></i></span>
            <span class="fw-semibold" style="color: var(--text-primary-light); font-size: 15px;">Kedisiplinan</span>
          </div>
          <div>
            <span class="fw-bold fs-5" style="color: var(--text-primary-light);">{{ $item['kedisiplinan_score'] }}</span>
            <span style="color: var(--text-secondary-light); font-size: 13px;">/{{ $item['kedisiplinan_max'] }}</span>
          </div>
        </div>
        <div class="aspect-progress-track" role="progressbar" aria-valuenow="{{ $kedisiplinanPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kedisiplinan {{ number_format($kedisiplinanPct, 1) }} persen">
          <div class="aspect-progress-bar" style="width: {{ $kedisiplinanPct }}%;"></div>
        </div>
        <div class="text-center fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">
          {{ number_format($kedisiplinanPct, 1) }}%
        </div>
      </div>
    </div>

    {{-- Aspek 2: Mengajar --}}
    <div class="col-xl-3 col-md-6 col-12">
      <div class="aspect-card">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="aspect-icon-wrap"><i class="ri-book-open-line" aria-hidden="true"></i></span>
            <span class="fw-semibold" style="color: var(--text-primary-light); font-size: 15px;">Mengajar</span>
          </div>
          <div>
            <span class="fw-bold fs-5" style="color: var(--text-primary-light);">{{ $item['mengajar_score'] }}</span>
            <span style="color: var(--text-secondary-light); font-size: 13px;">/{{ $item['mengajar_max'] }}</span>
          </div>
        </div>
        <div class="aspect-progress-track" role="progressbar" aria-valuenow="{{ $mengajarPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Mengajar {{ number_format($mengajarPct, 1) }} persen">
          <div class="aspect-progress-bar" style="width: {{ $mengajarPct }}%;"></div>
        </div>
        <div class="text-center fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">
          {{ number_format($mengajarPct, 1) }}%
        </div>
      </div>
    </div>

    {{-- Aspek 3: Daily Report --}}
    <div class="col-xl-3 col-md-6 col-12">
      <div class="aspect-card">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="aspect-icon-wrap"><i class="ri-file-text-line" aria-hidden="true"></i></span>
            <span class="fw-semibold" style="color: var(--text-primary-light); font-size: 15px;">Daily Report</span>
          </div>
          <div>
            <span class="fw-bold fs-5" style="color: var(--text-primary-light);">{{ $item['daily_report_score'] }}</span>
            <span style="color: var(--text-secondary-light); font-size: 13px;">/{{ $item['daily_report_max'] }}</span>
          </div>
        </div>
        <div class="aspect-progress-track" role="progressbar" aria-valuenow="{{ $dailyReportPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Daily report {{ number_format($dailyReportPct, 1) }} persen">
          <div class="aspect-progress-bar" style="width: {{ $dailyReportPct }}%;"></div>
        </div>
        <div class="text-center fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">
          {{ number_format($dailyReportPct, 1) }}%
        </div>
      </div>
    </div>

    {{-- Aspek 4: Teamwork --}}
    <div class="col-xl-3 col-md-6 col-12">
      <div class="aspect-card">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="aspect-icon-wrap"><i class="ri-group-line" aria-hidden="true"></i></span>
            <span class="fw-semibold" style="color: var(--text-primary-light); font-size: 15px;">Teamwork</span>
          </div>
          <div>
            <span class="fw-bold fs-5" style="color: var(--text-primary-light);">{{ $item['teamwork_score'] }}</span>
            <span style="color: var(--text-secondary-light); font-size: 13px;">/{{ $item['teamwork_max'] }}</span>
          </div>
        </div>
        <div class="aspect-progress-track" role="progressbar" aria-valuenow="{{ $teamworkPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Teamwork {{ number_format($teamworkPct, 1) }} persen">
          <div class="aspect-progress-bar" style="width: {{ $teamworkPct }}%;"></div>
        </div>
        <div class="text-center fw-semibold" style="color: var(--text-secondary-light); font-size: 14px;">
          {{ number_format($teamworkPct, 1) }}%
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Section: Catatan & Keterangan --}}
<div class="kpi-notes-card">
  <div class="d-flex align-items-center gap-2 mb-8 fw-semibold" style="color: #4F46E5; font-size: 15px;">
    <i class="ri-chat-1-line" aria-hidden="true"></i>
    <span>Catatan & Keterangan</span>
  </div>
  <p class="mb-0">
    {{ $item['catatan'] }}
  </p>
</div>
@endsection
