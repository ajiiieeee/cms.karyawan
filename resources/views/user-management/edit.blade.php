@extends('layouts.main')

@section('title', 'Edit User')

@section('content')
    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
      <div>
        <h6 class="fw-semibold mb-0">Edit User</h6>
        <p class="text-neutral-600 mt-4 mb-0">Settings &raquo; User Management &raquo; Edit</p>
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
        <form action="{{ route('user-management.update', \App\Helpers\IdEncryptor::encrypt($user->id)) }}" method="POST" enctype="multipart/form-data" id="form-user">
          @csrf
          @method('PUT')
          @include('user-management.partials.form', ['user' => $user])

          <div class="d-flex justify-content-end gap-8 mt-24">
            <a href="{{ route('user-management.index') }}" class="btn btn-outline-neutral-600"><i class="ri-arrow-left-line"></i> Kembali</a>
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
    $('#form-user').on('submit', function() {
        var btn = $(this).find('.btn-submit');
        btn.prop('disabled', true);
        btn.find('.btn-text').text('Menyimpan...');
        btn.find('.spinner-border').removeClass('d-none');
    });

    const togglePassword = document.querySelector('#toggle-password');
    const toggleConfirmPassword = document.querySelector('#toggle-password-confirmation');
    const password = document.querySelector('#password');
    const confirmPassword = document.querySelector('#password_confirmation');

    togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // Toggle the eye / eye-slash icon
        this.classList.toggle('ri-eye-off-line');
    });

    toggleConfirmPassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPassword.setAttribute('type', type);
        // Toggle the eye / eye-slash icon
        this.classList.toggle('ri-eye-off-line');
    });
});
</script>
@endpush
