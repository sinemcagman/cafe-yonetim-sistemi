@php
use Illuminate\Support\Facades\Auth;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
  <a href="{{ route('dashboard') }}" class="app-brand-link">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
  </a>
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
<div
  class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" aria-label="Menüyü aç/kapat">
    <i class="icon-base ti tabler-menu-2 icon-md"></i>
  </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <!-- Tarih ve saat -->
  <div class="navbar-nav align-items-center d-none d-md-flex">
    <div class="nav-item d-flex align-items-center gap-2 text-body">
      <i class="icon-base ti tabler-calendar-event icon-md"></i>
      <span>{{ now()->translatedFormat('d F Y, l') }}</span>
      <span class="badge bg-label-primary ms-1" data-live-clock>{{ now()->format('H:i') }}</span>
    </div>
  </div>

  <ul class="navbar-nav flex-row align-items-center gap-1 gap-sm-2 ms-auto">
    @auth
    <!-- Hızlı erişim: Masalar -->
    <li class="nav-item d-flex align-items-center me-1 me-sm-2">
      <a href="{{ route('tables.index') }}" class="btn btn-label-primary d-flex align-items-center gap-1 px-3" style="height: 38px">
        <i class="icon-base ti tabler-armchair icon-18px"></i>
        <span class="d-none d-sm-inline">Masalar</span>
      </a>
    </li>
    @endauth

    @if ($configData['hasCustomizer'] == true)
    <!-- Tema değiştirici -->
    <li class="nav-item dropdown d-flex align-items-center">
      <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill" id="nav-theme"
        href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Tema seç">
        <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
        <span class="d-none ms-2" id="nav-theme-text">Tema</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
        <li>
          <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
            aria-pressed="false">
            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Açık</span>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Koyu</span>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system"
            aria-pressed="false">
            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                data-icon="device-desktop-analytics"></i>Sistem</span>
          </button>
        </li>
      </ul>
    </li>
    <!-- / Tema değiştirici -->
    @endif

    @if (Auth::user()?->isManager())
    <!-- Kritik stok bildirimleri -->
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown d-flex align-items-center">
      <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
        href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
        aria-label="Kritik stok bildirimleri">
        <span class="position-relative">
          <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
          @if ($lowStockProducts->isNotEmpty())
          <span class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
          @endif
        </span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end p-0">
        <li class="dropdown-menu-header border-bottom">
          <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Kritik Stok</h6>
            <span class="badge bg-label-{{ $lowStockProducts->isNotEmpty() ? 'danger' : 'success' }}">
              {{ $lowStockTotal }} ürün
            </span>
          </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
          <ul class="list-group list-group-flush">
            @forelse ($lowStockProducts as $product)
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
              <a href="{{ route('products.index', ['ara' => $product->name]) }}" class="d-flex text-reset">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar">
                    <span class="avatar-initial rounded-circle bg-label-{{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">
                      <i class="icon-base ti tabler-package"></i>
                    </span>
                  </div>
                </div>
                <div class="flex-grow-1">
                  <h6 class="small mb-1">{{ $product->name }}</h6>
                  <small class="mb-1 d-block text-body">
                    {{ $product->stock_quantity === 0 ? 'Stokta kalmadı' : "Son {$product->stock_quantity} adet" }}
                  </small>
                  <small class="text-body-secondary">{{ $product->category->name }}</small>
                </div>
              </a>
            </li>
            @empty
            <li class="list-group-item text-center py-6">
              <i class="icon-base ti tabler-circle-check icon-32px text-success mb-2"></i>
              <p class="mb-0 small">Tüm ürünlerin adedi yeterli.</p>
            </li>
            @endforelse
          </ul>
        </li>
        @if ($lowStockProducts->isNotEmpty())
        <li class="border-top">
          <div class="d-grid p-4">
            <a class="btn btn-primary btn-sm d-flex" href="{{ route('products.index', ['kritik' => 1]) }}">
              <small class="align-middle">Tüm kritik ürünleri gör</small>
            </a>
          </div>
        </li>
        @endif
      </ul>
    </li>
    <!--/ Kritik stok bildirimleri -->
    @endif

    @auth
    <!-- User -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown d-flex align-items-center ms-1">
      <a class="nav-link dropdown-toggle hide-arrow p-0 d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="dropdown"
        aria-label="Hesap menüsü">
        <div class="avatar avatar-online">
          <span class="avatar-initial rounded-circle bg-label-{{ Auth::user()->role->color() }}">{{ Auth::user()->initials() }}</span>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <div class="dropdown-item mt-0">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <span class="avatar-initial rounded-circle bg-label-{{ Auth::user()->role->color() }}">{{ Auth::user()->initials() }}</span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <small class="text-body-secondary">{{ Auth::user()->role->label() }}</small>
              </div>
            </div>
          </div>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item" href="{{ route('dashboard') }}">
            <i class="icon-base ti tabler-smart-home me-3 icon-md"></i><span class="align-middle">Panel</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="{{ route('tables.index') }}">
            <i class="icon-base ti tabler-armchair me-3 icon-md"></i><span class="align-middle">Masalar</span>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <div class="d-grid px-2 pt-2 pb-1">
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger d-flex w-100 justify-content-center">
                <small class="align-middle">Çıkış Yap</small>
                <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
              </button>
            </form>
          </div>
        </li>
      </ul>
    </li>
    <!--/ User -->
    @endauth
  </ul>
</div>
