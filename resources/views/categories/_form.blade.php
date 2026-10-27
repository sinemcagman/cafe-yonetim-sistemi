@csrf
<div class="row g-4">
  <div class="col-md-6">
    <label class="form-label" for="name">Kategori Adı</label>
    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" @class(['form-control', 'is-invalid' => $errors->has('name')]) placeholder="Örn. İçecekler" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-3">
    <label class="form-label" for="sort_order">Menüdeki Sırası</label>
    <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}" @class(['form-control', 'is-invalid' => $errors->has('sort_order')])>
    @error('sort_order')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-3">
    <label class="form-label" for="icon">İkon</label>
    <input type="text" name="icon" id="icon" value="{{ old('icon', $category->icon) }}" @class(['form-control', 'is-invalid' => $errors->has('icon')]) placeholder="fi fi-rr-mug-hot-alt">
    @error('icon')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(old('is_active', $category->is_active))>
      <label class="form-check-label" for="is_active">Aktif (menüde gösterilir)</label>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-primary waves-effect waves-light">Kaydet</button>
  <a href="{{ route('categories.index') }}" class="btn btn-outline-light btn-shadow waves-effect">Vazgeç</a>
</div>
