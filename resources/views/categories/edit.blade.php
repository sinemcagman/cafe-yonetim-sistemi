@extends('layouts/layoutMaster')

@section('title', 'Kategori Düzenle')

@section('content')
<x-page-header :title="$category->name" subtitle="Kategoriyi düzenleyin" icon="tabler-category">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('categories.index') }}">Kategoriler</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">Düzenle</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <x-form-errors />
        <form method="POST" action="{{ route('categories.update', $category) }}">
          @method('PUT')
          @include('categories._form')
          <button type="submit" class="btn btn-primary me-3">Kaydet</button>
          <a href="{{ route('categories.index') }}" class="btn btn-label-secondary">Vazgeç</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
