@extends('layouts/layoutMaster')

@section('title', 'Kategoriler')

@php
  // Doğrulama hatası olduysa paneli, kullanıcının girdiği değerlerle tekrar aç
  $reopen = $errors->any() ? old('_panel') : null;
@endphp

@section('content')
<x-page-header title="Kategoriler" subtitle="Menüdeki ürün gruplarını ve sıralarını yönetin." icon="tabler-category">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Kategoriler</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <button type="button" class="btn btn-primary" data-category-create>
      <i class="icon-base ti tabler-plus icon-16px me-1"></i> Kategori Ekle
    </button>
  </x-slot:actions>
</x-page-header>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center border-bottom">
    <h5 class="card-title mb-0">Menü Kategorileri</h5>
    <span class="badge bg-label-primary">{{ $categories->count() }} kategori</span>
  </div>
  @if ($categories->isEmpty())
    <x-empty-state icon="tabler-category" title="Henüz kategori yok"
      text="Ürün eklemeden önce İçecekler, Yiyecekler gibi kategoriler oluşturun.">
      <button type="button" class="btn btn-primary" data-category-create>İlk kategoriyi ekle</button>
    </x-empty-state>
  @else
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Kategori</th>
            <th class="text-center">Sıra</th>
            <th>Ürünler</th>
            <th>Durum</th>
            <th class="text-end">İşlemler</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($categories as $category)
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded bg-label-primary"><i class="{{ $category->iconClass() }} icon-18px"></i></span>
                  </div>
                  <span class="fw-medium text-heading">{{ $category->name }}</span>
                </div>
              </td>
              <td class="text-center">{{ $category->sort_order }}</td>
              <td>
                <a href="{{ route('products.index', ['kategori' => $category->id]) }}" class="d-inline-flex align-items-center gap-1">
                  {{ $category->products_count }} ürün <i class="icon-base ti tabler-arrow-up-right icon-14px"></i>
                </a>
              </td>
              <td><x-status-badge :active="$category->is_active" /></td>
              <td class="text-end">
                <button type="button" class="btn btn-icon btn-text-secondary rounded-pill" data-bs-toggle="tooltip" title="Düzenle"
                  data-category-edit
                  data-action="{{ route('categories.update', $category) }}"
                  data-id="{{ $category->id }}"
                  data-name="{{ $category->name }}"
                  data-icon="{{ $category->icon }}"
                  data-sort="{{ $category->sort_order }}"
                  data-active="{{ $category->is_active ? 1 : 0 }}">
                  <i class="icon-base ti tabler-edit icon-md"></i>
                </button>
                <div class="d-inline-block">
                  <button type="button" class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                    data-bs-toggle="dropdown" aria-label="Diğer işlemler">
                    <i class="icon-base ti tabler-dots-vertical icon-md"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('products.index', ['kategori' => $category->id]) }}">
                      <i class="icon-base ti tabler-coffee icon-sm"></i> Ürünlerini gör
                    </a>
                    <div class="dropdown-divider"></div>
                    <x-delete-button :action="route('categories.destroy', $category)" confirm="{{ $category->name }} silinsin mi?"
                      text="İçinde ürün bulunan kategoriler silinemez; bunun yerine pasif yapabilirsiniz." />
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

<!-- Kategori ekleme / düzenleme paneli -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="categoryPanel" aria-labelledby="categoryPanelLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="categoryPanelLabel" class="offcanvas-title">Kategori Ekle</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
    <form method="POST" action="{{ route('categories.store') }}" id="categoryForm">
      <input type="hidden" name="_method" value="POST" id="categoryMethod">
      <input type="hidden" name="_panel" value="create" id="categoryPanelKey">
      @include('categories._form', ['category' => new \App\Models\Category(['is_active' => true])])
      <button type="submit" class="btn btn-primary me-3">Kaydet</button>
      <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Vazgeç</button>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const panelEl = document.getElementById('categoryPanel');
    const panel = new bootstrap.Offcanvas(panelEl);
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('categoryPanelLabel');
    const method = document.getElementById('categoryMethod');
    const panelKey = document.getElementById('categoryPanelKey');
    const defaultIcon = @json(\App\Models\Category::DEFAULT_ICON);

    const clearErrors = () => form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const open = (data = null) => {
      clearErrors();
      form.action = data ? data.action : @json(route('categories.store'));
      method.value = data ? 'PUT' : 'POST';
      panelKey.value = data ? data.id : 'create';
      title.textContent = data ? 'Kategoriyi Düzenle' : 'Kategori Ekle';
      form.querySelector('[name=name]').value = data ? data.name : '';
      form.querySelector('[name=sort_order]').value = data ? data.sort : 0;
      form.querySelector('#category-active').checked = data ? data.active === '1' : true;
      form.querySelector('[data-icon-picker]').dispatchEvent(
        new CustomEvent('icon-picker:set', { detail: (data && data.icon) || defaultIcon })
      );
      panel.show();
      setTimeout(() => form.querySelector('[name=name]').focus(), 300);
    };

    document.querySelectorAll('[data-category-create]').forEach(btn => btn.addEventListener('click', () => open()));
    document.querySelectorAll('[data-category-edit]').forEach(btn => btn.addEventListener('click', () => open(btn.dataset)));

    @if ($reopen)
      // Hatalı gönderimde paneli eski değerlerle tekrar aç
      form.action = @json($reopen === 'create' ? route('categories.store') : route('categories.update', $reopen));
      method.value = @json($reopen === 'create' ? 'POST' : 'PUT');
      panelKey.value = @json($reopen);
      title.textContent = @json($reopen === 'create' ? 'Kategori Ekle' : 'Kategoriyi Düzenle');
      panel.show();
    @endif
  });
</script>
@endpush
