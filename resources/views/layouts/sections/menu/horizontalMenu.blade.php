@php
  use Illuminate\Support\Facades\Route;
  $configData = Helper::appClasses();
@endphp
<!-- Horizontal Menu -->
<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
  {{ $attribute }}="{{ $value }}" @endforeach>
  <div class="{{ $containerNav }} d-flex h-100">
    <ul class="menu-inner">
      @foreach ($menuData[1]->menu as $menu)
        {{-- active menu method --}}
        @php
          $activeClass = request()->routeIs(...(array) $menu->slug) ? 'active' : null;
          $href = isset($menu->route) ? route($menu->route) : (isset($menu->url) ? url($menu->url) : 'javascript:void(0);');
        @endphp

        {{-- main menu --}}
        <li class="menu-item {{ $activeClass }}">
          <a href="{{ $href }}"
            class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
            @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
            @isset($menu->icon)
              <i class="{{ $menu->icon }}"></i>
            @endisset
            <div>{{ isset($menu->name) ? __($menu->name) : '' }}</div>
          </a>

          {{-- submenu --}}
          @isset($menu->submenu)
            @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
          @endisset
        </li>
      @endforeach
    </ul>
  </div>
</aside>
<!--/ Horizontal Menu -->
