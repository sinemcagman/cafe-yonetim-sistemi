{{-- Kategori formu: hem yan panelde (offcanvas) hem ayrı sayfada kullanılır --}}
@csrf
<div class="mb-6">
  <label class="form-label" for="category-name">Kategori adı</label>
  <input type="text" name="name" id="category-name" value="{{ old('name', $category->name) }}"
    @class(['form-control', 'is-invalid' => $errors->has('name')]) placeholder="Örn. Sıcak İçecekler" maxlength="100" required>
  @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-6">
  <label class="form-label d-block">Menü ikonu</label>
  <x-icon-picker name="icon" label="Seçili ikon" :value="old('icon', $category->icon)"
    :suggested="\App\Models\Category::SUGGESTED_ICONS" />
  @error('icon')
    <div class="invalid-feedback d-block">{{ $message }}</div>
  @enderror
</div>

<div class="mb-6">
  <label class="form-label" for="category-sort">Menüdeki sırası</label>
  <input type="number" name="sort_order" id="category-sort" min="0" max="999"
    value="{{ old('sort_order', $category->sort_order ?? 0) }}" @class(['form-control', 'is-invalid' => $errors->has('sort_order')])>
  <div class="form-text">Küçük numaralı kategoriler sipariş ekranında önce görünür.</div>
  @error('sort_order')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-6">
  <div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="category-active" value="1"
      @checked(old('is_active', $category->is_active))>
    <label class="form-check-label" for="category-active">Aktif — sipariş ekranında gösterilsin</label>
  </div>
</div>
