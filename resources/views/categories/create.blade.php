@extends('layouts.app')

@section('title', 'Kategori Ekle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Kategoriler</a></li>
  <li class="breadcrumb-item active" aria-current="page">Yeni</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('categories.store') }}">
        @include('categories._form')
      </form>
    </div>
  </div>
@endsection
