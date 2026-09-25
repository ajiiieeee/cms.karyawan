<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="@yield('meta_description', 'Modern Education Admin Dashboard')">
  <meta name="robots" content="INDEX,FOLLOW">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <script>
    (function () {
      try {
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
        } else if (savedTheme === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
      } catch (e) {}
    })();
  </script>

  <!-- Title -->
  <title>{{ config('app.name', 'Edudash') }} | @yield('title', 'Dashboard')</title>
  
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon-logo.png') }}" sizes="16x16">
  
  <!-- remix icon font css -->
  <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
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
  <!-- dark theme css -->
  <link rel="stylesheet" href="{{ asset('assets/css/theme-dark.css') }}">
  
  @stack('styles')
</head>

<body>
  <div class="body-overlay"></div>

  <div class="overlay bg-black bg-opacity-50 w-100 h-100 position-fixed z-9 visibility-hidden opacity-0 duration-300"></div>

  @include('layouts.sidebar')

  <main class="dashboard-main">
    @include('layouts.navbar')

    <div class="dashboard-main-body">
      @yield('content')
    </div>

    @include('layouts.footer')
  </main>

  <!-- jQuery library js -->
  <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
  <!-- Bootstrap js -->
  <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
  <!-- Apex Chart js -->
  <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
  <!-- Iconify Font js -->
  <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
  <!-- Data Table js -->
  <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
  <!-- jQuery UI js -->
  <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
  <!-- SweetAlert2 js -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Select2 js -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <!-- main js -->
  <script src="{{ asset('assets/js/app.js') }}"></script>

  <!-- Logout Confirmation -->
  <style>
    .swal2-popup.logout-popup {
      border-radius: 16px;
      padding: 2rem 1.5rem 1.5rem;
      max-width: 380px;
      box-shadow: 0 20px 60px rgba(0,0,0,.15);
      border: 1px solid rgba(0,0,0,.05);
    }
    .logout-icon-wrapper {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
    }
    .logout-icon-wrapper i {
      font-size: 36px;
      color: #DC2626;
    }
    .swal2-popup.logout-popup .swal2-html-container {
      margin: 0;
      padding: 0;
    }
    .logout-title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1B2559;
      margin-bottom: .5rem;
    }
    .logout-text {
      font-size: .875rem;
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    .logout-actions {
      display: flex;
      gap: .75rem;
    }
    .logout-actions .btn-logout-cancel,
    .logout-actions .btn-logout-confirm {
      flex: 1;
      padding: .625rem 1rem;
      border-radius: 10px;
      font-size: .875rem;
      font-weight: 600;
      border: none;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .4rem;
      transition: all .2s ease;
    }
    .logout-actions .btn-logout-cancel {
      background: #f1f5f9;
      color: #475569;
    }
    .logout-actions .btn-logout-cancel:hover {
      background: #e2e8f0;
      color: #1e293b;
    }
    .logout-actions .btn-logout-confirm {
      background: #DC2626;
      color: #fff;
      box-shadow: 0 4px 12px rgba(220,38,38,.3);
    }
    .logout-actions .btn-logout-confirm:hover {
      background: #b91c1c;
      box-shadow: 0 4px 16px rgba(220,38,38,.4);
      transform: translateY(-1px);
    }
    .logout-actions .btn-logout-confirm:disabled {
      opacity: .7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    [data-theme="dark"] .swal2-popup.logout-popup {
      background: #1e293b;
      border-color: rgba(255,255,255,.08);
    }
    [data-theme="dark"] .logout-title {
      color: #e2e8f0;
    }
    [data-theme="dark"] .logout-text {
      color: #94a3b8;
    }
    [data-theme="dark"] .logout-icon-wrapper {
      background: linear-gradient(135deg, rgba(220,38,38,.15) 0%, rgba(220,38,38,.25) 100%);
    }
    [data-theme="dark"] .logout-actions .btn-logout-cancel {
      background: #334155;
      color: #cbd5e1;
    }
    [data-theme="dark"] .logout-actions .btn-logout-cancel:hover {
      background: #475569;
      color: #f1f5f9;
    }

    /* ========== Delete Confirmation Popup ========== */
    .swal2-popup.delete-popup {
      border-radius: 16px;
      padding: 2rem 1.5rem 1.5rem;
      max-width: 400px;
      box-shadow: 0 20px 60px rgba(0,0,0,.15);
      border: 1px solid rgba(0,0,0,.05);
    }
    .delete-icon-wrapper {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
    }
    .delete-icon-wrapper i {
      font-size: 32px;
      color: #DC2626;
    }
    .swal2-popup.delete-popup .swal2-html-container {
      margin: 0;
      padding: 0;
    }
    .delete-title {
      font-size: 1.15rem;
      font-weight: 700;
      color: #1B2559;
      margin-bottom: .375rem;
    }
    .delete-text {
      font-size: .8125rem;
      color: #64748b;
      line-height: 1.6;
      margin-bottom: 1.5rem;
    }
    .delete-text strong {
      color: #334155;
      font-weight: 600;
    }
    .delete-actions {
      display: flex;
      gap: .75rem;
    }
    .delete-actions .btn-delete-cancel,
    .delete-actions .btn-delete-confirm {
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
    .delete-actions .btn-delete-cancel {
      background: #f1f5f9;
      color: #475569;
    }
    .delete-actions .btn-delete-cancel:hover {
      background: #e2e8f0;
      color: #1e293b;
    }
    .delete-actions .btn-delete-confirm {
      background: #DC2626;
      color: #fff;
      box-shadow: 0 4px 12px rgba(220,38,38,.3);
    }
    .delete-actions .btn-delete-confirm:hover {
      background: #b91c1c;
      box-shadow: 0 4px 16px rgba(220,38,38,.4);
      transform: translateY(-1px);
    }
    [data-theme="dark"] .swal2-popup.delete-popup {
      background: #1e293b;
      border-color: rgba(255,255,255,.08);
    }
    [data-theme="dark"] .delete-title { color: #e2e8f0; }
    [data-theme="dark"] .delete-text { color: #94a3b8; }
    [data-theme="dark"] .delete-text strong { color: #cbd5e1; }
    [data-theme="dark"] .delete-icon-wrapper {
      background: linear-gradient(135deg, rgba(220,38,38,.15) 0%, rgba(220,38,38,.25) 100%);
    }
    [data-theme="dark"] .delete-actions .btn-delete-cancel {
      background: #334155;
      color: #cbd5e1;
    }
    [data-theme="dark"] .delete-actions .btn-delete-cancel:hover {
      background: #475569;
      color: #f1f5f9;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sidebarLogoutBtn = document.getElementById('sidebar-logout-btn');
      const sidebarLogoutForm = document.getElementById('sidebar-logout-form');
      const profileLogoutBtn = document.getElementById('profile-logout-btn');
      const profileLogoutForm = document.getElementById('profile-logout-form');

      function handleLogout(btn, form) {
        if (!btn || !form) return;
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          Swal.fire({
            html: `
              <div class="logout-icon-wrapper">
                <i class="ri-shut-down-line"></i>
              </div>
              <div class="logout-title">Konfirmasi Logout</div>
              <div class="logout-text">Anda akan keluar dari sesi ini.<br>Apakah Anda yakin ingin melanjutkan?</div>
              <div class="logout-actions">
                <button type="button" class="btn-logout-cancel" id="swal-cancel-btn">
                  <i class="ri-arrow-left-line"></i> Batal
                </button>
                <button type="button" class="btn-logout-confirm" id="swal-confirm-btn">
                  <i class="ri-shut-down-line"></i> Ya, Logout
                </button>
              </div>
            `,
            showConfirmButton: false,
            showCancelButton: false,
            showCloseButton: false,
            customClass: {
              popup: 'logout-popup',
            },
            didOpen: (popup) => {
              const cancelBtn = popup.querySelector('#swal-cancel-btn');
              const confirmBtn = popup.querySelector('#swal-confirm-btn');

              cancelBtn.addEventListener('click', () => Swal.close());

              confirmBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging out...';
                
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.6';
                form.submit();
              });
            }
          });
        });
      }

      handleLogout(sidebarLogoutBtn, sidebarLogoutForm);
      handleLogout(profileLogoutBtn, profileLogoutForm);
    });
  </script>

  @stack('scripts')
</body>

</html>
