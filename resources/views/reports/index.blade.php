@extends('layouts/layoutMaster')

@section('title', 'Raporlar')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/demlio-reports.js'])
@endsection

@php
  $rangeText = $report->days() === 1
    ? $report->from()->translatedFormat('d F Y, l')
    : $report->from()->translatedFormat('d F').' – '.$report->to()->translatedFormat('d F Y');
  $peakHour = $hourly->sortByDesc('orders')->first();
@endphp

@section('content')
<x-page-header title="Raporlar" :subtitle="$rangeText.' · '.$report->days().' gün'" icon="tabler-chart-histogram">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Raporlar</li>
  </x-slot:breadcrumb>
</x-page-header>

<!-- Dönem seçimi: hazır dönemler ve tarih aralığı tek satırda -->
<div class="card mb-6">
  <div class="card-body d-flex flex-wrap align-items-end gap-4">
    <div>
      <span class="form-label d-block">Dönem</span>
      <div class="d-flex flex-wrap gap-2" role="group" aria-label="Hazır dönemler">
        @foreach ($presets as $key => $label)
          <a href="{{ route('reports.index', ['donem' => $key]) }}" @class(['btn btn-sm rounded-pill', 'btn-primary' => $preset === $key, 'btn-label-secondary' => $preset !== $key])
            @if ($preset === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
      </div>
    </div>
    <form method="GET" action="{{ route('reports.index') }}" class="ms-md-auto" style="min-width: 260px">
      <label class="form-label" for="report-range">Tarih aralığı</label>
      <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
        <input type="text" name="tarih" id="report-range" class="form-control" data-datepicker="range" data-auto-submit
          data-max-date="{{ today()->toDateString() }}" placeholder="Tarih seçin"
          value="{{ $preset ? '' : $report->from()->toDateString().' - '.$report->to()->toDateString() }}">
      </div>
    </form>
  </div>
</div>

<!-- Özet -->
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <p class="mb-0">Ciro</p>
          <span class="avatar"><span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-cash icon-24px"></i></span></span>
        </div>
        <h3 class="mb-1">{{ money($summary['revenue']) }}</h3>
        @if ($summary['change'] !== null)
          @php $up = $summary['change'] >= 0; @endphp
          <small @class(['fw-medium', 'text-success' => $up, 'text-danger' => ! $up])>
            <i class="icon-base ti {{ $up ? 'tabler-arrow-up-right' : 'tabler-arrow-down-right' }} icon-14px"></i>
            {{ $up ? '+' : '−' }}%{{ number_format(abs($summary['change']), 1, ',', '.') }}
          </small>
          <small class="text-body-secondary">önceki {{ $report->days() }} güne göre</small>
        @else
          <small class="text-body-secondary">Önceki dönemde tahsilat yok</small>
        @endif
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <p class="mb-0">Nakit / kart</p>
          <span class="avatar"><span class="avatar-initial rounded bg-label-info"><i class="icon-base ti tabler-credit-card icon-24px"></i></span></span>
        </div>
        <h5 class="mb-1">{{ money($summary['cash']) }} <small class="text-body-secondary fw-normal">nakit</small></h5>
        <h5 class="mb-0">{{ money($summary['card']) }} <small class="text-body-secondary fw-normal">kart</small></h5>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <p class="mb-0">Kapanan sipariş</p>
          <span class="avatar"><span class="avatar-initial rounded bg-label-success"><i class="icon-base ti tabler-receipt icon-24px"></i></span></span>
        </div>
        <h3 class="mb-1">{{ number_format($summary['closed_orders'], 0, ',', '.') }}</h3>
        <small class="text-body-secondary">Ortalama adisyon <span class="fw-medium text-heading">{{ money($summary['average_ticket']) }}</span></small>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <p class="mb-0">İptal edilen</p>
          <span class="avatar"><span class="avatar-initial rounded bg-label-danger"><i class="icon-base ti tabler-ban icon-24px"></i></span></span>
        </div>
        <h3 class="mb-1">{{ money($summary['cancelled_amount']) }}</h3>
        <small class="text-body-secondary">{{ $summary['cancelled_items'] }} ürün iptal edildi</small>
      </div>
    </div>
  </div>
</div>

<div class="row g-6">
  <!-- Günlük ciro -->
  <div class="col-12">
    <x-chart-card id="revenue" title="Günlük ciro" subtitle="Tahsil edilen ödemeler (nakit + kart)"
      :empty="$daily->sum('total') == 0" empty-text="Seçilen dönemde ödeme alınmadı.">
      <table class="table table-sm">
        <thead><tr><th>Gün</th><th class="text-end">Ciro</th></tr></thead>
        <tbody>
          @foreach ($daily as $day)
            <tr><td>{{ \Illuminate\Support\Carbon::parse($day['date'])->translatedFormat('d F Y, l') }}</td><td class="text-end">{{ money($day['total']) }}</td></tr>
          @endforeach
        </tbody>
        <tfoot><tr><th>Toplam</th><th class="text-end">{{ money($daily->sum('total')) }}</th></tr></tfoot>
      </table>
    </x-chart-card>
  </div>

  <!-- En çok satanlar -->
  <div class="col-xl-7">
    <x-chart-card id="products" title="En çok satan ürünler" subtitle="Satış adedine göre ilk 10 (iptaller hariç)"
      :empty="$topProducts->isEmpty()" empty-text="Seçilen dönemde satış yok.">
      <table class="table table-sm">
        <thead><tr><th>#</th><th>Ürün</th><th>Kategori</th><th class="text-end">Adet</th><th class="text-end">Tutar</th></tr></thead>
        <tbody>
          @foreach ($topProducts as $product)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="fw-medium text-heading">{{ $product->name }}</td>
              <td>{{ $product->category }}</td>
              <td class="text-end">{{ $product->quantity }}</td>
              <td class="text-end">{{ money($product->revenue) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </x-chart-card>
  </div>

  <!-- Kategori dağılımı -->
  <div class="col-xl-5">
    <x-chart-card id="categories" title="Kategoriye göre satış" subtitle="Satılan ürünlerin tutarı"
      :empty="$categories->isEmpty()" empty-text="Seçilen dönemde satış yok.">
      <table class="table table-sm">
        <thead><tr><th>Kategori</th><th class="text-end">Adet</th><th class="text-end">Tutar</th><th class="text-end">Pay</th></tr></thead>
        <tbody>
          @php $categoryTotal = max(0.01, $categories->sum('revenue')); @endphp
          @foreach ($categories as $category)
            <tr>
              <td class="fw-medium text-heading">{{ $category->name }}</td>
              <td class="text-end">{{ $category->quantity }}</td>
              <td class="text-end">{{ money($category->revenue) }}</td>
              <td class="text-end">%{{ number_format($category->revenue / $categoryTotal * 100, 1, ',', '.') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </x-chart-card>
  </div>

  <!-- Saatlik yoğunluk -->
  <div class="col-12">
    <x-chart-card id="hourly" title="Saatlik yoğunluk"
      :subtitle="$peakHour ? 'Açılan sipariş sayısı · en yoğun saat '.$peakHour['label'].' ('.$peakHour['orders'].' sipariş)' : 'Açılan sipariş sayısı'"
      :empty="$hourly->isEmpty()" empty-text="Seçilen dönemde sipariş açılmadı.">
      <table class="table table-sm">
        <thead><tr><th>Saat</th><th class="text-end">Sipariş</th></tr></thead>
        <tbody>
          @foreach ($hourly as $row)
            <tr><td>{{ $row['label'] }}–{{ str_pad((string) (($row['hour'] + 1) % 24), 2, '0', STR_PAD_LEFT) }}:00</td><td class="text-end">{{ $row['orders'] }}</td></tr>
          @endforeach
        </tbody>
      </table>
    </x-chart-card>
  </div>
</div>

<script>
  // Grafik verisi: resources/assets/js/demlio-reports.js okur
  window.reportData = @json($chartData);
</script>
@endsection
