@props(['id', 'title', 'subtitle' => null, 'empty' => false, 'emptyText' => null])

{{--
  Grafik kartı: başlık, "Grafik / Tablo" geçişi. Grafik #chart-{id} içine çizilir;
  slot içeriği (tablo) erişilebilirlik ve kesin değerler için aynı verinin tablo görünümüdür.
--}}
<div {{ $attributes->class(['card h-100']) }}>
  <div class="card-header d-flex justify-content-between align-items-start gap-3">
    <div>
      <h5 class="card-title mb-1">{{ $title }}</h5>
      @if ($subtitle)
        <p class="card-subtitle mb-0">{{ $subtitle }}</p>
      @endif
    </div>
    @unless ($empty)
      <div class="btn-group btn-group-sm flex-shrink-0" role="group" aria-label="{{ $title }} görünümü">
        <input type="radio" class="btn-check" name="view-{{ $id }}" id="view-{{ $id }}-chart" value="chart" checked data-view-toggle="{{ $id }}">
        <label class="btn btn-outline-secondary" for="view-{{ $id }}-chart" title="Grafik"><i class="icon-base ti tabler-chart-bar icon-16px"></i><span class="visually-hidden">Grafik</span></label>
        <input type="radio" class="btn-check" name="view-{{ $id }}" id="view-{{ $id }}-table" value="table" data-view-toggle="{{ $id }}">
        <label class="btn btn-outline-secondary" for="view-{{ $id }}-table" title="Tablo"><i class="icon-base ti tabler-table icon-16px"></i><span class="visually-hidden">Tablo</span></label>
      </div>
    @endunless
  </div>
  <div class="card-body">
    @if ($empty)
      <x-empty-state icon="tabler-chart-bar-off" title="Bu dönemde veri yok" :text="$emptyText" class="py-6" />
    @else
      <div id="chart-{{ $id }}" data-chart-panel="{{ $id }}" role="img" aria-label="{{ $title }} grafiği"></div>
      <div class="d-none table-responsive" data-table-panel="{{ $id }}">
        {{ $slot }}
      </div>
    @endif
  </div>
</div>
