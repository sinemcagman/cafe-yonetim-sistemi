@extends('layouts.app')

@section('title', 'Ürünler')

@section('breadcrumb')
  <li class="breadcrumb-item active" aria-current="page">Ürünler</li>
@endsection

@section('page-actions')
  <a href="{{ route('products.create') }}" class="btn btn-primary waves-effect waves-light">
    <i class="fi fi-rr-plus me-1"></i> Ürün Ekle
  </a>
@endsection

@section('content')
  @if ($lowStockCount > 0 && ! request()->boolean('kritik'))
    <div class="alert alert-warning d-flex align-items-center justify-content-between gap-2" role="alert">
      <span><i class="fi fi-rr-exclamation me-1"></i> {{ $lowStockCount }} ürünün adedi kritik seviyede.</span>
      <a href="{{ route('products.index', ['kritik' => 1]) }}" class="btn btn-sm btn-warning">Göster</a>
    </div>
  @endif

  <div class="card overflow-hidden">
    <div class="card-header border-0 pb-0">
      <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
          <input type="text" name="ara" value="{{ request('ara') }}" class="form-control" placeholder="Ürün ara...">
        </div>
        <div class="col-md-3">
          <select name="kategori" class="form-select">
            <option value="">Tüm kategoriler</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected(request('kategori') == $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="kritik" value="1" id="kritik" @checked(request()->boolean('kritik'))>
            <label class="form-check-label" for="kritik">Sadece kritik stok</label>
          </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary waves-effect w-100">Filtrele</button>
          @if (request()->hasAny(['ara', 'kategori', 'kritik']))
            <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-shadow btn-icon waves-effect" title="Temizle">
              <i class="fi fi-rr-cross-small"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table display table-row-rounded mb-0">
          <thead class="table-light">
            <tr>
              <th class="minw-200px">Ürün</th>
              <th>Kategori</th>
              <th>Satış Fiyatı</th>
              <th>Adet</th>
              <th>Durum</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($products as $product)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $product->name }}</div>
                  @if ($product->description)
                    <small class="text-muted">{{ $product->description }}</small>
                  @endif
                </td>
                <td>{{ $product->category->name }}</td>
                <td><span class="fw-semibold">{{ money($product->price) }}</span></td>
                <td>
                  @if ($product->isLowStock())
                    <span class="badge badge-lg bg-danger-subtle text-danger" title="Kritik seviye: {{ $product->low_stock_threshold }}">
                      {{ $product->stock_quantity }} adet
                    </span>
                  @else
                    <span class="badge badge-lg bg-success-subtle text-success">{{ $product->stock_quantity }} adet</span>
                  @endif
                </td>
                <td>
                  <span @class(['badge badge-lg', $product->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'])>
                    {{ $product->is_active ? 'Satışta' : 'Satış durduruldu' }}
                  </span>
                </td>
                <td>
                  <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light btn-shadow waves-effect"
                            data-bs-toggle="modal" data-bs-target="#stockModal"
                            data-action="{{ route('products.stock', $product) }}"
                            data-name="{{ $product->name }}"
                            data-stock="{{ $product->stock_quantity }}">
                      <i class="fi fi-rr-box-open me-1"></i> Stok
                    </button>
                    <div class="btn-group">
                      <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fi fi-rr-menu-dots"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('products.edit', $product) }}">
                            <i class="fi fi-rr-edit"></i> Düzenle
                          </a>
                        </li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('stock-movements.index', ['urun' => $product->id]) }}">
                            <i class="fi fi-rr-time-past"></i> Stok Geçmişi
                          </a>
                        </li>
                        <li>
                          <x-delete-button :action="route('products.destroy', $product)" confirm="{{ $product->name }} ürünü silinsin mi?" />
                        </li>
                      </ul>
                    </div>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">Kriterlere uygun ürün bulunamadı.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($products->hasPages())
        <div class="px-2 pt-3">{{ $products->links() }}</div>
      @endif
    </div>
  </div>
@endsection

@push('modals')
  <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="" class="modal-content" id="stockForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="stockModalLabel">Stok Güncelle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3"><strong data-stock-name></strong> — mevcut adet: <strong data-stock-current></strong></p>
          <div class="mb-3">
            <label class="form-label" for="stockType">Hareket Türü</label>
            <select name="type" id="stockType" class="form-select">
              <option value="{{ \App\Enums\StockMovementType::Giris->value }}">Stok girişi (yeni ürün geldi)</option>
              <option value="{{ \App\Enums\StockMovementType::Duzeltme->value }}">Düzeltme (fire, sayım farkı)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" for="stockQuantity">Adet</label>
            <input type="number" name="quantity" id="stockQuantity" class="form-control" required>
            <small class="text-muted">Düzeltmede stoğu azaltmak için eksi değer girin (örn. -2).</small>
          </div>
          <div>
            <label class="form-label" for="stockReason">Neden</label>
            <input type="text" name="reason" id="stockReason" class="form-control" maxlength="255" placeholder="Örn. Tedarikçiden 50 adet geldi" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-light btn-shadow waves-effect" data-bs-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-primary waves-effect waves-light">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
@endpush

@push('scripts')
  <script>
    document.getElementById('stockModal').addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      document.getElementById('stockForm').action = button.dataset.action;
      this.querySelector('[data-stock-name]').textContent = button.dataset.name;
      this.querySelector('[data-stock-current]').textContent = button.dataset.stock;
      document.getElementById('stockQuantity').value = '';
      document.getElementById('stockReason').value = '';
    });
  </script>
@endpush
