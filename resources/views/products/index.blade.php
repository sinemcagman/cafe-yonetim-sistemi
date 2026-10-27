@extends('layouts/layoutMaster')

@section('title', 'Ürünler')

@php
  $filtered = collect(['ara', 'kategori', 'kritik', 'durum'])->contains(fn ($key) => request()->filled($key));
@endphp

@section('content')
<x-page-header title="Ürünler" subtitle="Menüdeki ürünleri, fiyatları ve eldeki adetleri yönetin." icon="tabler-coffee">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Ürünler</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <a href="{{ route('stock-movements.index') }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-arrows-exchange icon-16px me-1"></i> Stok Hareketleri
    </a>
    <a href="{{ route('products.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-plus icon-16px me-1"></i> Ürün Ekle
    </a>
  </x-slot:actions>
</x-page-header>

<!-- Özet -->
<div class="card mb-6">
  <div class="card-widget-separator-wrapper">
    <div class="card-body card-widget-separator">
      <div class="row gy-4 gy-sm-1">
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Toplam ürün</p>
              <h4 class="mb-1">{{ $summary['total'] }}</h4>
              <p class="mb-0 small text-body-secondary">{{ $categories->count() }} kategoride</p>
            </div>
            <span class="avatar me-sm-6"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-coffee icon-26px"></i></span></span>
          </div>
          <hr class="d-none d-sm-block d-lg-none me-6" />
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
            <div>
              <p class="mb-1">Satışta</p>
              <h4 class="mb-1">{{ $summary['active'] }}</h4>
              <p class="mb-0 small text-body-secondary">{{ $summary['total'] - $summary['active'] }} ürünün satışı durduruldu</p>
            </div>
            <span class="avatar p-2 me-lg-6"><span class="avatar-initial rounded bg-label-success"><i class="icon-base ti tabler-circle-check icon-26px"></i></span></span>
          </div>
          <hr class="d-none d-sm-block d-lg-none" />
        </div>
        <div class="col-sm-6 col-lg-3">
          <a href="{{ route('products.index', ['kritik' => 1]) }}" class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3 text-reset">
            <div>
              <p class="mb-1">Kritik stok</p>
              <h4 class="mb-1 {{ $summary['low_stock'] > 0 ? 'text-danger' : '' }}">{{ $summary['low_stock'] }}</h4>
              <p class="mb-0 small text-body-secondary">{{ $summary['low_stock'] > 0 ? 'Listelemek için tıklayın' : 'Tüm adetler yeterli' }}</p>
            </div>
            <span class="avatar p-2 me-sm-6"><span class="avatar-initial rounded bg-label-{{ $summary['low_stock'] > 0 ? 'danger' : 'success' }}"><i class="icon-base ti tabler-alert-triangle icon-26px"></i></span></span>
          </a>
        </div>
        <div class="col-sm-6 col-lg-3">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="mb-1">Stok değeri</p>
              <h4 class="mb-1">{{ money($summary['stock_value']) }}</h4>
              <p class="mb-0 small text-body-secondary">Satış fiyatı × adet</p>
            </div>
            <span class="avatar p-2"><span class="avatar-initial rounded bg-label-info"><i class="icon-base ti tabler-wallet icon-26px"></i></span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Özet -->

<div class="card">
  <!-- Filtre -->
  <div class="card-header border-bottom">
    <form method="GET" action="{{ route('products.index') }}" class="row g-4 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="filter-search">Ara</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
          <input type="search" name="ara" id="filter-search" value="{{ request('ara') }}" class="form-control" placeholder="Ürün adı...">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-category">Kategori</label>
        <select name="kategori" id="filter-category" class="select2 form-select" data-placeholder="Tüm kategoriler"
          data-allow-clear="true" data-auto-submit>
          <option value=""></option>
          @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(request('kategori') == $category->id)>{{ $category->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label" for="filter-status">Durum</label>
        <select name="durum" id="filter-status" class="select2 form-select" data-placeholder="Tümü" data-allow-clear="true"
          data-search="false" data-auto-submit>
          <option value=""></option>
          <option value="satista" @selected(request('durum') === 'satista')>Satışta</option>
          <option value="durduruldu" @selected(request('durum') === 'durduruldu')>Durduruldu</option>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-center justify-content-between gap-3">
        @if (request()->filled('adet'))
          <input type="hidden" name="adet" value="{{ request('adet') }}">
        @endif
        <div class="form-check form-switch mb-2">
          <input class="form-check-input" type="checkbox" name="kritik" value="1" id="filter-low" @checked(request()->boolean('kritik')) data-auto-submit>
          <label class="form-check-label" for="filter-low">Kritik stok</label>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-icon" aria-label="Filtrele"><i class="icon-base ti tabler-filter"></i></button>
          @if ($filtered)
            <a href="{{ route('products.index') }}" class="btn btn-label-secondary btn-icon" data-bs-toggle="tooltip" title="Filtreleri temizle"><i class="icon-base ti tabler-x"></i></a>
          @endif
        </div>
      </div>
    </form>
  </div>
  <!--/ Filtre -->

  @if ($products->isEmpty())
    @if ($filtered)
      <x-empty-state icon="tabler-search" title="Sonuç bulunamadı" text="Filtrelere uyan ürün yok. Arama kriterlerini değiştirmeyi deneyin.">
        <a href="{{ route('products.index') }}" class="btn btn-label-primary">Filtreleri temizle</a>
      </x-empty-state>
    @else
      <x-empty-state icon="tabler-coffee" title="Menü henüz boş" text="İlk ürününüzü ekleyerek menüyü oluşturmaya başlayın.">
        <a href="{{ route('products.create') }}" class="btn btn-primary">Ürün Ekle</a>
      </x-empty-state>
    @endif
  @else
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Ürün</th>
            <th>Kategori</th>
            <th class="text-end">Fiyat</th>
            <th>Adet</th>
            <th>Durum</th>
            <th class="text-end">İşlemler</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($products as $product)
            @php
              $stockColor = $product->stock_quantity === 0 ? 'danger' : ($product->isLowStock() ? 'warning' : 'success');
            @endphp
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3 flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-secondary"><i class="{{ $product->category->iconClass() }} icon-18px"></i></span>
                  </div>
                  <div class="d-flex flex-column text-wrap">
                    <a href="{{ route('products.edit', $product) }}" class="fw-medium text-heading">{{ $product->name }}</a>
                    @if ($product->description)
                      <small class="text-body-secondary text-truncate" style="max-width: 260px">{{ $product->description }}</small>
                    @endif
                  </div>
                </div>
              </td>
              <td>{{ $product->category->name }}</td>
              <td class="text-end fw-medium">{{ money($product->price) }}</td>
              <td>
                <span class="badge bg-label-{{ $stockColor }}" @if ($product->isLowStock()) data-bs-toggle="tooltip" title="Kritik seviye: {{ $product->low_stock_threshold }} adet" @endif>
                  @if ($product->stock_quantity === 0)
                    <i class="icon-base ti tabler-alert-triangle icon-14px me-1"></i>Tükendi
                  @else
                    {{ $product->stock_quantity }} adet
                  @endif
                </span>
              </td>
              <td><x-status-badge :active="$product->is_active" active-text="Satışta" inactive-text="Durduruldu" /></td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-label-primary me-1" data-stock-open
                  data-action="{{ route('products.stock', $product) }}"
                  data-name="{{ $product->name }}"
                  data-stock="{{ $product->stock_quantity }}">
                  <i class="icon-base ti tabler-package icon-14px me-1"></i> Stok
                </button>
                <a href="{{ route('products.edit', $product) }}" class="btn btn-icon btn-text-secondary rounded-pill" data-bs-toggle="tooltip" title="Düzenle">
                  <i class="icon-base ti tabler-edit icon-md"></i>
                </a>
                <div class="d-inline-block">
                  <button type="button" class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-label="Diğer işlemler">
                    <i class="icon-base ti tabler-dots-vertical icon-md"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('stock-movements.index', ['urun' => $product->id]) }}">
                      <i class="icon-base ti tabler-history icon-sm"></i> Stok geçmişi
                    </a>
                    <div class="dropdown-divider"></div>
                    <x-delete-button :action="route('products.destroy', $product)" confirm="{{ $product->name }} silinsin mi?"
                      text="Siparişlerde kullanılmış ürünler silinemez; satışını durdurabilirsiniz." />
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <x-list-footer :paginator="$products" unit="ürün" />
  @endif
</div>

@include('products._stock-panel')
@endsection
