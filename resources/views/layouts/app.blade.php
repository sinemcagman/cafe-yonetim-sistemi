<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
@include('layouts.partials.head')
</head>

<body>
  <div class="page-layout">

@include('layouts.partials.header')

@include('layouts.partials.sidebar')

    <main class="app-wrapper">

      <div class="container">

        <div class="app-page-head d-flex flex-wrap gap-3 align-items-center justify-content-between">
          <div class="clearfix">
            <h1 class="app-page-title">@yield('page-title', View::yieldContent('title'))</h1>
            @hasSection('breadcrumb')
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">Panel</a>
                  </li>
                  @yield('breadcrumb')
                </ol>
              </nav>
            @endif
          </div>
          @yield('page-actions')
        </div>

        @include('layouts.partials.alerts')

        @yield('content')

      </div>

    </main>

@include('layouts.partials.footer')

  </div>

  @stack('modals')

  <!-- begin::GXON Page Scripts -->
  <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
  @stack('vendor-scripts')
  <script src="{{ asset('assets/js/appSettings.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  @stack('scripts')
  <!-- end::GXON Page Scripts -->
</body>

</html>
