@extends('layouts.app')

@section('title', 'Yetkisiz Erişim')

@section('content')
  <div class="card">
    <div class="card-body text-center py-5">
      <div class="avatar avatar-lg bg-danger-subtle text-danger rounded-circle mx-auto mb-3">
        <i class="fi fi-rr-lock"></i>
      </div>
      <h5 class="mb-1">Bu sayfaya erişim yetkiniz yok</h5>
      <p class="mb-4">{{ $exception->getMessage() ?: 'Rolünüz bu işlem için yeterli değil.' }}</p>
      <a href="{{ route('dashboard') }}" class="btn btn-primary waves-effect waves-light">Panele Dön</a>
    </div>
  </div>
@endsection
