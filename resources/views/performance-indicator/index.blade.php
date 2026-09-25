@extends('layouts.main')

@section('title', 'Performance Indicator')

@push('styles')
<style>
  /* Design tokens — pakai variable template agar otomatis ikut dark mode */
  .kpi-card {
    background-color: var(--white);
    border: 1px solid var(--neutral-200);
    border-radius: 16px;
    padding: 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .kpi-card:hover {
    transform: translateY(-3px);
    border-color: var(--neutral-300);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
  }
  [data-theme="dark"] .kpi-card {
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.35);
  }

  .kpi-score-badge {
    border-radius: 12px;
    padding: 12px 20px;
    text-align: center;
    color: #FFFFFF;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.2;
    margin: 12px 0 14px 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }
  .kpi-badge-purple {
    background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
  }
  /* Mint digelapkan agar kontras putih ≥4.5:1 untuk staff umum */
  .kpi-badge-mint {
    background: linear-gradient(135deg, #059669 0%, #0E7490 100%);
  }

  .kpi-card-label {
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary-light);
  }

  .kpi-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
  }
  .kpi-table th {
    background-color: var(--neutral-50);
    color: var(--text-secondary-light);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 14px 18px;
    border-bottom: 1px solid var(--neutral-200);
    white-space: nowrap;
  }
  .kpi-table td {
    padding: 16px 18px;
    font-size: 14px;
    color: var(--text-primary-light);
    border-bottom: 1px solid var(--neutral-200);
    vertical-align: middle;
  }
  .kpi-table tbody tr:hover td {
    background-color: var(--neutral-50);
  }
  .kpi-table tr:last-child td {
    border-bottom: none;
  }

  /* Tombol aksi 44px — mudah disentuh, ada label teks untuk non-teknis */
  .btn-kpi-view {
    min-height: 44px;
    min-width: 44px;
    padding: 10px 16px;
    border-radius: 10px;
    background-color: #4338CA;
    color: #FFFFFF !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    text-decoration: none;
  }
  .btn-kpi-view:hover {
    background-color: #3730A3;
    box-shadow: 0 4px 12px rgba(67, 56, 202, 0.4);
  }
  .btn-kpi-view:focus-visible,
  .kpi-card:focus-within .btn-kpi-view:focus-visible {
    outline: 3px solid #A5B4FC;
    outline-offset: 2px;
  }
  @media (max-width: 575.98px) {
    .kpi-score-badge { font-size: 24px; }
    .kpi-table td { font-size: 13px; padding: 12px 14px; }
  }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <div class="d-flex align-items-center gap-3">
    <div class="w-40-px h-40-px bg-primary-50 text-primary-600 rounded-8 d-flex align-items-center justify-content-center flex-shrink-0" aria-hidden="true">
      <i class="ri-line-chart-line text-xl"></i>
    </div>
    <div>
      <h5 class="fw-bold mb-1" style="color: var(--text-primary-light); font-size: 20px;">Performance Indicator</h5>
      <p class="mb-0" style="color: var(--text-secondary-light); font-size: 14px;">Riwayat penilaian Key Performance Indicator</p>
    </div>
  </div>
  <div class="d-flex align-items-center gap-2">
    <span class="badge radius-8 px-14 py-8 d-inline-flex align-items-center gap-2 fw-medium shadow-none" style="background-color: var(--white); color: var(--text-secondary-light); border: 1px solid var(--neutral-200); font-size: 14px;">
      <i class="ri-calendar-line" aria-hidden="true"></i> {{ now()->translatedFormat('d M Y') }}
    </span>
  </div>
</div>

{{-- Top 4 KPI Summary Cards --}}
<div class="row g-4 mb-28">

  {{-- Card 1: Latest Performance --}}
  <div class="col-xl-3 col-md-6 col-12">
    <div class="kpi-card text-center">
      <div>
        <span class="kpi-card-label">Latest Performance</span>
        <div class="kpi-score-badge kpi-badge-purple" role="status" aria-label="Skor terbaru {{ number_format($latestScore, 1) }} dari 100">
          {{ number_format($latestScore, 1) }}<span class="fs-6 fw-semibold">/100</span>
        </div>
        <h6 class="fw-bold mb-4" style="color: var(--text-primary-light); font-size: 16px;">{{ $latestStatus }}</h6>
      </div>
      <div class="d-flex align-items-center justify-content-center gap-1" style="color: var(--text-secondary-light); font-size: 14px;">
        <i class="ri-calendar-line" aria-hidden="true"></i> {{ $latestPeriod }}
      </div>
    </div>
  </div>

  {{-- Card 2: Average Score --}}
  <div class="col-xl-3 col-md-6 col-12">
    <div class="kpi-card text-center">
      <div>
        <span class="kpi-card-label">Average Score</span>
        <div class="kpi-score-badge kpi-badge-mint" role="status" aria-label="Rata-rata skor {{ number_format($averageScore, 1) }} dari 100">
          {{ number_format($averageScore, 1) }}<span class="fs-6 fw-semibold">/100</span>
        </div>
        <h6 class="fw-bold mb-4" style="color: var(--text-primary-light); font-size: 16px;">Overall Performance</h6>
      </div>
      <div style="color: var(--text-secondary-light); font-size: 14px;">
        Dari {{ $totalEvaluations }} periode penilaian
      </div>
    </div>
  </div>

  {{-- Card 3: Best Score --}}
  <div class="col-xl-3 col-md-6 col-12">
    <div class="kpi-card text-center">
      <div>
        <span class="kpi-card-label">Best Score</span>
        <div class="kpi-score-badge kpi-badge-purple" role="status" aria-label="Skor terbaik {{ number_format($bestScore, 1) }} dari 100">
          {{ number_format($bestScore, 1) }}<span class="fs-6 fw-semibold">/100</span>
        </div>
        <h6 class="fw-bold mb-4" style="color: var(--text-primary-light); font-size: 16px;">Peak Performance</h6>
      </div>
      <div style="color: var(--text-secondary-light); font-size: 14px;">
        Pencapaian tertinggi Anda
      </div>
    </div>
  </div>

  {{-- Card 4: Lowest Score --}}
  <div class="col-xl-3 col-md-6 col-12">
    <div class="kpi-card text-center">
      <div>
        <span class="kpi-card-label">Lowest Score</span>
        <div class="kpi-score-badge kpi-badge-mint" role="status" aria-label="Skor terendah {{ number_format($lowestScore, 1) }} dari 100">
          {{ number_format($lowestScore, 1) }}<span class="fs-6 fw-semibold">/100</span>
        </div>
        <h6 class="fw-bold mb-4" style="color: var(--text-primary-light);">Needs Focus</h6>
      </div>
      <div class="text-sm fw-semibold d-flex align-items-center justify-content-center gap-1" style="color: var(--danger-600);">
        <i class="ri-arrow-down-line" aria-hidden="true"></i> {{ $diffFromBest }} poin dari best
      </div>
    </div>
  </div>

</div>

{{-- Riwayat Penilaian KPI Table --}}
<div class="radius-16 overflow-hidden shadow-none mb-28" style="background-color: var(--white); border: 1px solid var(--neutral-200);">
  <div class="py-18 px-24 d-flex flex-wrap align-items-center justify-content-between gap-3" style="border-bottom: 1px solid var(--neutral-200);">
    <div class="d-flex align-items-center gap-2">
      <i class="ri-file-text-fill text-primary-600 text-lg" aria-hidden="true"></i>
      <h6 class="fw-bold mb-0" style="color: var(--text-primary-light);">Riwayat Penilaian KPI</h6>
    </div>
    <div>
      <span class="badge radius-pill px-14 py-6 text-sm fw-medium" style="background-color: var(--neutral-50); color: var(--text-secondary-light); border: 1px solid var(--neutral-300);">
        Total: {{ $evaluations->count() }} penilaian
      </span>
    </div>
  </div>

  <div class="p-0">
    <div class="table-responsive">
      <table class="kpi-table">
        <thead>
          <tr>
            <th scope="col" style="width: 130px;">Periode</th>
            <th scope="col">Status</th>
            <th scope="col">Kedisiplinan</th>
            <th scope="col">Mengajar</th>
            <th scope="col">Daily Report</th>
            <th scope="col">Teamwork</th>
            <th scope="col">Total Score</th>
            <th scope="col" style="min-width: 140px;" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($evaluations as $item)
            @php
              $badgeBg = match($item['status_badge'] ?? '') {
                'Excellent' => 'background-color: rgba(16,185,129,.14); color: #059669; border: 1px solid rgba(16,185,129,.35);',
                'Very Good' => 'background-color: rgba(59,130,246,.12); color: #2563EB; border: 1px solid rgba(59,130,246,.35);',
                'Needs Focus' => 'background-color: rgba(220,38,38,.1); color: #DC2626; border: 1px solid rgba(220,38,38,.3);',
                default => 'background-color: rgba(245,158,11,.14); color: #B45309; border: 1px solid rgba(245,158,11,.35);',
              };
            @endphp
            <tr>
              <td class="fw-semibold" style="color: var(--text-primary-light);">{{ $item['periode_kode'] }}</td>
              <td><span class="badge radius-pill px-12 py-4 text-xs fw-semibold" style="{{ $badgeBg }}">{{ $item['status_badge'] }}</span></td>
              <td>{{ $item['kedisiplinan_score'] }}/{{ $item['kedisiplinan_max'] }}</td>
              <td>{{ $item['mengajar_score'] }}/{{ $item['mengajar_max'] }}</td>
              <td>{{ $item['daily_report_score'] }}/{{ $item['daily_report_max'] }}</td>
              <td>{{ $item['teamwork_score'] }}/{{ $item['teamwork_max'] }}</td>
              <td class="fw-bold" style="color: var(--text-primary-light);">{{ $item['total_score'] }}/{{ $item['total_max'] }}</td>
              <td class="text-center">
                <a href="{{ route('performance-indicator.show', $item['id']) }}" class="btn-kpi-view" aria-label="Lihat detail penilaian {{ $item['periode_label'] }}">
                  <i class="ri-eye-line" aria-hidden="true"></i><span>Detail</span>
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center py-32">Belum ada data penilaian.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
