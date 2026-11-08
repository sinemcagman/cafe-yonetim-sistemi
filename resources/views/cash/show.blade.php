@extends('layouts/layoutMaster')

@section('title', 'Kasa Oturumu')

@php
  $diff = $session->difference !== null ? (float) $session->difference : null;
@endphp

@section('content')
<x-page-header :title="'Kasa · '.$session->opened_at->translatedFormat('d F Y')" icon="tabler-cash-register"
  :subtitle="$session->opener->name.' açtı '.$session->opened_at->format('H:i').($session->closed_at ? ' · '.($session->closer?->name ?? '').' kapattı '.$session->closed_at->format('H:i') : ' · açık')">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item">
      <a href="{{ route('cash.index') }}">Kasa</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item">
      <a href="{{ route('cash.history') }}">Geçmiş</a>
      <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
    </li>
    <li class="breadcrumb-item active">Oturum</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <x-status-badge :status="$session->status" class="fs-6" />
  </x-slot:actions>
</x-page-header>

<div class="row g-6">
  <div class="col-xl-4">
    <div class="card">
      <div class="card-header"><h5 class="card-title mb-0">Özet</h5></div>
      <div class="card-body">
        <div class="d-flex justify-content-between mb-2"><span>Açılış nakdi</span><span>{{ money($summary['opening']) }}</span></div>
        <div class="d-flex justify-content-between mb-2"><span>Nakit tahsilat</span><span class="text-success">+{{ money($summary['cash_sales']) }}</span></div>
        <div class="d-flex justify-content-between mb-2"><span>Kart tahsilat</span><span>{{ money($summary['card_sales']) }}</span></div>
        <div class="d-flex justify-content-between mb-2"><span>Elle giriş</span><span class="text-success">+{{ money($summary['manual_in']) }}</span></div>
        <div class="d-flex justify-content-between mb-3"><span>Elle çıkış</span><span class="text-danger">−{{ money($summary['manual_out']) }}</span></div>
        <div class="d-flex justify-content-between border-top pt-3 mb-2">
          <span class="fw-medium">Beklenen nakit</span><span class="fw-medium text-heading">{{ money($summary['expected_cash']) }}</span>
        </div>
        @if ($session->closing_amount !== null)
          <div class="d-flex justify-content-between mb-2"><span class="fw-medium">Sayılan nakit</span><span class="fw-medium text-heading">{{ money($session->closing_amount) }}</span></div>
          <div @class(['d-flex justify-content-between rounded p-3 mt-3', 'bg-label-success' => $diff == 0, 'bg-label-danger' => $diff != 0])>
            <span class="fw-medium">Fark</span>
            <span class="fw-medium">{{ $diff == 0 ? 'Tuttu' : ($diff > 0 ? '+' : '−').money(abs($diff)).($diff > 0 ? ' fazla' : ' eksik') }}</span>
          </div>
        @endif
        @if ($session->note)
          <p class="small text-body-secondary mt-4 mb-0" style="white-space: pre-line">{{ $session->note }}</p>
        @endif
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title mb-0">Hareketler</h5>
        <span class="badge bg-label-primary">{{ $movements->count() }} kayıt</span>
      </div>
      @if ($movements->isEmpty())
        <x-empty-state icon="tabler-arrows-exchange" title="Bu oturumda hareket yok" />
      @else
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr><th>Saat</th><th>Açıklama</th><th>Yöntem</th><th>Çalışan</th><th class="text-end">Tutar</th></tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @foreach ($movements as $movement)
                @php $in = $movement->type === \App\Enums\CashMovementType::Giris; @endphp
                <tr>
                  <td>{{ $movement->created_at->format('H:i') }}</td>
                  <td class="text-wrap" style="min-width: 200px">
                    @if ($movement->payment?->order)
                      <a href="{{ route('orders.show', $movement->payment->order) }}" class="text-heading">{{ $movement->description }}</a>
                    @else
                      {{ $movement->description }}
                    @endif
                  </td>
                  <td><x-status-badge :status="$movement->method" /></td>
                  <td>{{ $movement->user->name }}</td>
                  <td @class(['text-end fw-medium', 'text-success' => $in, 'text-danger' => ! $in])>{{ $in ? '+' : '−' }}{{ money($movement->amount) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
