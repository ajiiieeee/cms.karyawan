@extends('layouts.main')

@section('title', 'Tambah Menu')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Tambah Menu</h6>
        <p class="text-neutral-600 mt-4 mb-0">Settings &raquo; Menu Management &raquo; Tambah</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="card shadow-1 radius-8">
      <div class="card-body p-24">
        <form action="{{ route('menu-management.store') }}" method="POST" id="form-menu">
          @csrf
          @include('menu-management.partials.form', ['menu' => null])

          <div class="d-flex justify-content-end gap-8 mt-24">
            <a href="{{ route('menu-management.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
            <button type="submit" class="btn btn-primary-600 btn-submit">
              <span class="btn-text">Simpan Perubahan</span>
              <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#form-menu').on('submit', function() {
        var btn = $(this).find('.btn-submit');
        btn.prop('disabled', true);
        btn.find('.btn-text').text('Menyimpan...');
        btn.find('.spinner-border').removeClass('d-none');
    });
});
</script>
@endpush
