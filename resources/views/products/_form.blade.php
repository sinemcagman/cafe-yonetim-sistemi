{{-- Ürün formu: create ve edit sayfaları ortak kullanır --}}
@csrf
<x-form-errors />

<div class="row g-6">
  <!-- Sol: ürün bilgileri -->
  <div class="col-12 col-lg-8">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">Ürün bilgileri</h5>
      </div>
      <div class="card-body">
        <div class="mb-6">
          <label class="form-label" for="product-name">Ürün adı</label>
          <input type="text" name="name" id="product-name" value="{{ old('name', $product->name) }}"
            @class(['form-control', 'is-invalid' => $errors->has('name')]) placeholder="Örn. Türk Kahvesi" maxlength="150" required autofocus>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-6">
          <label class="form-label d-block">Kategori</label>
          @if ($categories->isEmpty())
            <div class="alert alert-warning mb-0">
              Önce bir kategori oluşturmalısınız. <a href="{{ route('categories.index') }}" class="alert-link">Kategorilere git</a>
            </div>
          @else
            <div class="row g-3">
              @foreach ($categories as $category)
                <div class="col-6 col-md-4">
                  <input type="radio" class="btn-check" name="category_id" id="category-{{ $category->id }}" value="{{ $category->id }}"
                    @checked(old('category_id', $product->category_id) == $category->id) required>
                  <label @class(['btn btn-outline-primary w-100 d-flex align-items-center gap-2 justify-content-start', 'opacity-50' => ! $category->is_active])
                    for="category-{{ $category->id }}">
                    <i class="{{ $category->iconClass() }} icon-20px"></i>
                    <span class="text-truncate">{{ $category->name }}</span>
                  </label>
                </div>
              @endforeach
            </div>
          @endif
          @error('category_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div>
          <label class="form-label" for="product-description">Açıklama <span class="text-body-secondary">(isteğe bağlı)</span></label>
          <textarea name="description" id="product-description" rows="3" maxlength="500"
            @class(['form-control', 'is-invalid' => $errors->has('description')]) placeholder="Örn. Duble, közde pişirilir">{{ old('description', $product->description) }}</textarea>
          @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>
  </div>
  <!--/ Sol -->

  <!-- Sağ: fiyat, stok, durum -->
  <div class="col-12 col-lg-4">
    <div class="card mb-6">
      <div class="card-header">
        <h5 class="card-title mb-0">Fiyat ve stok</h5>
      </div>
      <div class="card-body">
        <div class="mb-6">
          <label class="form-label" for="product-price">Satış fiyatı</label>
          <div class="input-group">
            <input type="text" inputmode="decimal" name="price" id="product-price"
              value="{{ old('price', $product->price !== null ? number_format((float) $product->price, 2, ',', '') : '') }}"
              @class(['form-control', 'is-invalid' => $errors->has('price')]) placeholder="0,00" required>
            <span class="input-group-text">₺</span>
            @error('price')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mb-6">
          <label class="form-label" for="product-stock">Eldeki adet</label>
          @if ($product->exists)
            <div class="d-flex align-items-center justify-content-between bg-lighter rounded px-4 py-3">
              <span class="h5 mb-0">{{ $product->stock_quantity }} adet</span>
              <a href="{{ route('products.index', ['ara' => $product->name]) }}" class="small">Stok güncelle</a>
            </div>
            <div class="form-text">Adet, nedeni kaydedilerek ürün listesindeki "Stok" butonuyla değiştirilir.</div>
          @else
            <input type="number" min="0" name="stock_quantity" id="product-stock" value="{{ old('stock_quantity', 0) }}"
              @class(['form-control', 'is-invalid' => $errors->has('stock_quantity')]) required>
            <div class="form-text">Başlangıç stoğu olarak kaydedilir.</div>
            @error('stock_quantity')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          @endif
        </div>

        <div>
          <label class="form-label" for="product-threshold">Kritik stok seviyesi</label>
          <input type="number" min="0" name="low_stock_threshold" id="product-threshold"
            value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}"
            @class(['form-control', 'is-invalid' => $errors->has('low_stock_threshold')]) required>
          <div class="form-text">Adet bu sayıya düştüğünde uyarı verilir.</div>
          @error('low_stock_threshold')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0">Satışta</h6>
            <small class="text-body-secondary">Kapalıysa ürün siparişe eklenemez.</small>
          </div>
          <div class="form-check form-switch mb-0">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="product-active" value="1"
              @checked(old('is_active', $product->is_active)) aria-label="Satışta">
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--/ Sağ -->
</div>
