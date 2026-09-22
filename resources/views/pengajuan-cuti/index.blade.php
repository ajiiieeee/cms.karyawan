@extends('layouts.main')

@section('title', 'Pengajuan Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Pengajuan Cuti</h6>
        <p class="text-neutral-600 mt-4 mb-0">Pengajuan Cuti</p>
      </div>
      @if(Auth::user()->hasMenuAccess('pengajuan-cuti', 'add'))
      <a href="{{ route('pengajuan-cuti.create') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-2">
        <i class="ri-add-line"></i> Tambah Pengajuan
      </a>
      @endif
    </div>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show mb-16" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <!-- Skeleton Loading -->
    <div id="skeleton-loader" class="card shadow-1 radius-8">
      <div class="card-body p-24">
        @for($i = 0; $i < 5; $i++)
        <div class="d-flex align-items-center gap-3 mb-16">
          <div class="skeleton-box rounded" style="width:40px;height:20px"></div>
          <div class="skeleton-box rounded flex-grow-1" style="height:20px"></div>
          <div class="skeleton-box rounded" style="width:100px;height:20px"></div>
          <div class="skeleton-box rounded" style="width:80px;height:20px"></div>
          <div class="skeleton-box rounded" style="width:100px;height:20px"></div>
        </div>
        @endfor
      </div>
    </div>

    <div id="table-container" class="card shadow-1 radius-8 d-none">
      <div class="card-body p-24">
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 mb-20">
          <div class="search-box position-relative">
            <i class="ri-search-line search-box-icon"></i>
            <input type="text" id="custom-search" class="form-control search-box-input" placeholder="Cari karyawan, jenis cuti...">
          </div>
        </div>
        <div class="table-responsive">
          <table id="pengajuan-cuti-table" class="table table-striped table-hover" style="width: 100%">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Karyawan</th>
                <th>Jenis Cuti</th>
                <th>Periode</th>
                <th>Jumlah Hari</th>
                <th>Status</th>
                <th width="280">Aksi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>

    <form id="delete-form" method="POST" class="d-none">
      @csrf
      @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#pengajuan-cuti-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("pengajuan-cuti.data") }}',
            type: 'GET',
            dataSrc: function(json) {
                $('#skeleton-loader').addClass('d-none');
                $('#table-container').removeClass('d-none');
                return json.data;
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false, width: '50px' },
            { data: 'nama_karyawan' },
            { data: 'jenis_cuti' },
            { data: 'tanggal' },
            { data: 'jumlah_hari', orderable: false },
            { data: 'status' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        dom: '<"d-none"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-16"<"text-neutral-500"i><"d-flex align-items-center gap-3"l p>>',
        language: {
            search: "Cari:",
            lengthMenu: "_MENU_",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            emptyTable: "Tidak ada data pengajuan cuti",
            paginate: { previous: "&laquo;", next: "&raquo;" }
        }
    });

    // Custom search input
    var searchTimer;
    $('#custom-search').on('input', function() {
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            table.search(val).draw();
        }, 400);
    });

    // Delete handler
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
                + '<div class="delete-title">Hapus Pengajuan Cuti</div>'
                + '<div class="delete-text">Anda yakin ingin menghapus pengajuan cuti karyawan <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
                + '<div class="delete-actions">'
                + '  <button type="button" class="btn-delete-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
                + '  <button type="button" class="btn-delete-confirm" id="swal-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>'
                + '</div>',
            showConfirmButton: false,
            showCancelButton: false,
            showCloseButton: false,
            customClass: { popup: 'delete-popup' },
            didOpen: function(popup) {
                popup.querySelector('#swal-cancel').addEventListener('click', function() { Swal.close(); });
                popup.querySelector('#swal-confirm').addEventListener('click', function() {
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menghapus...';
                    var form = $('#delete-form');
                    form.attr('action', '{{ route("pengajuan-cuti.index") }}/' + id);
                    form.submit();
                });
            }
        });
    });

    // Approve handler
    $(document).on('click', '.btn-approve', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var level = $(this).data('level');
        var levels = {0: 'Admin & HRD', 1: 'Manager', 2: 'Direktur'};
        var nextLevel = levels[level] || 'Berikutnya';

        Swal.fire({
            html: '<div class="approve-icon-wrapper"><i class="ri-checkbox-circle-line"></i></div>'
                + '<div class="approve-title">Approve Pengajuan Cuti</div>'
                + '<div class="approve-text">Anda yakin ingin meng-approve pengajuan cuti karyawan <strong>' + name + '</strong> ke level <strong>' + nextLevel + '</strong>?</div>'
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
                        url: '{{ url("pengajuan-cuti") }}/' + id + '/approve',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(res) {
                            Swal.close();
                            if (res.success) {
                                table.ajax.reload(null, false);
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#487fff', timer: 2000, showConfirmButton: false });
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
    $(document).on('click', '.btn-reject', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            html: '<div class="reject-icon-wrapper"><i class="ri-close-circle-line"></i></div>'
                + '<div class="reject-title">Tolak Pengajuan Cuti</div>'
                + '<div class="reject-text">Masukkan alasan penolakan pengajuan cuti karyawan <strong>' + name + '</strong>:</div>'
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
                        url: '{{ url("pengajuan-cuti") }}/' + id + '/reject',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}', reject_statement: reason },
                        success: function(res) {
                            Swal.close();
                            if (res.success) {
                                table.ajax.reload(null, false);
                                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#487fff', timer: 2000, showConfirmButton: false });
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
/* Custom search box */
.search-box {
    min-width: 280px;
}
.search-box-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #94a3b8;
    pointer-events: none;
    transition: color .2s ease;
}
.search-box-input {
    padding: 8px 14px 8px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: .8125rem;
    color: #334155;
    background: #f8fafc;
    transition: all .25s ease;
}
.search-box-input::placeholder {
    color: #b0b8c4;
    font-weight: 400;
}
.search-box-input:focus {
    outline: none;
    border-color: #487fff;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(72,127,255,.1);
}
.search-box-input:focus + .search-box-icon,
.search-box:has(.search-box-input:focus) .search-box-icon {
    color: #487fff;
}

/* DataTables footer tweaks */
.dataTables_wrapper .dataTables_info {
    font-size: .8125rem;
    padding: 0;
}
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 4px 8px;
    font-size: .8125rem;
    background: #f8fafc;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 8px !important;
    font-size: .8125rem;
}

/* Dark mode search box */
[data-theme="dark"] .search-box-input {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
[data-theme="dark"] .search-box-input:focus {
    background: #0f172a;
    border-color: #487fff;
    box-shadow: 0 0 0 3px rgba(72,127,255,.15);
}
[data-theme="dark"] .search-box-input::placeholder {
    color: #64748b;
}
[data-theme="dark"] .search-box-icon {
    color: #64748b;
}

.skeleton-box {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}
@keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Approve Popup */
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
.approve-icon-wrapper i {
    font-size: 32px;
    color: #059669;
}
.swal2-popup.approve-popup .swal2-html-container {
    margin: 0;
    padding: 0;
}
.approve-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1B2559;
    margin-bottom: .375rem;
}
.approve-text {
    font-size: .8125rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
}
.approve-text strong {
    color: #334155;
    font-weight: 600;
}
.approve-actions {
    display: flex;
    gap: .75rem;
}
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
.approve-actions .btn-approve-cancel {
    background: #f1f5f9;
    color: #475569;
}
.approve-actions .btn-approve-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.approve-actions .btn-approve-confirm {
    background: #059669;
    color: #fff;
    box-shadow: 0 4px 12px rgba(5,150,105,.3);
}
.approve-actions .btn-approve-confirm:hover {
    background: #047857;
    box-shadow: 0 4px 16px rgba(5,150,105,.4);
    transform: translateY(-1px);
}

/* Reject Popup */
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
.reject-icon-wrapper i {
    font-size: 32px;
    color: #D97706;
}
.swal2-popup.reject-popup .swal2-html-container {
    margin: 0;
    padding: 0;
}
.reject-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1B2559;
    margin-bottom: .375rem;
}
.reject-text {
    font-size: .8125rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1rem;
}
.reject-text strong {
    color: #334155;
    font-weight: 600;
}
.reject-actions {
    display: flex;
    gap: .75rem;
}
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
.reject-actions .btn-reject-cancel {
    background: #f1f5f9;
    color: #475569;
}
.reject-actions .btn-reject-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.reject-actions .btn-reject-confirm {
    background: #D97706;
    color: #fff;
    box-shadow: 0 4px 12px rgba(217,119,6,.3);
}
.reject-actions .btn-reject-confirm:hover {
    background: #B45309;
    box-shadow: 0 4px 16px rgba(217,119,6,.4);
    transform: translateY(-1px);
}

/* Dark mode */
[data-theme="dark"] .swal2-popup.approve-popup,
[data-theme="dark"] .swal2-popup.reject-popup {
    background: #1e293b;
    border-color: rgba(255,255,255,.08);
}
[data-theme="dark"] .approve-title,
[data-theme="dark"] .reject-title { color: #e2e8f0; }
[data-theme="dark"] .approve-text,
[data-theme="dark"] .reject-text { color: #94a3b8; }
[data-theme="dark"] .approve-text strong,
[data-theme="dark"] .reject-text strong { color: #cbd5e1; }
[data-theme="dark"] .approve-icon-wrapper {
    background: linear-gradient(135deg, rgba(5,150,105,.15) 0%, rgba(5,150,105,.25) 100%);
}
[data-theme="dark"] .reject-icon-wrapper {
    background: linear-gradient(135deg, rgba(217,119,6,.15) 0%, rgba(217,119,6,.25) 100%);
}
[data-theme="dark"] .approve-actions .btn-approve-cancel,
[data-theme="dark"] .reject-actions .btn-reject-cancel {
    background: #334155;
    color: #cbd5e1;
}
[data-theme="dark"] .approve-actions .btn-approve-cancel:hover,
[data-theme="dark"] .reject-actions .btn-reject-cancel:hover {
    background: #475569;
    color: #f1f5f9;
}
</style>
@endpush
