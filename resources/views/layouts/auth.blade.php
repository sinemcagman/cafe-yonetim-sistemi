<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
@include('layouts.partials.head')
</head>

<body>
  <div class="page-layout">

    <div class="auth-wrapper min-vh-100 px-2">
      <div class="row g-0 min-vh-100">
        <div class="col-xl-5 col-lg-6 mx-auto px-sm-4 align-self-center py-4">
          <div class="card card-body p-4 p-sm-5 maxw-450px m-auto rounded-4 auth-card" data-simplebar>
            <div class="mb-4 text-center">
              <img src="{{ asset('assets/images/logo.svg') }}" alt="{{ config('app.name') }}" class="mb-2">
              <div class="fw-bold text-dark fs-5">Kafe Yönetim</div>
            </div>
            @yield('content')
          </div>
        </div>
      </div>
    </div>

  </div>
  <!-- begin::GXON Page Scripts -->
  <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
  <script src="{{ asset('assets/js/appSettings.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
  <!-- end::GXON Page Scripts -->
</body>

</html>
