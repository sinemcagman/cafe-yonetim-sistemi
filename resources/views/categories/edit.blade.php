@extends('layouts.app')

@section('title', 'Kategori Düzenle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Kategoriler</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('categories.update', $category) }}">
        @method('PUT')
        @include('categories._form')
      </form>
    </div>
  </div>
@endsection
