@extends('layouts.main')

@section('title', 'Saldo Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Saldo Cuti</h6>
        <p class="text-neutral-600 mt-4 mb-0">Manajemen Saldo Cuti Karyawan</p>
      </div>
      @if(Auth::user()->hasMenuAccess('saldo-cuti', 'add'))
      <a href="{{ route('saldo-cuti.create') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-2">
        <i class="ri-add-line"></i> Tambah Saldo Cuti
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
          <div class="skeleton-box rounded" style="width:80px;height:20px"></div>
          <div class="skeleton-box rounded" style="width:120px;height:20px"></div>
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
            <input type="text" id="custom-search" class="form-control search-box-input"
                   placeholder="Cari karyawan atau NIK...">
          </div>
        </div>
        <div class="table-responsive">
          <table id="saldo-cuti-table" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Karyawan</th>
                <th width="110">Total Cuti</th>
                <th width="180">Saldo Pemakaian</th>
                <th width="140">Periode</th>
                <th width="100">Status</th>
                <th width="160">Aksi</th>
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
$(document).ready(function () {
    var table = $('#saldo-cuti-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("saldo-cuti.data") }}',
            type: 'GET',
            dataSrc: function (json) {
                $('#skeleton-loader').addClass('d-none');
                $('#table-container').removeClass('d-none');
                return json.data;
            }
        },
        columns: [
            { data: 'no',            orderable: false, searchable: false, width: '50px' },
            { data: 'nama_karyawan', orderable: false },
            { data: 'total_cuti',    width: '110px' },
            { data: 'saldo',         orderable: false, searchable: false },
            { data: 'periode',       orderable: false, searchable: false },
            { data: 'status',        orderable: false, searchable: false },
            { data: 'aksi',          orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        dom: '<"d-none"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-16"<"text-neutral-500"i><"d-flex align-items-center gap-3"l p>>',
        language: {
            search:      'Cari:',
            lengthMenu:  '_MENU_',
            info:        'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty:   'Tidak ada data',
            emptyTable:  'Belum ada data saldo cuti',
            paginate:    { previous: '&laquo;', next: '&raquo;' }
        }
    });

    // Custom search
    var searchTimer;
    $('#custom-search').on('input', function () {
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () { table.search(val).draw(); }, 400);
    });

    // Delete handler
    $(document).on('click', '.btn-delete', function () {
        var id   = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
                + '<div class="delete-title">Hapus Saldo Cuti</div>'
                + '<div class="delete-text">Anda yakin ingin menghapus saldo cuti karyawan <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
                + '<div class="delete-actions">'
                + '  <button type="button" class="btn-delete-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>'
                + '  <button type="button" class="btn-delete-confirm" id="swal-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>'
                + '</div>',
            showConfirmButton: false,
            showCancelButton:  false,
            showCloseButton:   false,
            customClass: { popup: 'delete-popup' },
            didOpen: function (popup) {
                popup.querySelector('#swal-cancel').addEventListener('click', function () { Swal.close(); });
                popup.querySelector('#swal-confirm').addEventListener('click', function () {
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menghapus...';
                    var form = $('#delete-form');
                    form.attr('action', '{{ route("saldo-cuti.index") }}/' + id);
                    form.submit();
                });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
/* ===== Search Box ===== */
.search-box { min-width: 280px; }
.search-box-icon {
    position: absolute; left: 14px; top: 50%;
    transform: translateY(-50%); font-size: 16px;
    color: #94a3b8; pointer-events: none; transition: color .2s ease;
}
.search-box-input {
    padding: 8px 14px 8px 40px; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: .8125rem; color: #334155;
    background: #f8fafc; transition: all .25s ease;
}
.search-box-input::placeholder { color: #b0b8c4; font-weight: 400; }
.search-box-input:focus {
    outline: none; border-color: #487fff;
    background: #fff; box-shadow: 0 0 0 3px rgba(72,127,255,.1);
}

/* ===== DataTables tweaks ===== */
.dataTables_wrapper .dataTables_info { font-size: .8125rem; padding: 0; }
.dataTables_wrapper .dataTables_length select {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 4px 8px; font-size: .8125rem; background: #f8fafc;
}
.dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: 8px !important; font-size: .8125rem; }

/* ===== Skeleton ===== */
.skeleton-box {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}
@keyframes skeleton-loading {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ===== Delete Popup ===== */
.swal2-popup.delete-popup {
    border-radius: 16px; padding: 2rem 1.5rem 1.5rem;
    max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.15);
    border: 1px solid rgba(0,0,0,.05);
}
.delete-icon-wrapper {
    width: 72px; height: 72px; border-radius: 50%;
    background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem;
}
.delete-icon-wrapper i { font-size: 32px; color: #DC2626; }
.swal2-popup.delete-popup .swal2-html-container { margin: 0; padding: 0; }
.delete-title { font-size: 1.15rem; font-weight: 700; color: #1B2559; margin-bottom: .375rem; }
.delete-text { font-size: .8125rem; color: #64748b; line-height: 1.6; margin-bottom: 1.5rem; }
.delete-text strong { color: #334155; font-weight: 600; }
.delete-actions { display: flex; gap: .75rem; }
.delete-actions .btn-delete-cancel,
.delete-actions .btn-delete-confirm {
    flex: 1; padding: .6rem 1rem; border-radius: 10px;
    font-size: .8125rem; font-weight: 600; border: none; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    gap: .4rem; transition: all .2s ease;
}
.delete-actions .btn-delete-cancel { background: #f1f5f9; color: #475569; }
.delete-actions .btn-delete-cancel:hover { background: #e2e8f0; color: #1e293b; }
.delete-actions .btn-delete-confirm { background: #DC2626; color: #fff; box-shadow: 0 4px 12px rgba(220,38,38,.3); }
.delete-actions .btn-delete-confirm:hover { background: #b91c1c; box-shadow: 0 4px 16px rgba(220,38,38,.4); transform: translateY(-1px); }

/* ===== Dark mode ===== */
[data-theme="dark"] .search-box-input { background: #1e293b; border-color: #334155; color: #e2e8f0; }
[data-theme="dark"] .search-box-input:focus { background: #0f172a; border-color: #487fff; box-shadow: 0 0 0 3px rgba(72,127,255,.15); }
[data-theme="dark"] .search-box-input::placeholder { color: #64748b; }
[data-theme="dark"] .swal2-popup.delete-popup { background: #1e293b; border-color: rgba(255,255,255,.08); }
[data-theme="dark"] .delete-title { color: #e2e8f0; }
[data-theme="dark"] .delete-text { color: #94a3b8; }
[data-theme="dark"] .delete-text strong { color: #cbd5e1; }
[data-theme="dark"] .delete-icon-wrapper { background: linear-gradient(135deg, rgba(220,38,38,.15) 0%, rgba(220,38,38,.25) 100%); }
[data-theme="dark"] .delete-actions .btn-delete-cancel { background: #334155; color: #cbd5e1; }
[data-theme="dark"] .delete-actions .btn-delete-cancel:hover { background: #475569; color: #f1f5f9; }
</style>
@endpush
