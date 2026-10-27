@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
$demoAccounts = [
  ['role' => \App\Enums\UserRole::Yonetici, 'email' => 'yonetici@kafe.test', 'icon' => 'tabler-user-shield'],
  ['role' => \App\Enums\UserRole::Garson, 'email' => 'garson@kafe.test', 'icon' => 'tabler-tools-kitchen-2'],
  ['role' => \App\Enums\UserRole::Kasiyer, 'email' => 'kasiyer@kafe.test', 'icon' => 'tabler-cash-register'],
];
@endphp

@extends('layouts/blankLayout')

@section('title', 'Giriş Yap')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="{{ route('login') }}" class="app-brand auth-cover-brand">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- Sol görsel -->
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        {{-- İllüstrasyon: unDraw "Barista" (undraw.co, ücretsiz lisans), kafe renklerine boyandı --}}
        <img src="{{ asset('assets/img/illustrations/barista-' . $configData['theme'] . '.svg') }}"
          alt="Kahve servis eden barista" class="my-5 auth-illustration" style="max-height: 62%"
          data-app-light-img="illustrations/barista-light.svg"
          data-app-dark-img="illustrations/barista-dark.svg" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Sol görsel -->

    <!-- Giriş formu -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 pt-5">
        <h4 class="mb-1">Hoş geldiniz! ☕</h4>
        <p class="mb-6">Siparişleri ve masaları yönetmek için çalışan hesabınızla giriş yapın.</p>

        <form id="formAuthentication" class="mb-6" action="{{ route('login') }}" method="POST" novalidate>
          @csrf
          <div class="mb-6">
            <label for="email" class="form-label">E-posta adresi</label>
            <input type="email" @class(['form-control', 'is-invalid' => $errors->has('email')]) id="email" name="email"
              value="{{ old('email') }}" placeholder="ornek@kafe.test" autocomplete="username" autofocus required />
            @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password">Şifre</label>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="password" @class(['form-control', 'is-invalid' => $errors->has('password')])
                name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                autocomplete="current-password" required />
              <span class="input-group-text cursor-pointer" title="Şifreyi göster/gizle"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
            @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="my-8">
            <div class="form-check mb-0 ms-2">
              <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember')) />
              <label class="form-check-label" for="remember"> Beni hatırla </label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary d-grid w-100">Giriş Yap</button>
        </form>

        @if (app()->isLocal())
        <div class="divider my-6">
          <div class="divider-text">Demo hesaplar</div>
        </div>
        <p class="small text-body-secondary text-center mb-3">Bir role tıklayın, bilgiler otomatik dolsun (şifre: <code>password</code>).</p>
        <div class="row g-2">
          @foreach ($demoAccounts as $account)
          <div class="col-4">
            <button type="button" class="btn btn-outline-{{ $account['role']->color() }} w-100 d-flex flex-column align-items-center py-3"
              data-demo-email="{{ $account['email'] }}">
              <i class="icon-base ti {{ $account['icon'] }} icon-24px mb-1"></i>
              <small>{{ $account['role']->label() }}</small>
            </button>
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>
    <!-- /Giriş formu -->
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-demo-email]').forEach(button => {
    button.addEventListener('click', () => {
      document.getElementById('email').value = button.dataset.demoEmail;
      document.getElementById('password').value = 'password';
      document.getElementById('formAuthentication').requestSubmit();
    });
  });
</script>
@endpush
