@extends('layouts.main')

@section('title', 'Edit Pengajuan FIMP')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Edit Pengajuan FIMP</h6>
        <p class="text-neutral-600 mt-4 mb-0">Pengajuan FIMP &raquo; Edit</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('pengajuan-fimp.update', \App\Helpers\IdEncryptor::encrypt($fimp->id)) }}" method="POST" id="form-fimp">
      @csrf
      @method('PUT')

      {{-- Approve / Reject Action --}}
    @php
      $currentUser = auth()->user();
      $isSuperAdmin = $currentUser->grup && strtolower($currentUser->grup->nama_grup) === 'super admin';
      $canApprove = false;
      if ($fimp->status_approval === 'Pending') {
          if ($isSuperAdmin) {
              $canApprove = true;
          } else {
              $userRole = null;
              if ($currentUser->grup) {
                  $grupName = strtolower($currentUser->grup->nama_grup);
                  if (in_array($grupName, ['hrd', 'manager', 'direktur'])) {
                      $userRole = $grupName;
                  }
              }
              if (!$userRole) {
                  $karyawanMatch = \App\Models\Karyawan::where('username', $currentUser->username)->first();
                  $userRole = $karyawanMatch ? $karyawanMatch->jabatan : null;
              }
              if ($userRole === 'hrd' && $fimp->status_pengajuan === 0) $canApprove = true;
              elseif ($userRole === 'manager' && $fimp->status_pengajuan === 1) $canApprove = true;
              elseif ($userRole === 'direktur' && $fimp->status_pengajuan === 2) $canApprove = true;
          }
      }
      $approvalLabels = [0 => 'Belum Diproses', 1 => 'HRD', 2 => 'Manager', 3 => 'Direktur'];
      $nextLabels = [0 => 'HRD', 1 => 'Manager', 2 => 'Direktur'];
      $isRejected = $fimp->status_approval === 'Reject';
      $isApproved = $fimp->status_approval === 'Approve';
      $steps = [
        ['level' => 1, 'label' => 'HRD'],
        ['level' => 2, 'label' => 'Manager'],
        ['level' => 3, 'label' => 'Direktur'],
      ];
    @endphp

    @if($canApprove)
    <div class="card shadow-1 radius-8 mb-24">
      <div class="card-body p-24">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-16">
          <div class="d-flex align-items-center gap-12">
            <div class="action-status-icon">
              <i class="ri-time-line"></i>
            </div>
            <div>
              <span class="d-block fw-semibold text-sm text-neutral-900">Menunggu Persetujuan {{ $nextLabels[$fimp->status_pengajuan] ?? '' }}</span>
              <span class="d-block text-xs text-neutral-500 mt-2">Level saat ini: {{ $approvalLabels[$fimp->status_pengajuan] ?? '-' }}</span>
            </div>
          </div>
          <div class="d-flex gap-8">
            <button type="button" class="btn-action-reject" id="btn-reject-edit">
              <i class="ri-close-circle-line"></i> Tolak
            </button>
            <button type="button" class="btn-action-approve" id="btn-approve-edit">
              <i class="ri-checkbox-circle-line"></i> Approve
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif
      @include('fimp.partials.form', ['fimp' => $fimp])

      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('pengajuan-fimp.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
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

        // Calculate on load for edit form
        hitungTotalHari();

        var validationAlert = document.getElementById('validation-alert');
        if (validationAlert) {
            validationAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Approve handler
        $('#btn-approve-edit').on('click', function() {
            var encId = '{{ \App\Helpers\IdEncryptor::encrypt($fimp->id) }}';
            var name = '{{ e($fimp->karyawan->nama_karyawan ?? "-") }}';
            var nextLevel = '{{ $nextLabels[$fimp->status_pengajuan] ?? "Berikutnya" }}';

            Swal.fire({
                html: '<div class="approve-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div>'
                    + '<div class="approve-title">Approve Pengajuan FIMP</div>'
                    + '<div class="approve-text">Anda yakin ingin meng-approve pengajuan FIMP karyawan <strong>' + name + '</strong> ke level <strong>' + nextLevel + '</strong>?</div>'
                    + '<div class="approve-actions">'
                    + '  <button type="button" class="btn-approve-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
                    + '  <button type="button" class="btn-approve-confirm" id="swal-confirm"><i class="ri-checkbox-circle-line"></i> Ya, Approve</button>'
                    + '</div>',
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: false,
                customClass: { popup: 'approve-popup' },
                didOpen: function(popup) {
                    popup.querySelector('#swal-cancel').addEventListener('click', function() { Swal.close(); });
                    popup.querySelector('#swal-confirm').addEventListener('click', function() {
                        var btn = this;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Memproses...';
                        $.ajax({
                            url: '{{ url("pengajuan-fimp") }}/' + encId + '/approve',
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function(res) {
                                Swal.close();
                                if (res.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#487fff', timer: 2000, showConfirmButton: false }).then(function() {
                                        window.location.href = '{{ route("pengajuan-fimp.index") }}';
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#487fff' });
                            }
                        });
                    });
                }
            });
        });

        // Reject handler
        $('#btn-reject-edit').on('click', function() {
            var encId = '{{ \App\Helpers\IdEncryptor::encrypt($fimp->id) }}';
            var name = '{{ e($fimp->karyawan->nama_karyawan ?? "-") }}';

            Swal.fire({
                html: '<div class="reject-icon-wrapper"><i class="ri-close-circle-line"></i></div>'
                    + '<div class="reject-title">Tolak Pengajuan FIMP</div>'
                    + '<div class="reject-text">Masukkan alasan penolakan pengajuan FIMP karyawan <strong>' + name + '</strong>:</div>'
                    + '<div class="mb-16"><textarea id="reject-reason" class="form-control" rows="3" placeholder="Tulis alasan penolakan..." maxlength="500" style="resize:none;border-radius:10px;font-size:.8125rem"></textarea></div>'
                    + '<div class="reject-actions">'
                    + '  <button type="button" class="btn-reject-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
                    + '  <button type="button" class="btn-reject-confirm" id="swal-confirm"><i class="ri-close-circle-line"></i> Ya, Tolak</button>'
                    + '</div>',
                showConfirmButton: false,
                showCancelButton: false,
                showCloseButton: false,
                customClass: { popup: 'reject-popup' },
                didOpen: function(popup) {
                    popup.querySelector('#swal-cancel').addEventListener('click', function() { Swal.close(); });
                    popup.querySelector('#swal-confirm').addEventListener('click', function() {
                        var reason = popup.querySelector('#reject-reason').value.trim();
                        if (!reason) {
                            popup.querySelector('#reject-reason').classList.add('is-invalid');
                            popup.querySelector('#reject-reason').focus();
                            return;
                        }
                        var btn = this;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Memproses...';
                        $.ajax({
                            url: '{{ url("pengajuan-fimp") }}/' + encId + '/reject',
                            type: 'POST',
                            data: { _token: '{{ csrf_token() }}', reject_statement: reason },
                            success: function(res) {
                                Swal.close();
                                if (res.success) {
                                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#487fff', timer: 2000, showConfirmButton: false }).then(function() {
                                        window.location.href = '{{ route("pengajuan-fimp.index") }}';
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                                Swal.fire({ icon: 'error', title: 'Gagal', text: msg, confirmButtonColor: '#487fff' });
                            }
                        });
                    });
                }
            });
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

    /* ===== Action Card ===== */
    .action-status-icon {
        width: 40px;
        height: 40px;
        min-width: 40px;
        border-radius: 50%;
        background: #fef3c7;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .action-status-icon i {
        font-size: 18px;
        color: #d97706;
    }
    .btn-action-approve,
    .btn-action-reject {
        padding: 8px 20px;
        border-radius: 8px;
        font-size: .8125rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }
    .btn-action-approve {
        background: #059669;
        color: #fff;
        box-shadow: 0 2px 8px rgba(5,150,105,.25);
    }
    .btn-action-approve:hover {
        background: #047857;
        box-shadow: 0 4px 12px rgba(5,150,105,.35);
        transform: translateY(-1px);
    }
    .btn-action-reject {
        background: #f1f5f9;
        color: #dc2626;
    }
    .btn-action-reject:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* ===== Approval Timeline ===== */
    .approval-timeline .tl-item { position: relative; }
    .tl-indicator {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex-shrink: 0;
        margin-right: 14px;
    }
    .tl-dot {
        width: 30px;
        height: 30px;
        min-width: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 13px;
        position: relative;
        z-index: 1;
    }
    .tl-dot--success { background: #22c55e; }
    .tl-dot--danger  { background: #ef4444; }
    .tl-dot--warning { background: #f59e0b; }
    .tl-line {
        width: 2px;
        height: 100%;
        min-height: 36px;
        flex-grow: 1;
    }
    .tl-content {
        padding-top: 4px;
        padding-bottom: 20px;
    }
    .tl-title {
        display: block;
        font-size: .8125rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
        line-height: 1.3;
    }
    .tl-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 50px;
        font-size: .6875rem;
        font-weight: 600;
    }
    .tl-badge--success { color: #16a34a; background: #dcfce7; border: 1px solid #bbf7d0; }
    .tl-badge--danger  { color: #dc2626; background: #fee2e2; border: 1px solid #fecaca; }
    .tl-badge--warning { color: #d97706; background: #fef3c7; border: 1px solid #fde68a; }
    .tl-date {
        font-size: .6875rem;
        color: #94a3b8;
        font-weight: 400;
    }
    .tl-reject-box {
        margin-top: 4px;
        margin-bottom: 16px;
        padding: 12px 16px;
        background: #fee2e2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        font-size: .8125rem;
        color: #991b1b;
        line-height: 1.5;
        width: 100%;
    }
    .tl-reject-box strong { font-size: .8125rem; color: #7f1d1d; }
    .tl-reject-box p { color: #b91c1c; line-height: 1.6; }
    .tl-reject-box i { color: #dc2626; }

    /* ===== SweetAlert Popups ===== */
    .swal2-popup.approve-popup {
        border-radius: 16px;
        padding: 2rem 1.5rem 1.5rem;
        max-width: 400px;
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
        border: 1px solid rgba(0,0,0,.05);
    }
    .approve-icon-wrapper {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }
    .approve-icon-wrapper i { font-size: 32px; color: #059669; }
    .swal2-popup.approve-popup .swal2-html-container { margin: 0; padding: 0; }
    .approve-title { font-size: 1.15rem; font-weight: 700; color: #1B2559; margin-bottom: .375rem; }
    .approve-text { font-size: .8125rem; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; }
    .approve-text strong { color: #334155; font-weight: 600; }
    .approve-actions { display: flex; gap: .75rem; }
    .approve-actions .btn-approve-cancel,
    .approve-actions .btn-approve-confirm {
        flex: 1;
        padding: .6rem 1rem;
        border-radius: 10px;
        font-size: .8125rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        transition: all .2s ease;
    }
    .approve-actions .btn-approve-cancel { background: #f1f5f9; color: #475569; }
    .approve-actions .btn-approve-cancel:hover { background: #e2e8f0; color: #1e293b; }
    .approve-actions .btn-approve-confirm { background: #059669; color: #fff; box-shadow: 0 4px 12px rgba(5,150,105,.3); }
    .approve-actions .btn-approve-confirm:hover { background: #047857; box-shadow: 0 4px 16px rgba(5,150,105,.4); transform: translateY(-1px); }

    .swal2-popup.reject-popup {
        border-radius: 16px;
        padding: 2rem 1.5rem 1.5rem;
        max-width: 420px;
        box-shadow: 0 20px 60px rgba(0,0,0,.15);
        border: 1px solid rgba(0,0,0,.05);
    }
    .reject-icon-wrapper {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.25rem;
    }
    .reject-icon-wrapper i { font-size: 32px; color: #D97706; }
    .swal2-popup.reject-popup .swal2-html-container { margin: 0; padding: 0; }
    .reject-title { font-size: 1.15rem; font-weight: 700; color: #1B2559; margin-bottom: .375rem; }
    .reject-text { font-size: .8125rem; color: #64748b; line-height: 1.6; margin-bottom: 1rem; }
    .reject-text strong { color: #334155; font-weight: 600; }
    .reject-actions { display: flex; gap: .75rem; }
    .reject-actions .btn-reject-cancel,
    .reject-actions .btn-reject-confirm {
        flex: 1;
        padding: .6rem 1rem;
        border-radius: 10px;
        font-size: .8125rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        transition: all .2s ease;
    }
    .reject-actions .btn-reject-cancel { background: #f1f5f9; color: #475569; }
    .reject-actions .btn-reject-cancel:hover { background: #e2e8f0; color: #1e293b; }
    .reject-actions .btn-reject-confirm { background: #D97706; color: #fff; box-shadow: 0 4px 12px rgba(217,119,6,.3); }
    .reject-actions .btn-reject-confirm:hover { background: #B45309; box-shadow: 0 4px 16px rgba(217,119,6,.4); transform: translateY(-1px); }
</style>
@endpush
