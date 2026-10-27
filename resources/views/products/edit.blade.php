@extends('layouts/layoutMaster')

@section('title', 'Ürün Düzenle')

@section('content')
<form method="POST" action="{{ route('products.update', $product) }}">
  @method('PUT')
  <x-page-header :title="$product->name" :subtitle="$product->category->name" icon="tabler-coffee">
    <x-slot:breadcrumb>
      <li class="breadcrumb-item">
        <a href="{{ route('products.index') }}">Ürünler</a>
        <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
      </li>
      <li class="breadcrumb-item active">Düzenle</li>
    </x-slot:breadcrumb>
    <x-slot:actions>
      <a href="{{ route('stock-movements.index', ['urun' => $product->id]) }}" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-history icon-16px me-1"></i> Stok Geçmişi
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="icon-base ti tabler-device-floppy icon-16px me-1"></i> Değişiklikleri Kaydet
      </button>
    </x-slot:actions>
  </x-page-header>

  @include('products._form')
</form>
@endsection
