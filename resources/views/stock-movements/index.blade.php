@extends('layouts/layoutMaster')

@section('title', 'Stok Hareketleri')

@php
  $selectedProduct = request()->filled('urun') ? $products->firstWhere('id', (int) request('urun')) : null;
@endphp

@section('content')
<x-page-header title="Stok Hareketleri" :subtitle="$selectedProduct ? $selectedProduct->name.' ürününün stok geçmişi' : 'Ürün adetlerindeki tüm değişiklikler ve nedenleri.'" icon="tabler-arrows-exchange">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('products.index') }}">Ürünler</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">Stok Hareketleri</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="card">
  <div class="card-header border-bottom">
    <form method="GET" action="{{ route('stock-movements.index') }}" class="row g-4 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="filter-product">Ürün</label>
        <select name="urun" id="filter-product" class="select2 form-select" data-placeholder="Tüm ürünler"
          data-allow-clear="true" data-auto-submit>
          <option value=""></option>
          @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected(request('urun') == $product->id)>{{ $product->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-type">Hareket türü</label>
        <select name="tur" id="filter-type" class="select2 form-select" data-placeholder="Tüm türler" data-allow-clear="true"
          data-search="false" data-auto-submit>
          <option value=""></option>
          @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(request('tur') === $type->value)>{{ $type->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-date">Tarih aralığı</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
          <input type="text" name="tarih" id="filter-date" class="form-control" value="{{ request('tarih') }}"
            placeholder="Tarih seçin" data-datepicker="range" data-max-date="{{ today()->toDateString() }}" data-auto-submit>
        </div>
      </div>
      <div class="col-md-2 text-md-end">
        @if (request()->filled('adet'))
          <input type="hidden" name="adet" value="{{ request('adet') }}">
        @endif
        @if (collect(['urun', 'tur', 'tarih'])->contains(fn ($key) => request()->filled($key)))
          <a href="{{ route('stock-movements.index') }}" class="btn btn-label-secondary">
            <i class="icon-base ti tabler-x icon-16px me-1"></i> Temizle
          </a>
        @endif
      </div>
    </form>
  </div>

  @if ($movements->isEmpty())
    <x-empty-state icon="tabler-arrows-exchange" title="Kayıtlı stok hareketi yok"
      text="Ürün eklendiğinde ve stok güncellendiğinde hareketler burada listelenir." />
  @else
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Tarih</th>
            <th>Ürün</th>
            <th>Tür</th>
            <th class="text-end">Değişim</th>
            <th class="text-center">Adet</th>
            <th>Neden</th>
            <th>Çalışan</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($movements as $movement)
            <tr>
              <td>
                <span class="d-block">{{ $movement->created_at->format('d.m.Y') }}</span>
                <small class="text-body-secondary">{{ $movement->created_at->format('H:i') }}</small>
              </td>
              <td>
                <a href="{{ route('stock-movements.index', ['urun' => $movement->product_id]) }}" class="fw-medium text-heading">{{ $movement->product->name }}</a>
              </td>
              <td><x-status-badge :status="$movement->type" /></td>
              <td class="text-end">
                <span @class(['fw-medium', 'text-success' => $movement->quantity > 0, 'text-danger' => $movement->quantity < 0])>
                  <i class="icon-base ti {{ $movement->quantity > 0 ? 'tabler-arrow-up' : 'tabler-arrow-down' }} icon-14px"></i>
                  {{ abs($movement->quantity) }}
                </span>
              </td>
              <td class="text-center">
                <span class="text-body-secondary">{{ $movement->quantity_before }}</span>
                <i class="icon-base ti tabler-arrow-narrow-right icon-14px mx-1"></i>
                <span class="fw-medium text-heading">{{ $movement->quantity_after }}</span>
              </td>
              <td class="text-wrap" style="min-width: 200px">{{ $movement->reason ?? '—' }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-xs me-2">
                    <span class="avatar-initial rounded-circle bg-label-secondary">{{ $movement->user?->initials() ?? 'S' }}</span>
                  </div>
                  {{ $movement->user?->name ?? 'Sistem' }}
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <x-list-footer :paginator="$movements" unit="hareket" />
  @endif
</div>
@endsection
