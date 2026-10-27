@extends('layouts.app')

@section('title', 'Masa Düzenle')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('tables.index') }}">Masalar</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $table->label() }}</li>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('tables.update', $table) }}">
        @method('PUT')
        @include('tables._form')
      </form>
    </div>
  </div>
@endsection
