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
          <li class="menu-heading">
            <span class="menu-label">Salon</span>
          </li>
          <li class="menu-item">
            <a @class(['menu-link', 'active' => request()->routeIs('tables.*')]) href="{{ route('tables.index') }}">
              <i class="fi fi-rr-restaurant"></i>
              <span class="menu-label">Masalar</span>
            </a>
          </li>
          @if (auth()->user()->isManager())
            <li class="menu-heading">
              <span class="menu-label">Menü Yönetimi</span>
            </li>
            <li class="menu-item">
              <a @class(['menu-link', 'active' => request()->routeIs('categories.*')]) href="{{ route('categories.index') }}">
                <i class="fi fi-rr-list"></i>
                <span class="menu-label">Kategoriler</span>
              </a>
            </li>
            <li class="menu-item">
              <a @class(['menu-link', 'active' => request()->routeIs('products.*')]) href="{{ route('products.index') }}">
                <i class="fi fi-rr-mug-hot-alt"></i>
                <span class="menu-label">Ürünler</span>
              </a>
            </li>
            <li class="menu-item">
              <a @class(['menu-link', 'active' => request()->routeIs('stock-movements.*')]) href="{{ route('stock-movements.index') }}">
                <i class="fi fi-rr-boxes"></i>
                <span class="menu-label">Stok Hareketleri</span>
              </a>
            </li>
          @endif
        </ul>
      </nav>
    </aside>
    <!-- end::GXON Sidebar Menu -->
