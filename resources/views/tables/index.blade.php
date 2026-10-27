@extends('layouts.app')

@section('title', 'Masalar')

@section('breadcrumb')
  <li class="breadcrumb-item active" aria-current="page">Masalar</li>
@endsection

@section('page-actions')
  @if (auth()->user()->isManager())
    <a href="{{ route('tables.create') }}" class="btn btn-primary waves-effect waves-light">
      <i class="fi fi-rr-plus me-1"></i> Masa Ekle
    </a>
  @endif
@endsection

@section('content')
  <div class="card d-flex flex-row flex-wrap align-items-center gap-4 h-auto px-4 py-3 mb-4">
    <span class="d-flex align-items-center gap-2">
      <span class="badge rounded-circle bg-success p-1"><span class="visually-hidden">Boş</span></span>
      Boş: <strong>{{ $availableCount }}</strong>
    </span>
    <span class="d-flex align-items-center gap-2">
      <span class="badge rounded-circle bg-danger p-1"><span class="visually-hidden">Kullanımda</span></span>
      Kullanımda: <strong>{{ $occupiedCount }}</strong>
    </span>
  </div>

  @forelse ($areas as $area => $areaTables)
    <h6 class="mb-3">{{ $area }}</h6>
    <div class="row">
      @foreach ($areaTables as $table)
        @include('tables._card', ['table' => $table])
      @endforeach
    </div>
  @empty
    <div class="card">
      <div class="card-body text-center text-muted py-5">Henüz masa tanımlanmadı.</div>
    </div>
  @endforelse
@endsection
