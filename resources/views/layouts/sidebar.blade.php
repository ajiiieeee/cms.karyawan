<aside class="sidebar">
  <button type="button" class="sidebar-close-btn"><iconify-icon icon="radix-icons:cross-2"></iconify-icon></button>
  <div><div class="sidebar-logo d-flex align-items-center justify-content-between">
    <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/images/logo.png') }}" alt="Portal Karyawan" class="light-logo"><img src="{{ asset('assets/images/logo-light.png') }}" alt="Portal Karyawan" class="dark-logo"><img src="{{ asset('assets/images/logo-icon.png') }}" alt="Portal Karyawan" class="logo-icon"></a>
    <button type="button" class="text-xxl d-xl-flex d-none line-height-1 sidebar-toggle text-neutral-500"><i class="ri-contract-left-line"></i></button>
  </div></div>
  <div class="mx-16 py-12"><div class="dropdown profile-dropdown"><button type="button" class="profile-dropdown__button d-flex align-items-center justify-content-between p-10 w-100 overflow-hidden bg-neutral-50 radius-12" data-bs-toggle="dropdown"><span class="d-flex align-items-start gap-10"><img src="{{ asset('assets/images/thumbs/leave-request-img2.png') }}" alt="Profile" class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0"><span class="profile-dropdown__contents"><span class="h6 mb-0 text-md d-block text-primary-light">{{ auth()->user()->nama }}</span><span class="text-secondary-light text-sm mb-0 d-block">Karyawan</span></span></span><span class="profile-dropdown__icon pe-8 text-xl d-flex line-height-1"><i class="ri-arrow-right-s-line"></i></span></button><ul class="dropdown-menu dropdown-menu-lg-end border p-12"><li><a href="{{ route('personal-data.edit') }}" class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6"><i class="ri-user-3-line"></i>Profil Saya</a></li><li><a href="javascript:void(0)" onclick="document.getElementById('logout-form').submit()" class="dropdown-item rounded text-danger-600 bg-hover-neutral-200 d-flex align-items-center gap-2 py-6"><i class="ri-shut-down-line"></i>Keluar</a></li></ul></div></div>
  <div class="sidebar-menu-area"><ul class="sidebar-menu" id="sidebar-menu">
    <li><a href="{{ route('dashboard') }}"><i class="ri-home-4-line"></i><span>Dashboard</span></a></li>
    <li><a href="{{ route('personal-data.edit') }}"><i class="ri-user-3-line"></i><span>Data Pribadi</span></a></li>
    <li><a href="{{ route('leave-requests.index') }}"><i class="ri-calendar-check-line"></i><span>Form Izin Cuti</span></a></li>
    <li><a href="{{ route('fimp.index') }}"><i class="ri-file-list-3-line"></i><span>FIMP</span></a></li>
    <li><a href="{{ route('overtime-requests.index') }}"><i class="ri-time-line"></i><span>Permintaan Lembur</span></a></li>
    <li><a href="{{ route('payroll.index') }}"><i class="ri-wallet-3-line"></i><span>Penggajian</span></a></li>
    <li><a href="{{ route('announcements.index') }}"><i class="ri-megaphone-line"></i><span>Pengumuman</span></a></li>
    <li><a href="javascript:void(0)" onclick="document.getElementById('logout-form').submit()"><i class="ri-shut-down-line text-danger-600"></i><span class="text-danger-600">Keluar</span></a></li>
  </ul><form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form></div>
</aside>

