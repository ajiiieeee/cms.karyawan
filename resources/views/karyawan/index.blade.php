@extends('layouts.main')

@section('title', 'Data Karyawan')

@section('content')
<div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <div>
    <h6 class="fw-semibold mb-0">Data Karyawan</h6>
    <p class="text-neutral-600 mt-4 mb-0">Data Master &raquo; Karyawan</p>
  </div>
  <div class="d-flex align-items-center gap-8">
    <!-- Tombol Export Excel -->
    <button type="button" class="btn btn-outline-success-600 d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalExportKaryawan">
      <i class="ri-file-excel-2-line text-lg"></i> Export Excel
    </button>

    @if(Auth::user()->hasMenuAccess('karyawan', 'add'))
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary-600 d-inline-flex align-items-center gap-2">
      <i class="ri-add-line"></i> Tambah Karyawan
    </a>
    @endif
  </div>
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
    <div class="table-responsive">
      <table id="karyawan-table" class="table table-striped table-hover" style="width: 100%">
        <thead>
          <tr>
            <th width="50">No</th>
            <th>Nama Karyawan</th>
            <th>Tanggal Lahir</th>
            <th>Jabatan</th>
            <th>Telepon</th>
            <th>Status</th>
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

<!-- Modal Export Karyawan Multi-Sheet -->
<div class="modal fade" id="modalExportKaryawan" tabindex="-1" aria-labelledby="modalExportKaryawanLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content radius-8 border-0 shadow">
      <form action="{{ route('karyawan.export') }}" method="GET">
        <div class="modal-header bg-neutral-50 py-16 px-24 border-bottom border-neutral-200">
          <h6 class="modal-title fw-semibold text-md mb-0 d-flex align-items-center gap-8" id="modalExportKaryawanLabel">
            <i class="ri-file-excel-2-line text-success-600 text-lg"></i> Export Laporan Kepegawaian (HR)
          </h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-24">
          <p class="text-xs text-neutral-500 mb-16">
            Hasil unduhan berupa 1 file Excel dengan 3 sheet terpisah: <strong>Profil &amp; Saldo Cuti</strong>, <strong>Riwayat Pengajuan Cuti</strong>, dan <strong>Riwayat Pengajuan FIMP</strong>.
          </p>

          <div class="mb-16">
            <label class="form-label text-sm fw-semibold">Status Akun</label>
            <select name="status_akun" class="form-select">
              <option value="">— Semua Status —</option>
              <option value="aktif">Aktif</option>
              <option value="tidak_aktif">Tidak Aktif</option>
            </select>
          </div>

          <div class="mb-16">
            <label class="form-label text-sm fw-semibold">Jabatan / Divisi</label>
            <select name="jabatan" class="form-select">
              <option value="">— Semua Jabatan —</option>
              @if(isset($jabatanOptions))
              @foreach($jabatanOptions as $val => $lbl)
              <option value="{{ $val }}">{{ $lbl }}</option>
              @endforeach
              @endif
            </select>
          </div>
        </div>

        <div class="modal-footer bg-white py-12 px-24 border-top border-neutral-200 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-neutral-600" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success-600 text-white d-inline-flex align-items-center gap-2">
            <i class="ri-download-2-line"></i> Download Excel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    var table = $('#karyawan-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("karyawan.data") }}',
        type: 'GET',
        dataSrc: function(json) {
          $('#skeleton-loader').addClass('d-none');
          $('#table-container').removeClass('d-none');
          return json.data;
        }
      },
      columns: [{
          data: 'no',
          orderable: false,
          searchable: false,
          width: '50px'
        },
        {
          data: 'nama_karyawan'
        },
        {
          data: 'tanggal_lahir'
        },
        {
          data: 'jabatan',
          orderable: false
        },
        {
          data: 'telefon'
        },
        {
          data: 'status'
        },
        {
          data: 'aksi',
          orderable: false,
          searchable: false
        }
      ],
      order: [
        [1, 'asc']
      ],
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
        infoEmpty: "Tidak ada data",
        emptyTable: "Tidak ada data karyawan",
        paginate: {
          previous: "&laquo;",
          next: "&raquo;"
        }
      }
    });

    $(document).on('click', '.btn-delete', function() {
      var id = $(this).data('id');
      var name = $(this).data('name');

      Swal.fire({
        html: '<div class="delete-icon-wrapper"><i class="ri-delete-bin-6-line"></i></div>' +
          '<div class="delete-title">Hapus Karyawan</div>' +
          '<div class="delete-text">Anda yakin ingin menghapus karyawan <strong>' + name + '</strong>? Data yang dihapus tidak dapat dikembalikan.</div>' +
          '<div class="delete-actions">' +
          '  <button type="button" class="btn-delete-cancel" id="swal-cancel"><i class="ri-arrow-left-line"></i> Batal</button>' +
          '  <button type="button" class="btn-delete-confirm" id="swal-confirm"><i class="ri-delete-bin-6-line"></i> Ya, Hapus</button>' +
          '</div>',
        showConfirmButton: false,
        showCancelButton: false,
        showCloseButton: false,
        customClass: {
          popup: 'delete-popup'
        },
        didOpen: function(popup) {
          popup.querySelector('#swal-cancel').addEventListener('click', function() {
            Swal.close();
          });
          popup.querySelector('#swal-confirm').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Menghapus...';
            var form = $('#delete-form');
            form.attr('action', '{{ route("karyawan.index") }}/' + id);
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
    0% {
      background-position: 200% 0;
    }

    100% {
      background-position: -200% 0;
    }
  }
</style>
@endpush