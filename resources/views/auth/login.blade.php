@extends('layouts.auth')

@section('title', 'Giriş Yap')

@section('content')
  <div class="text-center mb-4">
    <h5 class="mb-1">Hoş geldiniz</h5>
    <p>Devam etmek için çalışan hesabınızla giriş yapın.</p>
  </div>
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-4">
      <label class="form-label" for="loginEmail">E-posta Adresi</label>
      <input type="email" name="email" value="{{ old('email') }}" @class(['form-control', 'is-invalid' => $errors->has('email')]) id="loginEmail" placeholder="ornek@kafe.test" required autofocus>
      @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="mb-4">
      <label class="form-label" for="loginPassword">Şifre</label>
      <input type="password" name="password" @class(['form-control', 'is-invalid' => $errors->has('password')]) id="loginPassword" placeholder="********" required>
      @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="mb-4">
      <div class="form-check mb-0">
        <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe" @checked(old('remember'))>
        <label class="form-check-label" for="rememberMe"> Beni hatırla </label>
      </div>
    </div>
    <div class="mb-3">
      <button type="submit" class="btn btn-primary waves-effect waves-light w-100">Giriş Yap</button>
    </div>
  </form>
@endsection
