@extends('layouts.app')

@section('title', 'Kategoriler')

@section('breadcrumb')
  <li class="breadcrumb-item active" aria-current="page">Kategoriler</li>
@endsection

@section('page-actions')
  <a href="{{ route('categories.create') }}" class="btn btn-primary waves-effect waves-light">
    <i class="fi fi-rr-plus me-1"></i> Kategori Ekle
  </a>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="card-header d-flex gap-3 flex-wrap align-items-center justify-content-between border-0 pb-0">
      <h6 class="card-title mb-0">Menü Kategorileri</h6>
      <span class="text-muted">{{ $categories->count() }} kategori</span>
    </div>
    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table display table-row-rounded mb-0">
          <thead class="table-light">
            <tr>
              <th class="minw-200px">Kategori</th>
              <th>Sıra</th>
              <th>Ürün Sayısı</th>
              <th>Durum</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($categories as $category)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs bg-primary-subtle text-primary rounded-circle">
                      <i class="{{ $category->icon ?: 'fi fi-rr-utensils' }}"></i>
                    </div>
                    <div class="ms-2 fw-semibold">{{ $category->name }}</div>
                  </div>
                </td>
                <td>{{ $category->sort_order }}</td>
                <td>
                  <a href="{{ route('products.index', ['kategori' => $category->id]) }}">{{ $category->products_count }} ürün</a>
                </td>
                <td><x-status-badge :active="$category->is_active" /></td>
                <td>
                  <div class="btn-group float-end">
                    <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fi fi-rr-menu-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('categories.edit', $category) }}">
                          <i class="fi fi-rr-edit"></i> Düzenle
                        </a>
                      </li>
                      <li>
                        <x-delete-button :action="route('categories.destroy', $category)" confirm="{{ $category->name }} kategorisi silinsin mi?" />
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">Henüz kategori eklenmedi.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
