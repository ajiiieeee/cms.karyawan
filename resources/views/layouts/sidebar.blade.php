<aside class="sidebar">
  <button type="button" class="sidebar-close-btn">
    <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
  </button>
  <div class="">
    <div class="sidebar-logo d-flex align-items-center justify-content-between">
      <a href="{{ route('dashboard') }}" class="">
        <img src="{{ asset('assets/images/logo_cm.png') }}" alt="site logo" class="light-logo">
        <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
        <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
      </a>
      <button type="button" class="text-xxl d-xl-flex d-none line-height-1 sidebar-toggle text-neutral-500"
        aria-label="Collapse Sidebar">
        <i class="ri-contract-left-line"></i>
      </button>
    </div>
  </div>

  <!-- User Info start -->
  <div class="mx-16 py-12">
    <div class="dropdown profile-dropdown">
      <button type="button"
        class="profile-dropdown__button d-flex align-items-center justify-content-between p-10 w-100 overflow-hidden bg-neutral-50 radius-12"
        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
        <span class="d-flex align-items-start gap-10">
          <img src="{{ Auth::user()->foto ? asset('upload/foto-user/' . Auth::user()->foto) : asset('assets/images/profile-default.jpg') }}" alt="Thumbnail"
            class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0">
          <span class="profile-dropdown__contents">
            <span class="h6 mb-0 text-md d-block text-primary-light">{{ Auth::user()->nama }}</span>
            <span class="text-secondary-light text-sm mb-0 d-block">{{ Auth::user()->grup->nama_grup ?? '-' }}</span>
          </span>
        </span>
        <span class="profile-dropdown__icon pe-8 text-xl d-flex line-height-1">
          <i class="ri-arrow-right-s-line"></i>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
        <li>
          <a href="{{ route('dashboard') }}"
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-user-3-line"></i>
            My Profile
          </a>
        </li>
        <li>
          <a href="javascript:void(0)" id="profile-logout-btn"
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-shut-down-line"></i>
            Log Out
          </a>
          <form id="profile-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
            @csrf
          </form>
        </li>
      </ul>
    </div>
  </div>
  <!-- User Info end -->

  <div class="sidebar-menu-area">
    <ul class="sidebar-menu" id="sidebar-menu">
      <li>
        <a href="{{ route('dashboard') }}">
          <i class="ri-home-4-line"></i>
          <span>Dashboard</span>
        </a>
      </li>

      @php
        $user = Auth::user();
        $parentMenus = \App\Models\Menu::where('parent', 0)->orderBy('urutan')->get();
        $childMenus = \App\Models\Menu::where('parent', '>', 0)->orderBy('urutan')->get()->groupBy('parent');
      @endphp
      @foreach($parentMenus as $parent)
        @php
          $children = $childMenus->get($parent->id, collect());
          $hasAccessToParent = false;

          // Check if user has access to parent menu itself
          if ($parent->link && $user->hasMenuAccess($parent->link, 'view')) {
              $hasAccessToParent = true;
          }

          // Check if user has access to any child menu
          $accessibleChildren = $children->filter(function($child) use ($user) {
              return $child->link && $user->hasMenuAccess($child->link, 'view');
          });

          if (!$hasAccessToParent && $accessibleChildren->isEmpty()) {
              continue;
          }
        @endphp

        @if($accessibleChildren->isNotEmpty())
          {{-- Parent with children --}}
          <li class="dropdown">
            <a href="javascript:void(0)">
              @if($parent->icon)<i class="{{ $parent->icon }}"></i>@endif
              <span>{{ $parent->nama_menu }}</span>
            </a>
            <ul class="sidebar-submenu">
              @foreach($accessibleChildren as $child)
              <li>
                <a href="{{ route(str_replace('/', '.', $child->link) . '.index') }}">
                  <i class="ri-circle-fill circle-icon w-auto"></i>
                  {{ $child->nama_menu }}
                </a>
              </li>
              @endforeach
            </ul>
          </li>
        @elseif($hasAccessToParent)
          {{-- Parent without children, as direct link --}}
          <li>
            <a href="{{ route(str_replace('/', '.', $parent->link) . '.index') }}">
              @if($parent->icon)<i class="{{ $parent->icon }}"></i>@endif
              <span>{{ $parent->nama_menu }}</span>
            </a>
          </li>
        @endif
      @endforeach

      <!-- Logout Menu -->
      <li>
        <a href="javascript:void(0)" id="sidebar-logout-btn">
          <i class="ri-shut-down-line text-danger-600"></i>
          <span class="text-danger-600">Logout</span>
        </a>
        <form id="sidebar-logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
          @csrf
        </form>
      </li>
    </ul>
  </div>
</aside>
