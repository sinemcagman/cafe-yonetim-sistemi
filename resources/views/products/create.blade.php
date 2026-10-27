@extends('layouts.app')

@section('title', 'Ürün Ekle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Ürünler</a></li>
  <li class="breadcrumb-item active" aria-current="page">Yeni</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('products.store') }}">
        @include('products._form')
      </form>
    </div>
  </div>
@endsection
