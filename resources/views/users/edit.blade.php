@extends('layouts/layoutMaster')

@section('title', 'Çalışan Düzenle')

@section('content')
<form method="POST" action="{{ route('users.update', $user) }}" autocomplete="off">
  @method('PUT')
  <x-page-header :title="$user->name" :subtitle="$user->role->label().' · '.($user->is_active ? 'Aktif' : 'Pasif')" icon="tabler-user-edit">
    <x-slot:breadcrumb>
      <li class="breadcrumb-item">
        <a href="{{ route('users.index') }}">Çalışanlar</a>
        <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
      </li>
      <li class="breadcrumb-item active">Düzenle</li>
    </x-slot:breadcrumb>
    <x-slot:actions>
      <a href="{{ route('activity-logs.index', ['calisan' => $user->id]) }}" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-history icon-16px me-1"></i> İşlem Geçmişi
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="icon-base ti tabler-device-floppy icon-16px me-1"></i> Değişiklikleri Kaydet
      </button>
    </x-slot:actions>
  </x-page-header>

  @include('users._form')
</form>
@endsection
