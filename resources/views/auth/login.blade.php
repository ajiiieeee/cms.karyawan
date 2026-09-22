<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Title -->
  <title>{{ config('app.name', 'C-Mobile') }} | Masuk</title>
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
  <!-- remix icon font css  -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/remixicon.css') }}">
  <!-- BootStrap css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
  <!-- Apex Chart css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
  <!-- Data Table css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
  <!-- Date picker css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
  <!-- Calendar css -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
  <!-- calendar -->
  <link rel="stylesheet" href="{{ asset('assets/css/lib/calendar.css') }}">
  <!-- main css -->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        .login-page {
            max-height: 100vh;
        }

        @media (max-width: 991.98px) {
            .login-form-side {
                width: 100% !important;
                min-height: 100vh;
                padding: 24px 16px !important;
            }

            .login-form-card {
                width: 100%;
                max-width: 420px;
            }
        }

        @media (max-width: 575.98px) {
            .login-form-side {
                padding: 20px 14px !important;
            }

            .login-form-card {
                max-width: 100%;
            }

            .submit-form {
                gap: 24px !important;
            }
        }
    </style>
</head>

<body>

  <!-- Theme Customization Structure Start -->
<div class="body-overlay"></div>


<div class="d-lg-flex bg-white login-page">
    <div class="w-50 d-lg-flex d-none overflow-hidden">
        <img src="{{ asset('assets/images/welcome.jpeg') }}" alt="Masuk Image" class="w-100 h-100 object-fit-cover">
    </div>
    <div class="lg-w-50 d-flex justify-content-center align-items-center login-form-side">
        <div class="max-w-440-px mx-auto login-form-card">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-50 w-75 d-flex justify-content-center mx-auto">
            <div class="mx-38">
                <div class="mt-32 mb-32">
                    <h1 class="h6 fw-bold text-dark mb-8">
                        Selamat Datang Kembali 👋
                    </h1>
                    <p class="text-sm text-dark mb-0">
                        Masuk ke akun Anda untuk melanjutkan
                    </p>
                </div>
    
                {{-- Alert Messages --}}
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
    
                <form id="loginForm" action="{{ route('login.submit') }}" method="POST" class="d-flex flex-column gap-32 submit-form" autocomplete="off">
                    @csrf
                    <div class="d-flex flex-column gap-16">
                        <div>
                            <label for="username" class="text-sm fw-semibold text-dark d-inline-block mb-8">
                                Username
                                <span class="text-danger-600">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-control text-dark @error('username') is-invalid @enderror" 
                                placeholder="Enter your username"
                                value="{{ old('username') }}"
                                maxlength="50"
                                autocomplete="username"
                                required
                            >
                            @error('username')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
    
                        <div>
                            <label for="password" class="text-sm fw-semibold text-dark d-inline-block mb-8">
                                Password
                                <span class="text-danger-600">*</span>
                            </label>
                            <div class="position-relative">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="password-field form-control text-dark @error('password') is-invalid @enderror" 
                                    placeholder="Enter your password"
                                    minlength="6"
                                    maxlength="255"
                                    autocomplete="current-password"
                                    required
                                >
                                <span
                                    id="togglePasswordBtn"
                                    class="position-absolute end-0 top-50 translate-middle-y me-16 text-secondary-light"
                                    style="cursor: pointer; z-index: 5; line-height: 0;"
                                    role="button"
                                    aria-label="Toggle password visibility">
                                    {{-- Eye Open --}}
                                    <svg id="iconEyeOpen" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    {{-- Eye Closed --}}
                                    <svg id="iconEyeClosed" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                        <line x1="1" y1="1" x2="23" y2="23"></line>
                                    </svg>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6 mx-auto">
                        <button type="submit" id="submitBtn" class="loginBtn btn btn-primary-600 text-md btn-sm px-12 py-16 w-100 radius-8">
                            <span id="submitText">Sign In</span>
                            <span id="submitSpinner" class="d-none">
                                <span class="spinner-border spinner-border-sm me-6" role="status" aria-hidden="true"></span>
                                Loading...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

  <!-- jQuery library js -->
  <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
  <!-- Bootstrap js -->
  <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
  <!-- Apex Chart js -->
  {{-- <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script> --}}
  <!-- Iconify Font js -->
  <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
  <!-- Data Table js -->
  {{-- <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script> --}}
  
  <!-- jQuery UI js -->
  <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
  
  <!-- main js -->
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <script>
    // Toggle Password Visibility
    document.getElementById('togglePasswordBtn').addEventListener('click', function () {
      var input     = document.getElementById('password');
      var eyeOpen   = document.getElementById('iconEyeOpen');
      var eyeClosed = document.getElementById('iconEyeClosed');
      if (input.type === 'password') {
        input.type            = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = '';
      } else {
        input.type              = 'password';
        eyeOpen.style.display   = '';
        eyeClosed.style.display = 'none';
      }
    });

    // Submit Button Spinner
    document.getElementById('loginForm').addEventListener('submit', function () {
      var btn     = document.getElementById('submitBtn');
      var text    = document.getElementById('submitText');
      var spinner = document.getElementById('submitSpinner');
      btn.disabled = true;
      text.classList.add('d-none');
      spinner.classList.remove('d-none');
    });
  </script>

</body>

</html>



