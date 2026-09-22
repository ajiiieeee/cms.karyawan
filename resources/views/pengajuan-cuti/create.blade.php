@extends('layouts.main')

@section('title', 'Tambah Pengajuan Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Tambah Pengajuan Cuti</h6>
        <p class="text-neutral-600 mt-4 mb-0">Pengajuan Cuti &raquo; Tambah</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('pengajuan-cuti.store') }}" method="POST" id="form-pengajuan-cuti">
      @csrf
      @include('pengajuan-cuti.partials.form', ['pengajuanCuti' => null])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('pengajuan-cuti.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
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
        var saldoCheckTimer = null;
        var saldoQuotaExceeded = false;
        var checkSaldoUrl = '{{ route("pengajuan-cuti.check-saldo") }}';

        // ── Submit: block if quota exceeded ──────────────────────────────
        $('#form-pengajuan-cuti').on('submit', function(e) {
            if (saldoQuotaExceeded) {
                e.preventDefault();
                $('#saldo-exceeded').removeClass('d-none');
                $('#saldo-exceeded')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            var btn = $(this).find('.btn-submit');
            btn.prop('disabled', true);
            btn.find('.btn-text').text('Menyimpan...');
            btn.find('.spinner-border').removeClass('d-none');
        });

        // ── Flatpickr ─────────────────────────────────────────────────────
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#datepicker-awal', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 },
                onChange: function() { hitungJumlahHari(); }
            });
            flatpickr('#datepicker-akhir', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 },
                onChange: function() { hitungJumlahHari(); }
            });
        }

        // ── Hitung jumlah hari + trigger saldo check ──────────────────────
        function hitungJumlahHari() {
            var awal = $('#tanggal_awal').val();
            var akhir = $('#tanggal_akhir').val();
            if (awal && akhir) {
                var parts1 = awal.split('/');
                var parts2 = akhir.split('/');
                var d1 = new Date(parts1[2], parts1[1]-1, parts1[0]);
                var d2 = new Date(parts2[2], parts2[1]-1, parts2[0]);
                if (d2 >= d1) {
                    var diff = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
                    $('#jumlah_hari').val(diff + ' hari');
                } else {
                    $('#jumlah_hari').val('');
                }
            } else {
                $('#jumlah_hari').val('');
            }
            scheduleSaldoCheck();
        }

        // ── Saldo check ───────────────────────────────────────────────────
        function scheduleSaldoCheck() {
            $('#saldo-available, #saldo-exceeded, #saldo-not-found').addClass('d-none');
            saldoQuotaExceeded = false;
            clearTimeout(saldoCheckTimer);
            saldoCheckTimer = setTimeout(doSaldoCheck, 400);
        }

        function doSaldoCheck() {
            var karyawanId = $('#karyawan_id').val();
            var jenisCuti  = $('#jenis_cuti').val();
            var tglAwal    = $('#tanggal_awal').val();
            var tglAkhir   = $('#tanggal_akhir').val();

            if (!karyawanId || !jenisCuti || !tglAwal || !tglAkhir) {
                return;
            }

            $.ajax({
                url: checkSaldoUrl,
                type: 'GET',
                data: {
                    karyawan_id:  karyawanId,
                    jenis_cuti:   jenisCuti,
                    tanggal_awal: tglAwal,
                    tanggal_akhir: tglAkhir,
                },
                success: function(res) {
                    $('#saldo-available, #saldo-exceeded, #saldo-not-found').addClass('d-none');
                    saldoQuotaExceeded = false;

                    if (res.type === 'available') {
                        if (res.exceeded) {
                            $('#saldo-sisa-exc').text(res.saldo_sisa);
                            $('#saldo-hari-exc').text(res.jumlah_hari);
                            $('#saldo-exceeded').removeClass('d-none');
                            saldoQuotaExceeded = true;
                        } else {
                            $('#saldo-sisa-val').text(res.saldo_sisa);
                            $('#saldo-terpakai-val').text(res.saldo_terpakai);
                            $('#saldo-total-val').text(res.total_cuti);
                            $('#saldo-periode-val').text(res.periode);
                            $('#saldo-available').removeClass('d-none');
                        }
                    } else if (res.type === 'not_found') {
                        $('#saldo-not-found').removeClass('d-none');
                    }
                    // 'not_applicable' / 'incomplete': hide all (already done)
                },
                error: function() { /* silent */ }
            });
        }

        // Wire triggers
        $('#karyawan_id, #jenis_cuti').on('change', function() { scheduleSaldoCheck(); });

        // ── Scroll to validation alert ────────────────────────────────────
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

    /* ===== Saldo quota check cards ===== */
    .saldo-check-card {
        display: flex; align-items: flex-start; gap: 8px;
        padding: 9px 14px; border-radius: 8px;
        font-size: .8125rem; width: 100%;
        background: #EFF6FF; border: 1px solid #BFDBFE; color: #1d4ed8;
    }
    .saldo-check-card i { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
    .saldo-check-card strong { font-weight: 700; }
    .saldo-check-card--danger  { background: #FEF2F2; border-color: #FECACA; color: #dc2626; }
    .saldo-check-card--warning { background: #FFFBEB; border-color: #FDE68A; color: #92400e; }
    [data-theme="dark"] .saldo-check-card         { background: rgba(72,127,255,.1); border-color: rgba(72,127,255,.3); color: #93c5fd; }
    [data-theme="dark"] .saldo-check-card--danger  { background: rgba(220,38,38,.1);  border-color: rgba(220,38,38,.3);  color: #fca5a5; }
    [data-theme="dark"] .saldo-check-card--warning { background: rgba(146,64,14,.1);  border-color: rgba(146,64,14,.3);  color: #fcd34d; }
</style>
@endpush
