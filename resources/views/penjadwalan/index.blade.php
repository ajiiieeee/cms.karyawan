@extends('layouts.main')

@section('title', 'Data Penjadwalan Kursus')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Data Penjadwalan Kursus</h6>
        <p class="text-neutral-600 mt-4 mb-0">Penjadwalan</p>
      </div>
      @if(Auth::user()->hasMenuAccess('penjadwalan', 'add'))
      <a href="{{ route('penjadwalan.create') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-2">
        <i class="ri-add-line"></i> Tambah Jadwal
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
          <div class="skeleton-box rounded" style="width:80px;height:20px"></div>
          <div class="skeleton-box rounded" style="width:120px;height:20px"></div>
        </div>
        @endfor
      </div>
    </div>

    <div id="table-container" class="card shadow-1 radius-8 d-none">
      <div class="card-body p-24">
        <div class="d-flex flex-wrap align-items-center justify-content-end gap-3 mb-20">
          <div class="search-box position-relative">
            <i class="ri-search-line search-box-icon"></i>
            <input type="text" id="custom-search" class="form-control search-box-input" placeholder="Cari jadwal kursus...">
          </div>
        </div>
        <div class="table-responsive">
          <table id="penjadwalan-table" class="table table-striped table-hover" style="width: 100%">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Nama Siswa</th>
                <th>Trainer</th>
                <th>Bidang Studi / Level</th>
                <th>Status Jadwal</th>
                <th>Lokasi</th>
                <th width="220">Aksi</th>
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
    var table = $('#penjadwalan-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("penjadwalan.data") }}',
            type: 'GET',
            dataSrc: function(json) {
                $('#skeleton-loader').addClass('d-none');
                $('#table-container').removeClass('d-none');
                return json.data;
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false, width: '50px' },
            { data: 'nama_siswa' },
            { data: 'nama_trainer' },
            { data: 'bidang_studi' },
            { data: 'lokasi', orderable: false },
            { data: 'status_jadwal' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        dom: '<"d-none"f>rt<"d-flex flex-wrap align-items-center justify-content-between gap-3 mt-16"<"text-neutral-500"i><"d-flex align-items-center gap-3"l p>>',
        language: {
            search: "Cari:",
            lengthMenu: "_MENU_",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            emptyTable: "Tidak ada data penjadwalan",
            paginate: { previous: "&laquo;", next: "&raquo;" }
        }
    });

    var searchTimer;
    $('#custom-search').on('input', function() {
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            table.search(val).draw();
        }, 400);
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
                + '<div class="delete-title">Hapus Penjadwalan</div>'
                + '<div class="delete-text">Anda yakin ingin menghapus jadwal kursus untuk siswa <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
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
                    form.attr('action', '{{ route("penjadwalan.index") }}/' + id);
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
</style>
@endpush
