@extends('layouts/layoutMaster')

@section('title', 'Masa Ekle')

@section('content')
<x-page-header title="Masa Ekle" icon="tabler-armchair">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('tables.index') }}">Masalar</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">Yeni</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="row">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <x-form-errors />
        <form method="POST" action="{{ route('tables.store') }}">
          @include('tables._form')
          <button type="submit" class="btn btn-primary me-3">Kaydet</button>
          <a href="{{ route('tables.index') }}" class="btn btn-label-secondary">Vazgeç</a>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
