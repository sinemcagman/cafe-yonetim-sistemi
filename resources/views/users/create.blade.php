@extends('layouts/layoutMaster')

@section('title', 'Çalışan Ekle')

@section('content')
<form method="POST" action="{{ route('users.store') }}" autocomplete="off">
  <x-page-header title="Yeni Çalışan" subtitle="Hesap oluşturulduktan sonra giriş bilgilerini çalışana iletin." icon="tabler-user-plus">
    <x-slot:breadcrumb>
      <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">Çalışanlar</a>
        <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
      </li>
      <li class="breadcrumb-item active">Yeni</li>
    </x-slot:breadcrumb>
    <x-slot:actions>
      <a href="{{ route('users.index') }}" class="btn btn-label-secondary">Vazgeç</a>
      <button type="submit" class="btn btn-primary">
        <i class="icon-base ti tabler-device-floppy icon-16px me-1"></i> Çalışanı Kaydet
      </button>
    </x-slot:actions>
  </x-page-header>

  @include('users._form')
</form>
@endsection
