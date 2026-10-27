@extends('layouts/layoutMaster')

@section('title', 'Ürün Ekle')

@section('content')
<form method="POST" action="{{ route('products.store') }}">
  <x-page-header title="Yeni Ürün" subtitle="Menüye yeni bir ürün ekleyin." icon="tabler-coffee">
    <x-slot:breadcrumb>
      <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">Ürünler</a>
        <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
      </li>
      <li class="breadcrumb-item active">Yeni</li>
    </x-slot:breadcrumb>
    <x-slot:actions>
      <a href="{{ route('products.index') }}" class="btn btn-label-secondary">Vazgeç</a>
      <button type="submit" class="btn btn-primary">
        <i class="icon-base ti tabler-device-floppy icon-16px me-1"></i> Ürünü Kaydet
      </button>
    </x-slot:actions>
  </x-page-header>

  @include('products._form')
</form>
@endsection
