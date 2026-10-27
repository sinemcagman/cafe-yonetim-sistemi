@csrf
<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="name">Ürün Adı</label>
    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" @class(['form-control', 'is-invalid' => $errors->has('name')]) placeholder="Örn. Çay" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-6">
    <label class="form-label" for="category_id">Kategori</label>
    <select name="category_id" id="category_id" @class(['form-select', 'is-invalid' => $errors->has('category_id')]) required>
      <option value="">Kategori seçin</option>
      @foreach ($categories as $category)
        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
          {{ $category->name }}{{ $category->is_active ? '' : ' (pasif)' }}
        </option>
      @endforeach
    </select>
    @error('category_id')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label" for="price">Satış Fiyatı (₺)</label>
    <input type="text" inputmode="decimal" name="price" id="price" value="{{ old('price', $product->price) }}" @class(['form-control', 'is-invalid' => $errors->has('price')]) placeholder="0,00" required>
    @error('price')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label" for="stock_quantity">Eldeki Adet</label>
    @if ($product->exists)
      <input type="text" id="stock_quantity" value="{{ $product->stock_quantity }}" class="form-control" disabled>
      <small class="text-muted">Adet değişiklikleri ürün listesindeki "Stok" butonuyla, nedeni kaydedilerek yapılır.</small>
    @else
      <input type="number" min="0" name="stock_quantity" id="stock_quantity" value="{{ old('stock_quantity', 0) }}" @class(['form-control', 'is-invalid' => $errors->has('stock_quantity')]) required>
      @error('stock_quantity')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    @endif
  </div>
  <div class="col-md-4">
    <label class="form-label" for="low_stock_threshold">Kritik Stok Seviyesi</label>
    <input type="number" min="0" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" @class(['form-control', 'is-invalid' => $errors->has('low_stock_threshold')]) required>
    @error('low_stock_threshold')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12">
    <label class="form-label" for="description">Açıklama</label>
    <textarea name="description" id="description" rows="2" @class(['form-control', 'is-invalid' => $errors->has('description')]) placeholder="İsteğe bağlı">{{ old('description', $product->description) }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(old('is_active', $product->is_active))>
      <label class="form-check-label" for="is_active">Satışta (kapatılırsa ürün siparişte seçilemez)</label>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-primary waves-effect waves-light">Kaydet</button>
  <a href="{{ route('products.index') }}" class="btn btn-outline-light btn-shadow waves-effect">Vazgeç</a>
</div>
