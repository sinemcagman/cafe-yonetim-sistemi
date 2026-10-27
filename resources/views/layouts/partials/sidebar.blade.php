    <!-- begin::GXON Sidebar Menu -->
    <aside class="app-menubar" id="appMenubar">
      <div class="app-navbar-brand">
        <a class="navbar-brand-logo" href="{{ route('dashboard') }}">
          <img src="{{ asset('assets/images/logo.svg') }}" alt="{{ config('app.name') }}">
        </a>
        <a class="navbar-brand-mini" href="{{ route('dashboard') }}">
          <span class="fw-bold text-dark fs-5">Kafe Yönetim</span>
        </a>
      </div>
      <nav class="app-navbar" data-simplebar>
        <ul class="menubar">
          <li class="menu-item">
            <a @class(['menu-link', 'active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}">
              <i class="fi fi-rr-apps"></i>
              <span class="menu-label">Panel</span>
            </a>
          </li>
        </ul>
      </nav>
    </aside>
    <!-- end::GXON Sidebar Menu -->
