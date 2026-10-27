@props(['name' => 'icon', 'value' => null, 'suggested' => [], 'label' => 'İkon'])

@php
  $value = $value ?: \App\Models\Category::DEFAULT_ICON;
@endphp

{{-- Tüm Tabler ikonları arasından aranabilir ikon seçici. Davranış: resources/js/icon-picker.js --}}
@once
  <link rel="preload" href="{{ asset(\App\Support\TablerIcons::PATH) }}" as="fetch" type="application/json" crossorigin="anonymous">
@endonce
<div class="icon-picker" data-icon-picker data-source="{{ asset(\App\Support\TablerIcons::PATH) }}">
  <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-icon-picker-input>

  <div class="d-flex align-items-center gap-3 mb-3">
    <div class="avatar avatar-lg flex-shrink-0">
      <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti {{ $value }} icon-28px" data-icon-picker-preview></i></span>
    </div>
    <div class="flex-grow-1 min-w-0">
      <small class="text-body-secondary d-block">{{ $label }}</small>
      <code class="text-heading" data-icon-picker-name>{{ $value }}</code>
    </div>
  </div>

  <div class="input-group input-group-merge mb-3">
    <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
    <input type="search" class="form-control" placeholder="İkon ara: kahve, tatlı, çorba, cup..." data-icon-picker-search
      aria-label="İkon ara">
  </div>

  <div class="border rounded p-2 overflow-auto" style="max-height: 260px" data-icon-picker-body>
    @if ($suggested)
      <small class="text-body-secondary text-uppercase fw-medium d-block px-1 mb-2" data-icon-picker-suggested-title>Önerilenler</small>
      <div class="d-flex flex-wrap gap-1 mb-3" data-icon-picker-suggested>
        @foreach ($suggested as $icon => $title)
          <button type="button" class="btn btn-icon btn-outline-secondary icon-picker-item" data-icon="{{ $icon }}"
            title="{{ $title }}" aria-label="{{ $title }}">
            <i class="icon-base ti {{ $icon }} icon-22px"></i>
          </button>
        @endforeach
      </div>
    @endif
    <small class="text-body-secondary text-uppercase fw-medium d-block px-1 mb-2">
      Tüm ikonlar <span data-icon-picker-count></span>
    </small>
    <div class="d-flex flex-wrap gap-1" data-icon-picker-grid>
      <div class="text-body-secondary small px-1">Yükleniyor...</div>
    </div>
    <div class="text-center mt-2">
      <button type="button" class="btn btn-sm btn-label-secondary d-none" data-icon-picker-more>Daha fazla göster</button>
    </div>
  </div>
</div>
