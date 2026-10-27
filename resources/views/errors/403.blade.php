@extends('layouts/layoutMaster')

@section('title', 'Yetkisiz Erişim')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-misc.scss'])
@endsection

@section('content')
<div class="card">
  <div class="card-body text-center py-10">
    <h4 class="mb-2">Bu sayfaya erişim yetkiniz yok 🔐</h4>
    <p class="mb-6 mx-auto" style="max-width: 460px">
      {{ $exception->getMessage() ?: 'Rolünüz bu işlem için yeterli değil.' }}
      Bir yanlışlık olduğunu düşünüyorsanız yöneticinizle iletişime geçin.
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary mb-8">
      <i class="icon-base ti tabler-arrow-left icon-16px me-1"></i> Panele Dön
    </a>
    <div>
      <img src="{{ asset('assets/img/illustrations/page-misc-you-are-not-authorized.png') }}" alt="Yetkisiz erişim"
        width="170" class="img-fluid" />
    </div>
  </div>
</div>
@endsection
