@extends('layouts.app')

@section('title', 'Masa Ekle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Masalar</a></li>
  <li class="breadcrumb-item active" aria-current="page">Yeni</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('tables.store') }}">
        @include('tables._form')
      </form>
    </div>
  </div>
@endsection
