@extends('layouts.app')

@section('title', 'Ürün Düzenle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Ürünler</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('products.update', $product) }}">
        @method('PUT')
        @include('products._form')
      </form>
    </div>
  </div>
@endsection
