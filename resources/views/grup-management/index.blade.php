@extends('layouts.main')

@section('title', 'Grup Management')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Grup Management</h6>
        <p class="text-neutral-600 mt-4 mb-0">Settings &raquo; Grup Management</p>
      </div>
      @if(Auth::user()->hasMenuAccess('grup-management', 'add'))
      <a href="{{ route('grup-management.create') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-2">
        <i class="ri-add-line"></i> Tambah Grup
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
          <div class="skeleton-box rounded" style="width:150px;height:20px"></div>
          <div class="skeleton-box rounded" style="width:80px;height:20px"></div>
        </div>
        @endfor
      </div>
    </div>

    <div id="table-container" class="card shadow-1 radius-8 d-none">
      <div class="card-body p-24">
        <div class="table-responsive">
          <table id="grup-table" class="table table-striped table-hover" style="width: 100%">
            <thead>
              <tr>
                <th width="50">No</th>
                <th>Nama Grup</th>
                <th>Deskripsi</th>
                <th width="100">Jumlah User</th>
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
    var table = $('#grup-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("grup-management.data") }}',
            type: 'GET',
            dataSrc: function(json) {
                $('#skeleton-loader').addClass('d-none');
                $('#table-container').removeClass('d-none');
                return json.data;
            }
        },
        columns: [
            { data: 'no', orderable: false, searchable: false, width: '50px' },
            { data: 'nama_grup' },
            { data: 'deskripsi' },
            { data: 'users_count', className: 'text-center' },
            { data: 'aksi', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            emptyTable: "Tidak ada data grup",
            paginate: { previous: "&laquo;", next: "&raquo;" }
        }
    });

    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');

        Swal.fire({
            html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>'
                + '<div class="delete-title">Hapus Grup</div>'
                + '<div class="delete-text">Anda yakin ingin menghapus grup <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>'
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
                    form.attr('action', '{{ route("grup-management.index") }}/' + id);
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
