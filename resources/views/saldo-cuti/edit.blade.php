@extends('layouts.main')

@section('title', 'Edit Saldo Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Edit Saldo Cuti</h6>
        <p class="text-neutral-600 mt-4 mb-0">Saldo Cuti &raquo; Edit</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('saldo-cuti.update', \App\Helpers\IdEncryptor::encrypt($saldoCuti->id)) }}"
          method="POST" id="form-saldo-cuti">
      @csrf
      @method('PUT')
      @include('saldo-cuti.partials.form', ['saldoCuti' => $saldoCuti])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('saldo-cuti.index') }}" class="btn btn-outline-neutral-600">
          <i class="ri-arrow-left-line"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary-600 btn-submit">
          <span class="btn-text">Simpan Perubahan</span>
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/flatpickr.js') }}"></script>
<script>
$(document).ready(function () {
    var overlapDetected = false;
    var overlapTimer    = null;
    var excludeId       = '{{ \App\Helpers\IdEncryptor::encrypt($saldoCuti->id) }}';

    // ── Submit guard ────────────────────────────────────────────
    $('#form-saldo-cuti').on('submit', function (e) {
        if (overlapDetected) {
            e.preventDefault();
            $('#overlap-warning').removeClass('d-none');
            $('html, body').animate({ scrollTop: $('#overlap-warning').offset().top - 120 }, 350);
            return false;
        }
        var btn = $(this).find('.btn-submit');
        btn.prop('disabled', true);
        btn.find('.btn-text').text('Menyimpan...');
        btn.find('.spinner-border').removeClass('d-none');
    });

    // ── Flatpickr ────────────────────────────────────────────────
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#datepicker-mulai', {
            dateFormat: 'd/m/Y', wrap: true, allowInput: false,
            disableMobile: true, locale: { firstDayOfWeek: 1 },
            onChange: function () { updateDurasiInfo(); scheduleOverlapCheck(); }
        });
        flatpickr('#datepicker-selesai', {
            dateFormat: 'd/m/Y', wrap: true, allowInput: false,
            disableMobile: true, locale: { firstDayOfWeek: 1 },
            onChange: function () { updateDurasiInfo(); scheduleOverlapCheck(); }
        });
    }

    // ── Karyawan change ──────────────────────────────────────────
    $('#karyawan_id').on('change', function () { scheduleOverlapCheck(); });

    // ── Durasi info (teks saja, tidak show/hide kartu) ───────────
    function updateDurasiInfo() {
        var mulai   = $('#periode_mulai').val();
        var selesai = $('#periode_selesai').val();
        if (mulai && selesai) {
            var p1 = mulai.split('/'), p2 = selesai.split('/');
            var d1 = new Date(p1[2], p1[1]-1, p1[0]);
            var d2 = new Date(p2[2], p2[1]-1, p2[0]);
            if (d2 > d1) {
                var days = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24));
                $('#durasi-periode').text(days + ' hari (' + mulai + ' s.d. ' + selesai + ')');
            }
        }
    }

    // ── Overlap check (debounced 450ms) ──────────────────────────
    function scheduleOverlapCheck() {
        clearTimeout(overlapTimer);
        $('#periode-info').addClass('d-none');
        $('#overlap-warning').addClass('d-none');
        overlapDetected = false;
        overlapTimer = setTimeout(doOverlapCheck, 450);
    }

    function doOverlapCheck() {
        var karyawanId  = $('#karyawan_id').val();
        var mulai       = $('#periode_mulai').val();
        var selesai     = $('#periode_selesai').val();

        if (! mulai || ! selesai) {
            $('#periode-info').addClass('d-none');
            $('#overlap-warning').addClass('d-none');
            return;
        }

        // Tanggal valid tapi karyawan belum dipilih — tampilkan durasi saja
        if (! karyawanId) {
            updateDurasiInfo();
            $('#overlap-warning').addClass('d-none');
            $('#periode-info').removeClass('d-none');
            overlapDetected = false;
            return;
        }

        $.ajax({
            url: '{{ route("saldo-cuti.check-overlap") }}',
            method: 'GET',
            data: { karyawan_id: karyawanId, periode_mulai: mulai, periode_selesai: selesai, exclude_id: excludeId },
            success: function (res) {
                if (res.overlap) {
                    $('#overlap-message').text(res.message);
                    $('#periode-info').addClass('d-none');
                    $('#overlap-warning').removeClass('d-none');
                    overlapDetected = true;
                } else {
                    updateDurasiInfo();
                    $('#overlap-warning').addClass('d-none');
                    $('#periode-info').removeClass('d-none');
                    overlapDetected = false;
                }
            },
            error: function () {
                updateDurasiInfo();
                $('#overlap-warning').addClass('d-none');
                $('#periode-info').removeClass('d-none');
                overlapDetected = false;
            }
        });
    }

    // ── Populate durasi on load, then run initial overlap check ──
    updateDurasiInfo();
    doOverlapCheck();

    // ── Scroll to server-side validation errors on load ──────────
    var validationAlert = document.getElementById('validation-alert');
    if (validationAlert) {
        validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
@endpush

@push('styles')
@include('saldo-cuti.partials.datepicker-styles')
@endpush
