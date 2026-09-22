@extends('layouts.main')

@section('title', 'Edit Karyawan')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Edit Karyawan</h6>
        <p class="text-neutral-600 mt-4 mb-0">Data Master &raquo; Karyawan &raquo; Edit</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('karyawan.update', \App\Helpers\IdEncryptor::encrypt($karyawan->id)) }}" method="POST" enctype="multipart/form-data" id="form-karyawan">
      @csrf
      @method('PUT')
      @include('karyawan.partials.form', ['karyawan' => $karyawan])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('karyawan.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
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
    $(document).ready(function() {
        $('#form-karyawan').on('submit', function() {
            var btn = $(this).find('.btn-submit');
            btn.prop('disabled', true);
            btn.find('.btn-text').text('Menyimpan...');
            btn.find('.spinner-border').removeClass('d-none');
        });

        // Toggle password visibility
        const togglePassword = document.querySelector('#toggle-password');
        const toggleConfirmPassword = document.querySelector('#toggle-password-confirmation');

        if (togglePassword && password) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('ri-eye-off-line');
            });
        }

        // Initialize flatpickr date pickers
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#datepicker-lahir', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 }
            });
            flatpickr('#datepicker-masuk', {
                dateFormat: 'd/m/Y',
                wrap: true,
                allowInput: false,
                disableMobile: true,
                locale: { firstDayOfWeek: 1 }
            });
        }
        // Scroll to validation errors
    var validationAlert = document.getElementById('validation-alert');
    if (validationAlert) {
        validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Foto upload handler
        var fotoArea = document.getElementById('foto-upload-area');
        var fotoInput = document.getElementById('foto');
        var fotoPreviewState = document.getElementById('foto-preview-state');
        var fotoEmptyState = document.getElementById('foto-empty-state');
        var fotoPreviewImg = document.getElementById('foto-preview-img');
        var fotoPreviewName = document.getElementById('foto-preview-name');
        var fotoRemoveBtn = document.getElementById('foto-remove-btn');

        fotoArea.addEventListener('click', function(e) {
            if (e.target.closest('#foto-remove-btn')) return;
            fotoInput.click();
        });

        fotoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var file = this.files[0];
                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({ icon: 'warning', title: 'File terlalu besar', text: 'Ukuran foto maksimal 2MB.', confirmButtonColor: '#487fff' });
                    this.value = '';
                    return;
                }
                var reader = new FileReader();
                reader.onload = function(e) {
                    fotoPreviewImg.src = e.target.result;
                    fotoPreviewName.textContent = file.name;
                    fotoPreviewState.classList.remove('d-none');
                    fotoEmptyState.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            }
        });

        fotoRemoveBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            fotoInput.value = '';
            fotoPreviewImg.src = '';
            fotoPreviewName.textContent = '';
            fotoPreviewState.classList.add('d-none');
            fotoEmptyState.classList.remove('d-none');
        });

        // Drag and drop
        ['dragenter', 'dragover'].forEach(function(evt) {
            fotoArea.addEventListener(evt, function(e) { e.preventDefault(); fotoArea.classList.add('foto-drag-over'); });
        });
        ['dragleave', 'drop'].forEach(function(evt) {
            fotoArea.addEventListener(evt, function(e) { e.preventDefault(); fotoArea.classList.remove('foto-drag-over'); });
        });
        fotoArea.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            if (dt.files && dt.files[0]) {
                fotoInput.files = dt.files;
                fotoInput.dispatchEvent(new Event('change'));
            }
        });
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
            max-width: 36px !important;
            height: 36px !important;
            line-height: 36px !important;
            margin: 1px !important;
        }
        .flatpickr-day:hover {
            background: #eff6ff !important;
            border-color: transparent !important;
            color: #487fff !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
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
        .flatpickr-day.today:hover {
            background: #487fff !important;
            color: #fff !important;
        }
        .flatpickr-day.flatpickr-disabled {
            color: #cbd5e1 !important;
        }
        .flatpickr-innerContainer {
            padding: 8px 12px 12px !important;
        }
        [data-theme="dark"] .datepicker-wrapper input[readonly] {
            background-color: transparent;
        }
        [data-theme="dark"] .flatpickr-calendar {
            background: #1e293b !important;
            border-color: rgba(255,255,255,.08) !important;
        }
        [data-theme="dark"] .flatpickr-weekdays {
            background: #162032 !important;
        }
        [data-theme="dark"] .flatpickr-weekday {
            color: #94a3b8 !important;
        }
        [data-theme="dark"] .flatpickr-day {
            color: #e2e8f0 !important;
        }
        [data-theme="dark"] .flatpickr-day:hover {
            background: #334155 !important;
            color: #93b4ff !important;
        }
        [data-theme="dark"] .flatpickr-day.flatpickr-disabled {
            color: #475569 !important;
        }
        @media (max-width: 575.98px) {
            .flatpickr-calendar {
                width: calc(100vw - 32px) !important;
                max-width: 320px !important;
            }
            .flatpickr-day {
                max-width: 32px !important;
                height: 32px !important;
                line-height: 32px !important;
            }
        }
    </style>

    <style>
        /* Foto Upload */
        .foto-upload-area {
            position: relative;
            border: 2px dashed #d0d5dd;
            border-radius: 12px;
            cursor: pointer;
            transition: all .2s ease;
            overflow: hidden;
        }
        .foto-upload-area:hover {
            border-color: #487fff;
            background: #f8faff;
        }
        .foto-upload-area.foto-drag-over {
            border-color: #487fff;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(72,127,255,.12);
        }
        .foto-upload-area.has-error {
            border-color: #dc2626;
            background: #fef2f2;
        }
        .foto-upload-area.has-error:hover {
            border-color: #b91c1c;
            background: #fee2e2;
        }
        .foto-upload-input {
            position: absolute;
            width: 0;
            height: 0;
            opacity: 0;
            pointer-events: none;
        }
        .foto-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 32px 16px;
        }
        .foto-empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .foto-empty-icon i {
            font-size: 24px;
            color: #487fff;
        }
        .foto-empty-text {
            display: flex;
            gap: 4px;
            font-size: .8125rem;
        }
        .foto-preview-state {
            padding: 12px 16px;
        }
        .foto-preview-card {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 16px;
            transition: background .15s ease;
        }
        .foto-preview-card:hover {
            background: #f1f5f9;
        }
        .foto-preview-img {
            width: 64px;
            height: 64px;
            border-radius: 10px;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        .foto-preview-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .foto-preview-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .foto-remove-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all .15s ease;
            font-size: 18px;
        }
        .foto-remove-btn:hover {
            background: #fecaca;
            color: #b91c1c;
            transform: scale(1.05);
        }
        [data-theme="dark"] .foto-upload-area.has-error {
            border-color: #dc2626;
            background: rgba(220,38,38,.08);
        }
        [data-theme="dark"] .foto-upload-area {
            border-color: #334155;
        }
        [data-theme="dark"] .foto-upload-area:hover {
            border-color: #487fff;
            background: rgba(72,127,255,.05);
        }
        [data-theme="dark"] .foto-upload-area.foto-drag-over {
            background: rgba(72,127,255,.08);
        }
        [data-theme="dark"] .foto-empty-icon {
            background: linear-gradient(135deg, rgba(72,127,255,.12) 0%, rgba(72,127,255,.2) 100%);
        }
        [data-theme="dark"] .foto-preview-card {
            background: #1e293b;
        }
        [data-theme="dark"] .foto-preview-card:hover {
            background: #253349;
        }
        [data-theme="dark"] .foto-preview-img {
            border-color: #334155;
        }
        [data-theme="dark"] .foto-remove-btn {
            background: rgba(220,38,38,.15);
            color: #f87171;
        }
        [data-theme="dark"] .foto-remove-btn:hover {
            background: rgba(220,38,38,.25);
            color: #fca5a5;
        }
        @media (max-width: 575.98px) {
            .foto-empty-state { padding: 24px 12px; }
            .foto-preview-img { width: 48px; height: 48px; border-radius: 8px; }
            .foto-preview-card { padding: 10px 12px; gap: 12px; }
        }
    </style>
@endpush
