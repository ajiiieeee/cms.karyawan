@extends('layouts.main')

@section('title', 'Tambah Penjadwalan Kursus')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Tambah Penjadwalan Kursus</h6>
        <p class="text-neutral-600 mt-4 mb-0">Penjadwalan &raquo; Tambah</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('penjadwalan.store') }}" method="POST" enctype="multipart/form-data" id="form-penjadwalan">
      @csrf
      @include('penjadwalan.partials.form', ['penjadwalan' => null])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('penjadwalan.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
        <button type="submit" class="btn btn-primary-600 btn-submit">
          <span class="btn-text">Simpan Data</span>
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/flatpickr.js') }}"></script>
<script>
$(document).ready(function() {

    // Initialize Flatpickr date pickers
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#datepicker-mulai', {
            dateFormat: 'd/m/Y',
            wrap: true,
            allowInput: false,
            disableMobile: true,
            locale: { firstDayOfWeek: 1 }
        });

        flatpickr('#datepicker-selesai', {
            dateFormat: 'd/m/Y',
            wrap: true,
            allowInput: false,
            disableMobile: true,
            locale: { firstDayOfWeek: 1 }
        });
    }

    // Form submit handler - disable button and show spinner
    $('#form-penjadwalan').on('submit', function() {
        var btn = $(this).find('.btn-submit');
        btn.prop('disabled', true);
        btn.find('.btn-text').text('Menyimpan...');
        btn.find('.spinner-border').removeClass('d-none');
    });

    // Scroll to validation errors
    var validationAlert = document.getElementById('validation-alert');
    if (validationAlert) {
        validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endpush

@push('styles')
<style>
    /* Datepicker */
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
    }
    .flatpickr-day:hover {
        background: #EFF6FF !important;
        border-color: transparent !important;
        color: #487fff !important;
    }
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
        background: #487fff !important;
        border-color: #487fff !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(72,127,255,.3);
    }
    .flatpickr-day.today {
        border-color: #487fff !important;
        color: #487fff !important;
        background: transparent !important;
    }
    .flatpickr-day.today.selected {
        color: #fff !important;
    }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        fill: #fff !important;
        color: #fff !important;
        padding: 8px !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: rgba(255,255,255,.7) !important;
    }
    .flatpickr-innerContainer {
        padding: 8px !important;
    }

    /* Select2 container full width */
    .select2-container {
        width: 100% !important;
    }

    /* Select2 custom */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        min-height: 40px;
        font-size: .875rem;
        display: flex;
        align-items: center;
    }
    .select2-container--bootstrap-5 .select2-selection--single {
        padding-right: 36px;
        position: relative;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding: 6px 12px;
        color: #334155;
        line-height: 1.5;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 28px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 1.125rem;
        font-weight: 400;
        color: #94a3b8;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f1f5f9;
        transition: all .15s;
        cursor: pointer;
        z-index: 2;
        line-height: 1;
        padding: 0;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear:hover {
        background: #e2e8f0;
        color: #ef4444;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.1);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: #487fff;
        color: #fff;
    }
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border-radius: 8px;
        border: 1.5px solid #e2e8f0;
        padding: 8px 12px;
        font-size: .8125rem;
    }
    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #487fff;
        box-shadow: 0 0 0 3px rgba(72,127,255,.1);
    }
    .select2-container--bootstrap-5 .select2-results__option {
        padding: 10px 14px;
        font-size: .8125rem;
    }
    .select2-container--bootstrap-5 .select2-results {
        max-height: 280px;
    }
</style>
@endpush
