@extends('layouts.main')
@section('title', 'Dashboard Karyawan')

@section('content')
<div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <div>
    <h6 class="fw-semibold mb-0">Dashboard Karyawan</h6>
    <p class="text-neutral-600 mt-4 mb-0">Pantau pengajuan, aktivitas, dan informasi terbaru Anda dalam satu halaman.</p>
  </div>
</div>
@include('partials.alert')

<div class="mt-24">
  <div class="row gy-4">
    @foreach([['Cuti Menunggu', $cutiMenunggu, 'gradient-bg-end-12', 'bg-warning-600', 'Menunggu persetujuan'], ['FIMP Menunggu', $fimpMenunggu, 'gradient-bg-end-13', 'bg-blue-600', 'Izin meninggalkan pekerjaan'], ['Lembur Menunggu', $lemburMenunggu, 'gradient-bg-end-14', 'bg-purple-600', 'Permintaan lembur kerja'], ['Pengumuman Aktif', $jumlahPengumuman, 'gradient-bg-end-15', 'bg-primary-600', 'Informasi terbaru perusahaan']] as [$label, $value, $gradient, $color, $description])
    <div class="col-xxl-3 col-sm-6">
      <div class="card p-3 shadow-2 radius-8 h-100 border-0 {{ $gradient }}">
        <div class="card-body p-0">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-1">
            <div class="d-flex align-items-center gap-3"><span class="mb-0 w-48-px h-48-px {{ $color }} flex-shrink-0 text-white d-flex justify-content-center align-items-center rounded-circle h6"><img src="{{ asset('assets/images/icons/parent-widget-icon1.png') }}" alt="{{ $label }}"></span>
              <div><span class="fw-medium text-primary-light text-md">{{ $label }}</span></div>
            </div>
          </div>
          <div class="mt-16">
            <h6 class="fw-semibold mb-0">{{ $value }}</h6>
            <p class="text-sm mb-0 mt-1 text-secondary-light">{{ $description }}</p>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <div class="mt-16">
    <div class="row gy-4">
      <div class="col-xxl-8">
        <div class="row gy-4">
          <div class="col-12">
            <div class="card radius-12 border-0 h-100">
              <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between py-12 px-20 border-bottom border-neutral-200">
                <h6 class="mb-2 fw-bold text-lg">Statistik Aktivitas</h6><span class="form-select bg-base form-select-sm w-auto radius-8">Tahun {{ $tahunDashboard }}</span>
              </div>
              <div class="card-body py-20 d-flex flex-column justify-content-center">
                <ul class="d-flex flex-wrap align-items-center justify-content-center mb-20 gap-3">
                  <li class="d-flex align-items-center gap-2"><span class="w-12-px h-12-px rounded-circle bg-warning-600"></span><span class="text-secondary-light text-sm">Cuti Disetujui: <span class="text-primary-light text-xl fw-bold line-height-1 ms-4">{{ $cutiDisetujui }}</span></span></li>
                  <li class="d-flex align-items-center gap-2"><span class="w-12-px h-12-px rounded-circle bg-primary-600"></span><span class="text-secondary-light text-sm">Total Pengajuan: <span class="text-primary-light text-md fw-bold line-height-1 ms-4">{{ $totalPengajuan }}</span></span></li>
                </ul>
                <div id="courseActivityChart" class="apexcharts-tooltip-style-1"></div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card radius-12 border-0 h-100">
              <div class="d-flex align-items-center flex-wrap gap-2 justify-content-between py-12 px-20 border-bottom border-neutral-200">
                <h6 class="mb-2 fw-bold text-lg">Pengajuan Cuti</h6><a href="{{ route('leave-requests.index') }}" class="text-primary-600 text-sm fw-semibold">Lihat Semua</a>
              </div>
              <div class="card-body pe-0 py-8">
                <div class="d-flex flex-column max-h-390-px overflow-y-auto scroll-sm pe-20">@forelse($pengajuanCuti as $cuti)<div class="d-flex align-items-center justify-content-between gap-3 py-10 border-bottom">
                    <div class="flex-grow-1">
                      <h6 class="text-lg mb-4 fw-medium">{{ $cuti->kategoriCuti?->nama_kategori ?? 'Cuti Umum' }}</h6>
                      <div class="d-flex align-items-center gap-8"><i class="ri-calendar-line"></i><span class="text-sm text-secondary-light fw-medium">{{ $cuti->tanggal_awal?->format('d M Y') }} - {{ $cuti->tanggal_akhir?->format('d M Y') }}</span></div>
                    </div>
                    <div>@if($cuti->status_pengajuan === 'Approve')<span class="bg-success-100 text-success-600 px-12 py-4 radius-4 fw-medium text-sm">Disetujui</span>@elseif($cuti->status_pengajuan === 'Reject')<span class="bg-danger-100 text-danger-600 px-12 py-4 radius-4 fw-medium text-sm">Ditolak</span>@else<span class="bg-warning-100 text-warning-600 px-12 py-4 radius-4 fw-medium text-sm">Menunggu</span>@endif</div>
                  </div>@empty<div class="text-center py-32"><i class="ri-file-list-3-line text-3xl text-secondary-light"></i>
                    <p class="text-secondary-light text-sm mt-12">Belum ada pengajuan cuti.</p><a href="{{ route('leave-requests.create') }}" class="btn btn-sm btn-primary-600">Ajukan Cuti</a>
                  </div>@endforelse</div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-body p-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between px-20 py-16 border-bottom border-neutral-200">
                  <h6 class="text-lg mb-0">Papan Pengumuman</h6><a href="{{ route('announcements.index') }}" class="text-primary-600 text-sm fw-semibold">Lihat Semua</a>
                </div>
                <div class="ps-20 pt-20 pb-20">
                  <div class="pe-20 d-flex flex-column gap-28 overflow-y-auto max-h-390-px scroll-sm">@forelse($pengumuman as $index => $item)<div class="d-flex align-items-start gap-16"><img src="{{ asset('assets/images/thumbs/notice-board-img' . (($index % 3) + 1) . '.png') }}" alt="Pengumuman" class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0">
                      <div>
                        <h6 class="mb-4 text-lg">{{ $item->title }}</h6>
                        <p class="text-secondary-light text-sm mb-0">{{ \Illuminate\Support\Str::limit($item->content, 110) }}</p><span class="text-secondary-light text-sm mb-0 mt-4 d-block">{{ $item->published_at?->format('d M Y') }}</span>
                      </div>
                    </div>@empty<div class="text-center py-32 pe-20"><i class="ri-megaphone-line text-3xl text-secondary-light"></i>
                      <p class="text-secondary-light text-sm mt-12 mb-0">Belum ada pengumuman aktif.</p>
                    </div>@endforelse</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-xxl-4">
        <div class="card radius-12 border-0 h-100">
          <div class="card-body p-20">
            <h6 class="fw-bold text-lg mb-20">Akses Cepat</h6>
            <div class="d-grid gap-12"><a href="{{ route('leave-requests.create') }}" class="btn btn-primary-600 text-start"><i class="ri-add-line"></i> Ajukan Cuti</a><a href="{{ route('fimp.create') }}" class="btn btn-outline-primary-600 text-start"><i class="ri-file-text-line"></i> Buat FIMP</a><a href="{{ route('overtime-requests.create') }}" class="btn btn-outline-warning-600 text-start"><i class="ri-time-line"></i> Ajukan Lembur</a><a href="{{ route('personal-data.edit') }}" class="btn btn-outline-neutral-600 text-start"><i class="ri-user-3-line"></i> Data Pribadi</a></div>
            <div class="mt-24 pt-20 border-top">
              <p class="text-secondary-light text-sm mb-8">FIMP Disetujui</p>
              <h4 class="mb-16">{{ $fimpDisetujui }}</h4>
              <p class="text-secondary-light text-sm mb-8">Lembur Disetujui</p>
              <h4 class="mb-0">{{ $lemburDisetujui }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const chart = document.querySelector('#courseActivityChart');
    if (!chart || typeof ApexCharts === 'undefined') return;
    new ApexCharts(chart, {
      series: [{
        name: 'Pengajuan',
        data: @json($activitySeries)
      }],
      chart: {
        type: 'area',
        height: 295,
        toolbar: {
          show: false
        },
        fontFamily: 'inherit'
      },
      colors: ['#487FFF'],
      stroke: {
        curve: 'smooth',
        width: 3
      },
      fill: {
        type: 'gradient',
        gradient: {
          opacityFrom: .35,
          opacityTo: .02
        }
      },
      dataLabels: {
        enabled: false
      },
      markers: {
        size: 0
      },
      xaxis: {
        categories: @json($activityMonths),
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      grid: {
        borderColor: '#e9eef5',
        strokeDashArray: 3
      },
      yaxis: {
        min: 0,
        forceNiceScale: true
      },
      tooltip: {
        y: {
          formatter: value => value + ' pengajuan'
        }
      }
    }).render();
  });
</script>
@endpush