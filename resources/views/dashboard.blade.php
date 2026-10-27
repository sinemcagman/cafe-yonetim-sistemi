@extends('layouts/layoutMaster')

@section('title', 'Panel')

@php
  $user = auth()->user();
  $occupied = $stats['total_tables'] - $stats['available_tables'];
  $occupancy = $stats['total_tables'] > 0 ? round($occupied / $stats['total_tables'] * 100) : 0;
@endphp

@section('content')
<div class="row g-6">

  <!-- Karşılama -->
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="d-flex align-items-end row h-100">
        <div class="col-7">
          <div class="card-body">
            <h5 class="card-title mb-1 text-nowrap">Merhaba, {{ \Illuminate\Support\Str::before($user->name, ' ') }}! 👋</h5>
            <p class="mb-2">
              <span class="badge bg-label-{{ $user->role->color() }}">{{ $user->role->label() }}</span>
            </p>
            <h4 class="text-primary mb-1">{{ $stats['available_tables'] }} boş masa</h4>
            <p class="small text-body-secondary mb-3">Doluluk oranı %{{ $occupancy }}</p>
            <a href="{{ route('tables.index') }}" class="btn btn-primary btn-sm">
              <i class="icon-base ti tabler-armchair icon-16px me-1"></i> Masalara Git
            </a>
          </div>
        </div>
        <div class="col-5 text-center">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="{{ asset('assets/img/illustrations/barista-' . Helper::appClasses()['theme'] . '.svg') }}" height="150" alt=""
              class="img-fluid" data-app-light-img="illustrations/barista-light.svg"
              data-app-dark-img="illustrations/barista-dark.svg" />
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Karşılama -->

  <!-- Günün özeti -->
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">Günün Özeti</h5>
        <small class="text-body-secondary">{{ now()->translatedFormat('d F Y') }}</small>
      </div>
      <div class="card-body d-flex align-items-end">
        <div class="w-100">
          <div class="row gy-3">
            <div class="col-md-3 col-6">
              <div class="d-flex align-items-center">
                <div class="badge rounded bg-label-success me-4 p-2"><i class="icon-base ti tabler-armchair icon-lg"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $stats['available_tables'] }}<small class="text-body-secondary fs-6"> / {{ $stats['total_tables'] }}</small></h5>
                  <small>Boş masa</small>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="d-flex align-items-center">
                <div class="badge rounded bg-label-info me-4 p-2"><i class="icon-base ti tabler-receipt icon-lg"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $stats['open_orders'] }}</h5>
                  <small>Açık sipariş</small>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="d-flex align-items-center">
                <div class="badge rounded bg-label-primary me-4 p-2"><i class="icon-base ti tabler-cash icon-lg"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ money($stats['today_revenue']) }}</h5>
                  <small>Bugünkü tahsilat</small>
                </div>
              </div>
            </div>
            <div class="col-md-3 col-6">
              <div class="d-flex align-items-center">
                <div class="badge rounded bg-label-{{ $stats['low_stock'] > 0 ? 'danger' : 'success' }} me-4 p-2"><i class="icon-base ti tabler-package icon-lg"></i></div>
                <div class="card-info">
                  <h5 class="mb-0">{{ $stats['low_stock'] }}</h5>
                  <small>Kritik stok</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Günün özeti -->

  <!-- Masa durumu -->
  <div @class(['col-xxl-8' => $user->isManager(), 'col-12' => ! $user->isManager()])>
    <div class="card h-100">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h5 class="card-title mb-1">Masa Durumu</h5>
          <div class="d-flex gap-4 small">
            <span><span class="badge badge-dot bg-success me-1"></span>Boş ({{ $stats['available_tables'] }})</span>
            <span><span class="badge badge-dot bg-danger me-1"></span>Kullanımda ({{ $occupied }})</span>
          </div>
        </div>
        <a href="{{ route('tables.index') }}" class="btn btn-sm btn-label-primary">Tüm Masalar</a>
      </div>
      <div class="card-body">
        <div class="progress mb-6" style="height: 8px" role="progressbar" aria-label="Doluluk oranı"
          aria-valuenow="{{ $occupancy }}" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar bg-danger" style="width: {{ $occupancy }}%"></div>
        </div>
        @if ($tables->isEmpty())
          <x-empty-state icon="tabler-armchair" title="Henüz masa yok" text="Masaları tanımladığınızda doluluk durumu burada görünür.">
            @if ($user->isManager())
              <a href="{{ route('tables.create') }}" class="btn btn-primary btn-sm">Masa Ekle</a>
            @endif
          </x-empty-state>
        @else
          <div class="row g-3">
            @foreach ($tables as $table)
              <div class="col-4 col-sm-3 col-lg-2">
                <a href="{{ route('tables.index') }}#masa-{{ $table->number }}"
                  class="d-block text-center rounded-3 p-3 border {{ $table->isAvailable() ? 'bg-label-success border-success' : 'bg-label-danger border-danger' }}"
                  data-bs-toggle="tooltip" title="{{ $table->status->label() }} · {{ $table->capacity }} kişilik">
                  <i class="icon-base ti tabler-armchair icon-24px d-block mx-auto mb-1"></i>
                  <span class="fw-medium">{{ $table->number }}</span>
                </a>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
  <!--/ Masa durumu -->

  @if ($user->isManager())
  <!-- Kritik stok -->
  <div class="col-xxl-4 col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-1">Kritik Stok</h5>
          <p class="card-subtitle mb-0">Adedi azalan ürünler</p>
        </div>
        <a href="{{ route('products.index', ['kritik' => 1]) }}" class="btn btn-sm btn-label-primary">Tümü</a>
      </div>
      <div class="card-body">
        @forelse ($lowStockProducts as $product)
          <div class="d-flex align-items-center {{ $loop->last ? '' : 'mb-4' }}">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-{{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">
                <i class="icon-base ti tabler-package icon-md"></i>
              </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">{{ $product->name }}</h6>
                <small class="text-body-secondary">{{ $product->category->name }} · eşik {{ $product->low_stock_threshold }}</small>
              </div>
              <span class="badge bg-label-{{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">
                {{ $product->stock_quantity === 0 ? 'Tükendi' : $product->stock_quantity.' adet' }}
              </span>
            </div>
          </div>
        @empty
          <x-empty-state icon="tabler-circle-check" title="Her şey yolunda" text="Tüm ürünlerin adedi yeterli." class="py-6" />
        @endforelse
      </div>
    </div>
  </div>
  <!--/ Kritik stok -->

  <!-- Son işlemler -->
  <div class="col-xxl-12 col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-1">Son İşlemler</h5>
        <p class="card-subtitle mb-0">Çalışanların sistemde yaptığı son değişiklikler</p>
      </div>
      <div class="card-body pt-2">
        @if ($recentActivities->isEmpty())
          <x-empty-state icon="tabler-history" title="Henüz işlem yok" class="py-6" />
        @else
          <ul class="timeline mb-0">
            @foreach ($recentActivities as $activity)
              <li class="timeline-item timeline-item-transparent {{ $loop->last ? 'border-transparent pb-0' : '' }}">
                <span class="timeline-point timeline-point-{{ $activity->color() }}"></span>
                <div class="timeline-event {{ $loop->last ? 'pb-0' : '' }}">
                  <div class="timeline-header mb-1">
                    <h6 class="mb-0">{{ $activity->description }}</h6>
                    <small class="text-body-secondary" title="{{ $activity->created_at->format('d.m.Y H:i') }}">{{ $activity->created_at->diffForHumans() }}</small>
                  </div>
                  <p class="mb-0 small text-body-secondary">
                    <i class="icon-base ti tabler-user icon-xs me-1"></i>{{ $activity->user?->name ?? 'Sistem' }}
                  </p>
                </div>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
  <!--/ Son işlemler -->
  @endif

</div>
@endsection
