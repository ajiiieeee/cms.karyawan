@extends('layouts.main')

@section('title', 'Edit Pengajuan Cuti')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Edit Bidang Studi</h6>
        <p class="text-neutral-600 mt-4 mb-0">Bidang Studi &raquo; Edit</p>
      </div>
    </div>

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show mb-16" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <form action="{{ route('bidang-studi.update', \App\Helpers\IdEncryptor::encrypt($bidangStudi->id)) }}" method="POST" id="form-bidang-studi">
      @csrf
      @method('PUT')
      @include('bidang_studi.partials.form', ['bidangStudi' => $bidangStudi])
    
      <div class="d-flex justify-content-end gap-8 mt-24">
        <a href="{{ route('bidang-studi.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
        <button type="submit" class="btn btn-primary-600 btn-submit">
          <span class="btn-text">Simpan Perubahan</span>
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>

@endsection

@push ('scripts')
<script>
    $(document).ready(function() {
        $('#form-bidang-studi').on('submit', function() {
            var btn = $(this).find('.btn-submit');
            btn.prop('disabled', true);
            btn.find('.btn-text').text('Menyimpan...');
            btn.find('.spinner-border').removeClass('d-none');
        });


    })
</script>

@endpush    