@extends('layouts/layoutMaster')

@section('title', $order->label())

@php
  $editable = $order->isOpen() && auth()->user()->canTakeOrders();
  $typeIcon = match ($order->type) {
    \App\Enums\OrderType::Masa => 'tabler-armchair',
    \App\Enums\OrderType::Paket => 'tabler-package',
    \App\Enums\OrderType::GelAl => 'tabler-walk',
  };
@endphp

@section('content')
<x-page-header :title="$order->label()" :icon="$typeIcon"
  :subtitle="$order->type->label().' siparişi · '.$order->user->name.' açtı · '.$order->opened_at->diffForHumans()">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('orders.index') }}">Siparişler</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">{{ $order->code() }}</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    @if ($order->diningTable)
      <a href="{{ route('tables.index') }}#masa-{{ $order->diningTable->number }}" class="btn btn-label-secondary">
        <i class="icon-base ti tabler-armchair icon-16px me-1"></i> Masalar
      </a>
    @endif
    @if ($editable && (float) $order->paid_total === 0.0)
      <form method="POST" action="{{ route('orders.cancel', $order) }}" class="d-inline"
        data-prompt-reason="{{ $order->label() }} tümüyle iptal edilsin mi?"
        data-prompt-text="Tüm kalemler iptal edilir, ürün adetleri stoğa geri eklenir{{ $order->diningTable ? ' ve masa boşalır' : '' }}."
        data-prompt-placeholder="Örn. Müşteri ayrıldı" data-prompt-button="Siparişi iptal et">
        @csrf
        <input type="hidden" name="reason">
        <button type="submit" class="btn btn-label-danger">
          <i class="icon-base ti tabler-ban icon-16px me-1"></i> Siparişi İptal Et
        </button>
      </form>
    @endif
  </x-slot:actions>
</x-page-header>

<div class="row g-6">
  @if ($editable)
    <!-- Menü -->
    <div class="col-xl-8 col-lg-7">
      <div class="card">
        <div class="card-header border-bottom">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="input-group input-group-merge" style="max-width: 320px">
              <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
              <input type="search" class="form-control" placeholder="Ürün ara..." data-menu-search aria-label="Ürün ara">
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="small text-body-secondary">Eklenecek adet</span>
              <div class="input-group input-group-sm" style="width: 136px">
                <button type="button" class="btn btn-outline-secondary" data-qty-step="-1" aria-label="Azalt"><i class="icon-base ti tabler-minus"></i></button>
                <input type="number" min="1" max="100" value="1" class="form-control text-center fw-medium px-1" data-qty aria-label="Eklenecek adet">
                <button type="button" class="btn btn-outline-secondary" data-qty-step="1" aria-label="Artır"><i class="icon-base ti tabler-plus"></i></button>
              </div>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-4" data-menu-tabs role="tablist" aria-label="Kategoriler">
            <button type="button" class="btn btn-sm rounded-pill btn-primary" data-category="all" aria-pressed="true">Tümü</button>
            @foreach ($categories as $category)
              <button type="button" class="btn btn-sm rounded-pill btn-label-secondary d-flex align-items-center gap-1"
                data-category="{{ $category->id }}" aria-pressed="false">
                <i class="{{ $category->iconClass() }} icon-16px"></i> {{ $category->name }}
              </button>
            @endforeach
          </div>
        </div>
        <div class="card-body pt-6">
          @if ($categories->isEmpty())
            <x-empty-state icon="tabler-coffee" title="Menüde ürün yok" text="Sipariş alabilmek için önce ürün eklenmeli." />
          @else
            <div class="row g-3" data-menu-grid>
              @foreach ($categories as $category)
                @foreach ($category->products as $product)
                  @php $sellable = $product->is_active && $product->stock_quantity > 0; @endphp
                  <div class="col-6 col-md-4 col-xxl-3" data-menu-item data-category="{{ $category->id }}" data-name="{{ mb_strtolower($product->name) }}">
                    <form method="POST" action="{{ route('orders.items.store', $order) }}" class="h-100" data-add-item>
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $product->id }}">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" @disabled(! $sellable)
                        class="btn btn-outline-secondary w-100 h-100 d-flex flex-column align-items-start text-start p-3 menu-product">
                        <span class="d-flex w-100 justify-content-between align-items-start mb-2">
                          <span class="avatar avatar-sm">
                            <span class="avatar-initial rounded bg-label-primary"><i class="{{ $category->iconClass() }} icon-18px"></i></span>
                          </span>
                          <span @class(['badge', 'bg-label-success' => ! $product->isLowStock(), 'bg-label-warning' => $product->isLowStock() && $product->stock_quantity > 0, 'bg-label-danger' => $product->stock_quantity === 0])
                            data-stock-badge="{{ $product->id }}">
                            {{ ! $product->is_active ? 'Satışta değil' : ($product->stock_quantity === 0 ? 'Tükendi' : $product->stock_quantity.' adet') }}
                          </span>
                        </span>
                        <span class="fw-medium text-heading lh-sm mb-1">{{ $product->name }}</span>
                        <span class="text-primary fw-medium">{{ money($product->price) }}</span>
                      </button>
                    </form>
                  </div>
                @endforeach
              @endforeach
            </div>
            <div class="d-none" data-menu-empty>
              <x-empty-state icon="tabler-search" title="Ürün bulunamadı" text="Farklı bir ad deneyin." class="py-6" />
            </div>
          @endif
        </div>
      </div>
    </div>
  @endif

  <!-- Adisyon -->
  <div @class(['col-xl-4 col-lg-5' => $editable, 'col-lg-6' => ! $editable])>
    <div class="card position-sticky" style="top: 6rem" id="order-ticket" aria-live="polite">
      @include('orders._ticket')
    </div>
  </div>
</div>
@endsection

@if ($editable)
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const grid = document.querySelector('[data-menu-grid]');
    if (!grid) return;

    const ticket = document.getElementById('order-ticket');
    const qtyInput = document.querySelector('[data-qty]');
    const search = document.querySelector('[data-menu-search]');
    const tabs = document.querySelector('[data-menu-tabs]');
    const empty = document.querySelector('[data-menu-empty]');
    let category = 'all';

    // Kategori sekmeleri ve arama: kartları sayfayı yenilemeden süzer
    const applyFilter = () => {
      const query = search.value.toLocaleLowerCase('tr').trim();
      let visible = 0;
      grid.querySelectorAll('[data-menu-item]').forEach(item => {
        const show = (category === 'all' || item.dataset.category === category) && (!query || item.dataset.name.includes(query));
        item.classList.toggle('d-none', !show);
        if (show) visible++;
      });
      empty.classList.toggle('d-none', visible > 0);
    };

    tabs.querySelectorAll('[data-category]').forEach(tab => tab.addEventListener('click', () => {
      tabs.querySelectorAll('[data-category]').forEach(other => {
        other.classList.replace('btn-primary', 'btn-label-secondary');
        other.setAttribute('aria-pressed', 'false');
      });
      tab.classList.replace('btn-label-secondary', 'btn-primary');
      tab.setAttribute('aria-pressed', 'true');
      category = tab.dataset.category;
      applyFilter();
    }));
    search.addEventListener('input', applyFilter);

    // Eklenecek adet: bir sonraki ürüne dokunuşta kullanılır, sonra 1'e döner
    const clampQty = value => Math.min(100, Math.max(1, parseInt(value, 10) || 1));
    document.querySelectorAll('[data-qty-step]').forEach(button => button.addEventListener('click', () => {
      qtyInput.value = clampQty(parseInt(qtyInput.value, 10) + parseInt(button.dataset.qtyStep, 10));
    }));

    const updateStockBadges = stock => {
      Object.entries(stock || {}).forEach(([productId, quantity]) => {
        const badge = grid.querySelector(`[data-stock-badge="${productId}"]`);
        if (!badge) return;
        badge.textContent = quantity === 0 ? 'Tükendi' : `${quantity} adet`;
        badge.className = 'badge ' + (quantity === 0 ? 'bg-label-danger' : 'bg-label-success');
        badge.closest('button').disabled = quantity === 0;
      });
    };

    // Ürüne dokununca sayfayı yenilemeden siparişe ekle ve adisyonu güncelle
    grid.addEventListener('submit', async event => {
      const form = event.target.closest('[data-add-item]');
      if (!form) return;
      event.preventDefault();

      const button = form.querySelector('button');
      form.querySelector('[name=quantity]').value = clampQty(qtyInput.value);
      button.disabled = true;

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form)
        });
        const data = await response.json();

        if (data.ticket) {
          ticket.innerHTML = data.ticket;
          ticket.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
        updateStockBadges(data.stock);
        window.notify(data.ok ? 'success' : 'error', data.message || (data.errors ? Object.values(data.errors)[0][0] : 'İşlem tamamlanamadı.'));
        qtyInput.value = 1;
      } catch (error) {
        form.submit();
        return;
      } finally {
        if (!button.closest('[data-menu-item]').querySelector('[data-stock-badge]')?.textContent.includes('Tükendi')) {
          button.disabled = false;
        }
      }
    });
  });
</script>
@endpush
@endif
