@extends('layouts/layoutMaster')

@section('title', 'Siparişler')

@php
  use App\Enums\OrderType;
  $canTake = auth()->user()->canTakeOrders();
  $typeIcons = [
    OrderType::Masa->value => 'tabler-armchair',
    OrderType::Paket->value => 'tabler-package',
    OrderType::GelAl->value => 'tabler-walk',
  ];
  $filtered = collect(['tur', 'ara', 'tarih'])->contains(fn ($key) => request()->filled($key));
@endphp

@section('content')
<x-page-header title="Siparişler" subtitle="Açık siparişleri takip edin, geçmiş siparişlere göz atın." icon="tabler-receipt">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Siparişler</li>
  </x-slot:breadcrumb>
  @if ($canTake)
    <x-slot:actions>
      <button type="button" class="btn btn-label-primary" data-open-order="{{ OrderType::Paket->value }}">
        <i class="icon-base ti tabler-package icon-16px me-1"></i> Paket / Gel-Al
      </button>
      <button type="button" class="btn btn-primary" data-open-order="{{ OrderType::Masa->value }}">
        <i class="icon-base ti tabler-plus icon-16px me-1"></i> Yeni Sipariş
      </button>
    </x-slot:actions>
  @endif
</x-page-header>

<!-- Sekmeler -->
<div class="nav-align-top mb-6">
  <ul class="nav nav-pills flex-wrap gap-2">
    <li class="nav-item">
      <a href="{{ route('orders.index', request()->only('tur')) }}" @class(['nav-link', 'active' => $tab === 'acik'])>
        <i class="icon-base ti tabler-clock-hour-4 icon-sm me-1_5"></i> Açık
        <span class="badge rounded-pill bg-label-primary ms-1_5">{{ $openOrders->count() }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('orders.index', ['gorunum' => 'gecmis'] + request()->only('tur')) }}" @class(['nav-link', 'active' => $tab === 'gecmis'])>
        <i class="icon-base ti tabler-history icon-sm me-1_5"></i> Geçmiş
      </a>
    </li>
  </ul>
</div>

<!-- Filtre -->
<div class="card mb-6">
  <div class="card-body">
    <form method="GET" action="{{ route('orders.index') }}" class="row g-4 align-items-end">
      <input type="hidden" name="gorunum" value="{{ $tab === 'gecmis' ? 'gecmis' : '' }}">
      <div class="col-md-3">
        <label class="form-label" for="filter-order-no">Sipariş no</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text">#</span>
          <input type="search" name="ara" id="filter-order-no" value="{{ request('ara') }}" class="form-control" placeholder="Örn. 12" inputmode="numeric">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-order-type">Tür</label>
        <select name="tur" id="filter-order-type" class="select2 form-select" data-placeholder="Tüm türler"
          data-allow-clear="true" data-search="false" data-auto-submit>
          <option value=""></option>
          @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(request('tur') === $type->value)>{{ $type->label() }}</option>
          @endforeach
        </select>
      </div>
      @if ($tab === 'gecmis')
        <div class="col-md-3">
          <label class="form-label" for="filter-order-date">Tarih aralığı</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
            <input type="text" name="tarih" id="filter-order-date" class="form-control" value="{{ request('tarih') }}"
              placeholder="Tarih seçin" data-datepicker="range" data-max-date="{{ today()->toDateString() }}" data-auto-submit>
          </div>
        </div>
      @endif
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-filter icon-16px me-1"></i> Filtrele</button>
        @if ($filtered)
          <a href="{{ route('orders.index', $tab === 'gecmis' ? ['gorunum' => 'gecmis'] : []) }}" class="btn btn-label-secondary">Temizle</a>
        @endif
      </div>
    </form>
  </div>
</div>

@if ($tab === 'acik')
  @if ($openOrders->isEmpty())
    <div class="card">
      <x-empty-state icon="tabler-receipt" title="{{ $filtered ? 'Filtreye uyan açık sipariş yok' : 'Açık sipariş yok' }}"
        text="Yeni bir masa, paket ya da gel-al siparişi açabilirsiniz.">
        @if ($canTake)
          <button type="button" class="btn btn-primary" data-open-order="{{ OrderType::Masa->value }}">Sipariş Aç</button>
        @endif
      </x-empty-state>
    </div>
  @else
    <div class="row g-4">
      @foreach ($openOrders as $order)
        <div class="col-sm-6 col-xl-4 col-xxl-3">
          <a href="{{ route('orders.show', $order) }}" class="card h-100 text-reset border-0 border-top border-3 border-{{ $order->status->color() }}">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-{{ $order->type->color() }}"><i class="icon-base ti {{ $typeIcons[$order->type->value] }} icon-22px"></i></span>
                </div>
                <x-status-badge :status="$order->status" />
              </div>
              <h5 class="mb-1">{{ $order->label() }}</h5>
              <small class="text-body-secondary mb-3">
                <i class="icon-base ti tabler-clock icon-12px me-1"></i>{{ $order->opened_at->diffForHumans() }} · {{ $order->user->name }}
              </small>
              @if ($order->note)
                <p class="small mb-3 text-truncate" title="{{ $order->note }}"><i class="icon-base ti tabler-note icon-14px me-1"></i>{{ $order->note }}</p>
              @endif
              <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                <span class="small text-body-secondary">{{ $order->active_items_count }} kalem</span>
                <span class="fw-medium text-heading">{{ money($order->total) }}</span>
              </div>
            </div>
          </a>
        </div>
      @endforeach
    </div>
  @endif
@else
  <div class="card">
    @if ($history->isEmpty())
      <x-empty-state icon="tabler-history" title="Kayıt bulunamadı" text="Kapatılan ve iptal edilen siparişler burada listelenir." />
    @else
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Sipariş</th>
              <th>Tür</th>
              <th>Açan</th>
              <th>Açılış</th>
              <th>Kapanış</th>
              <th class="text-end">Tutar</th>
              <th>Durum</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach ($history as $order)
              <tr>
                <td><a href="{{ route('orders.show', $order) }}" class="fw-medium text-heading">{{ $order->label() }}</a></td>
                <td><x-status-badge :status="$order->type" /></td>
                <td>{{ $order->user->name }}</td>
                <td>{{ $order->opened_at->format('d.m.Y H:i') }}</td>
                <td>{{ $order->closed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                <td class="text-end fw-medium">{{ money($order->total) }}</td>
                <td><x-status-badge :status="$order->status" /></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <x-list-footer :paginator="$history" unit="sipariş" />
    @endif
  </div>
@endif

@if ($canTake)
  @include('orders._open-panel')
@endif
@endsection
