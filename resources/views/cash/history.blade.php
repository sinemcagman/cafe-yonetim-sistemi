@extends('layouts/layoutMaster')

@section('title', 'Kasa Geçmişi')

@section('content')
<x-page-header title="Kasa Geçmişi" subtitle="Günlük kasa raporu ve geçmiş kasa oturumları." icon="tabler-history">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('cash.index') }}">Kasa</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">Geçmiş</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="row g-6">
  <!-- Günlük rapor (sp_gunluk_kasa_raporu) -->
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-1">Günlük kasa raporu</h5>
        <p class="card-subtitle mb-0">{{ $reportDate->translatedFormat('d F Y, l') }}</p>
      </div>
      <div class="card-body">
        <form method="GET" action="{{ route('cash.history') }}" class="mb-6">
          @foreach (request()->except(['rapor', 'page']) as $key => $value)
            @if (is_scalar($value))
              <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
          @endforeach
          <label class="form-label" for="report-date">Gün</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
            <input type="text" name="rapor" id="report-date" class="form-control" value="{{ $reportDate->toDateString() }}"
              data-datepicker="single" data-max-date="{{ today()->toDateString() }}" data-auto-submit>
          </div>
        </form>

        @if ($report->isEmpty())
          <x-empty-state icon="tabler-receipt-off" title="Bu gün tahsilat yok" class="py-4" />
        @else
          @foreach ($report as $row)
            <div class="d-flex align-items-center mb-4">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $row['method']->color() }}">
                  <i class="icon-base ti {{ $row['method'] === \App\Enums\PaymentMethod::Nakit ? 'tabler-cash' : 'tabler-credit-card' }} icon-20px"></i>
                </span>
              </div>
              <div class="d-flex w-100 justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0">{{ $row['method']->label() }}</h6>
                  <small class="text-body-secondary">{{ $row['count'] }} ödeme · {{ $row['orders'] }} sipariş</small>
                </div>
                <span class="fw-medium text-heading">{{ money($row['total']) }}</span>
              </div>
            </div>
          @endforeach
          <div class="d-flex justify-content-between border-top pt-4">
            <h6 class="mb-0">Toplam</h6>
            <h5 class="mb-0 text-primary">{{ money($report->sum('total')) }}</h5>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Oturumlar -->
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header border-bottom">
        <form method="GET" action="{{ route('cash.history') }}" class="row g-4 align-items-end">
          <input type="hidden" name="rapor" value="{{ request('rapor') }}">
          <div class="col-md-6">
            <label class="form-label" for="session-date">Oturum tarihi</label>
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
              <input type="text" name="tarih" id="session-date" class="form-control" value="{{ request('tarih') }}"
                placeholder="Tarih aralığı seçin" data-datepicker="range" data-max-date="{{ today()->toDateString() }}" data-auto-submit>
            </div>
          </div>
          <div class="col-md-6">
            @if (request()->filled('tarih'))
              <a href="{{ route('cash.history', request()->only('rapor')) }}" class="btn btn-label-secondary">Temizle</a>
            @endif
          </div>
        </form>
      </div>
      @if ($sessions->isEmpty())
        <x-empty-state icon="tabler-cash-register" title="Kasa oturumu bulunamadı" />
      @else
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Açılış</th>
                <th>Kapanış</th>
                <th>Açan / Kapatan</th>
                <th class="text-center">Ödeme</th>
                <th class="text-end">Sayılan</th>
                <th class="text-end">Fark</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @foreach ($sessions as $session)
                @php $diff = $session->difference !== null ? (float) $session->difference : null; @endphp
                <tr>
                  <td><a href="{{ route('cash.sessions.show', $session) }}" class="fw-medium text-heading">{{ $session->opened_at->format('d.m.Y H:i') }}</a></td>
                  <td>
                    @if ($session->closed_at)
                      {{ $session->closed_at->format('d.m.Y H:i') }}
                    @else
                      <x-status-badge :status="$session->status" />
                    @endif
                  </td>
                  <td>{{ $session->opener->name }}@if ($session->closer && $session->closer->isNot($session->opener)) <span class="text-body-secondary">/ {{ $session->closer->name }}</span>@endif</td>
                  <td class="text-center">{{ $session->payments_count }}</td>
                  <td class="text-end">{{ $session->closing_amount !== null ? money($session->closing_amount) : '—' }}</td>
                  <td class="text-end">
                    @if ($diff === null)
                      —
                    @elseif ($diff == 0)
                      <span class="badge bg-label-success">Tuttu</span>
                    @else
                      <span class="badge bg-label-danger">{{ $diff > 0 ? '+' : '−' }}{{ money(abs($diff)) }}</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <x-list-footer :paginator="$sessions" unit="oturum" />
      @endif
    </div>
  </div>
</div>
@endsection
