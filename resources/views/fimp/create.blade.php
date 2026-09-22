@extends('layouts.main')

@section('title', 'Tambah Pengajuan FIMP')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Tambah Pengajuan FIMP</h6>
        <p class="text-neutral-600 mt-4 mb-0">Pengajuan FIMP &raquo; Tambah</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('pengajuan-fimp.store') }}" method="POST" id="form-fimp">
      @csrf
      @include('fimp.partials.form', ['fimp' => null])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('pengajuan-fimp.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
        <button type="submit" class="btn btn-primary-600 btn-submit">
          <span class="btn-text">Simpan Pengajuan</span>
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/flatpickr.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#form-fimp').on('submit', function() {
            var btn = $(this).find('.btn-submit');
            btn.prop('disabled', true);
            btn.find('.btn-text').text('Menyimpan...');
            btn.find('.spinner-border').removeClass('d-none');
        });

        if (typeof flatpickr !== 'undefined') {
            flatpickr('#datepicker-awal', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 },
                onChange: function() { hitungTotalHari(); }
            });
            flatpickr('#datepicker-akhir', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 },
                onChange: function() { hitungTotalHari(); }
            });
        }

        function hitungTotalHari() {
            var awal = $('#tanggal_awal').val();
            var akhir = $('#tanggal_akhir').val();
            if (awal && akhir) {
                var parts1 = awal.split('/');
                var parts2 = akhir.split('/');
                var d1 = new Date(parts1[2], parts1[1]-1, parts1[0]);
                var d2 = new Date(parts2[2], parts2[1]-1, parts2[0]);
                if (d2 >= d1) {
                    var diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                    $('#total_hari').val(diff + ' hari');
                } else {
                    $('#total_hari').val('');
                }
            } else {
                $('#total_hari').val('');
            }
        }

        var validationAlert = document.getElementById('validation-alert');
        if (validationAlert) {
            validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
@endpush

@push('styles')
<style>
    .datepicker-wrapper {
        cursor: pointer;
    }
    .datepicker-wrapper input[readonly] {
        background-color: #fff;
        cursor: pointer;
    }
    .datepicker-wrapper .datepicker-icon {
        pointer-events: none;
        z-index: 2;
        line-height: 1;
    }
    .flatpickr-calendar {
        border-radius: 12px !important;
        box-shadow: 0 8px 32px rgba(0,0,0,.12) !important;
        border: 1px solid rgba(0,0,0,.06) !important;
        font-family: inherit !important;
        padding: 0 !important;
        overflow: hidden;
    }
    .flatpickr-months {
        background: linear-gradient(135deg, #487fff 0%, #3b6de0 100%);
        border-radius: 12px 12px 0 0;
        padding: 8px 4px;
    }
    .flatpickr-months .flatpickr-month {
        height: 40px;
    }
    .flatpickr-current-month {
        color: #fff !important;
        font-weight: 600 !important;
        font-size: .95rem !important;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: transparent !important;
        color: #fff !important;
        font-weight: 600 !important;
        -webkit-appearance: none;
        appearance: none;
    }
    .flatpickr-current-month input.cur-year {
        color: #fff !important;
        font-weight: 600 !important;
    }
    .flatpickr-weekdays {
        background: #f8fafc !important;
        padding: 4px 0;
    }
    .flatpickr-weekday {
        color: #64748b !important;
        font-weight: 600 !important;
        font-size: .75rem !important;
        text-transform: uppercase;
    }
    .flatpickr-day {
        border-radius: 8px !important;
        font-size: .8125rem !important;
        font-weight: 500 !important;
        color: #334155 !important;
        transition: all .15s ease !important;
    }
    .flatpickr-day:hover {
        background: #EFF6FF !important;
        border-color: #EFF6FF !important;
        color: #487fff !important;
    }
    .flatpickr-day.selected {
        background: #487fff !important;
        border-color: #487fff !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(72,127,255,.35);
    }
    .flatpickr-day.today {
        border-color: #487fff !important;
        color: #487fff !important;
        font-weight: 700 !important;
    }
    .flatpickr-day.today.selected {
        color: #fff !important;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        color: #fff !important;
        fill: #fff !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: rgba(255,255,255,.7) !important;
    }
</style>
@endpush
