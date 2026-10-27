@extends('layouts/layoutMaster')

@section('title', 'Masalar')

@php
  $isManager = auth()->user()->isManager();
  $inactiveCount = $tables->where('is_active', false)->count();
  $reopen = $errors->any() ? old('_panel') : null;
@endphp

@section('content')
<x-page-header title="Masalar" subtitle="Hangi masanın boş, hangisinin kullanımda olduğunu buradan takip edin." icon="tabler-armchair">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Masalar</li>
  </x-slot:breadcrumb>
  @if ($isManager)
    <x-slot:actions>
      <button type="button" class="btn btn-primary" data-table-create>
        <i class="icon-base ti tabler-plus icon-16px me-1"></i> Masa Ekle
      </button>
    </x-slot:actions>
  @endif
</x-page-header>

@if ($tables->isEmpty())
  <div class="card">
    <x-empty-state icon="tabler-armchair" title="Henüz masa tanımlanmadı"
      text="Kafedeki masaları numaralarıyla ekleyin; çalışanlar doluluk durumunu buradan görsün.">
      @if ($isManager)
        <button type="button" class="btn btn-primary" data-table-create>İlk masayı ekle</button>
      @endif
    </x-empty-state>
  </div>
@else
  <!-- Durum sekmeleri -->
  <div class="nav-align-top mb-6">
    <ul class="nav nav-pills flex-wrap gap-2" role="tablist" data-table-filter>
      <li class="nav-item">
        <button type="button" class="nav-link active" data-filter="all">
          <i class="icon-base ti tabler-layout-grid icon-sm me-1_5"></i> Tümü
          <span class="badge rounded-pill bg-label-primary ms-1_5">{{ $tables->count() }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-filter="bos">
          <i class="icon-base ti tabler-circle-check icon-sm me-1_5"></i> Boş
          <span class="badge rounded-pill bg-label-success ms-1_5">{{ $availableCount }}</span>
        </button>
      </li>
      <li class="nav-item">
        <button type="button" class="nav-link" data-filter="dolu">
          <i class="icon-base ti tabler-users icon-sm me-1_5"></i> Kullanımda
          <span class="badge rounded-pill bg-label-danger ms-1_5">{{ $occupiedCount }}</span>
        </button>
      </li>
      @if ($inactiveCount > 0)
        <li class="nav-item">
          <button type="button" class="nav-link" data-filter="pasif">
            <i class="icon-base ti tabler-eye-off icon-sm me-1_5"></i> Pasif
            <span class="badge rounded-pill bg-label-secondary ms-1_5">{{ $inactiveCount }}</span>
          </button>
        </li>
      @endif
    </ul>
  </div>

  @foreach ($areas as $area => $areaTables)
    <div class="mb-6" data-table-area>
      <h6 class="text-body-secondary text-uppercase small fw-medium mb-3">
        <i class="icon-base ti tabler-map-pin icon-14px me-1"></i>{{ $area }}
        <span class="ms-1">({{ $areaTables->count() }})</span>
      </h6>
      <div class="row g-4">
        @foreach ($areaTables as $table)
          @include('tables._card', ['table' => $table])
        @endforeach
      </div>
    </div>
  @endforeach

  <div class="card d-none" data-table-empty>
    <x-empty-state icon="tabler-filter-off" title="Bu durumda masa yok" />
  </div>
@endif

@if ($isManager)
  <!-- Masa ekleme / düzenleme paneli -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="tablePanel" aria-labelledby="tablePanelLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="tablePanelLabel" class="offcanvas-title">Masa Ekle</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body p-6">
      <form method="POST" action="{{ route('tables.store') }}" id="tableForm">
        <input type="hidden" name="_method" value="POST" id="tableMethod">
        <input type="hidden" name="_panel" value="create" id="tablePanelKey">
        @include('tables._form', ['table' => new \App\Models\DiningTable(['number' => $nextNumber, 'capacity' => 4, 'is_active' => true])])
        <button type="submit" class="btn btn-primary me-3">Kaydet</button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
      </form>
    </div>
  </div>
@endif
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Durum sekmeleri: kartları sayfayı yenilemeden süzer
    const filterNav = document.querySelector('[data-table-filter]');
    if (filterNav) {
      filterNav.querySelectorAll('[data-filter]').forEach(button => {
        button.addEventListener('click', () => {
          filterNav.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
          button.classList.add('active');
          const filter = button.dataset.filter;
          let visible = 0;
          document.querySelectorAll('[data-table-area]').forEach(area => {
            let areaVisible = 0;
            area.querySelectorAll('[data-table-card]').forEach(card => {
              const show = filter === 'all' || card.dataset.tableCard === filter;
              card.classList.toggle('d-none', !show);
              if (show) areaVisible++;
            });
            area.classList.toggle('d-none', areaVisible === 0);
            visible += areaVisible;
          });
          document.querySelector('[data-table-empty]').classList.toggle('d-none', visible > 0);
        });
      });
    }

    // Panelden masa ekleme / düzenleme (yalnızca yönetici)
    const panelEl = document.getElementById('tablePanel');
    if (!panelEl) return;

    const panel = new bootstrap.Offcanvas(panelEl);
    const form = document.getElementById('tableForm');
    const method = document.getElementById('tableMethod');
    const panelKey = document.getElementById('tablePanelKey');
    const title = document.getElementById('tablePanelLabel');
    const nextNumber = @json($nextNumber);

    const open = (data = null) => {
      form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      form.action = data ? data.action : @json(route('tables.store'));
      method.value = data ? 'PUT' : 'POST';
      panelKey.value = data ? data.id : 'create';
      title.textContent = data ? `Masa ${data.number} — Düzenle` : 'Masa Ekle';
      form.querySelector('[name=number]').value = data ? data.number : nextNumber;
      form.querySelector('[name=capacity]').value = data ? data.capacity : 4;
      // Bölüm alanı Select2: listede olmayan bir bölüm ise önce seçenek olarak ekle
      const area = data ? data.area : '';
      const $area = $(form.querySelector('[name=area]'));
      if (area && !$area.find('option').filter((i, option) => option.value === area).length) {
        $area.append(new Option(area, area));
      }
      $area.val(area).trigger('change');
      form.querySelector('#table-active').checked = data ? data.active === '1' : true;
      panel.show();
    };

    document.querySelectorAll('[data-table-create]').forEach(btn => btn.addEventListener('click', () => open()));
    document.querySelectorAll('[data-table-edit]').forEach(btn => btn.addEventListener('click', () => open(btn.dataset)));

    @if ($reopen)
      form.action = @json($reopen === 'create' ? route('tables.store') : route('tables.update', $reopen));
      method.value = @json($reopen === 'create' ? 'POST' : 'PUT');
      panelKey.value = @json($reopen);
      title.textContent = @json($reopen === 'create' ? 'Masa Ekle' : 'Masayı Düzenle');
      panel.show();
    @endif
  });
</script>
@endpush
