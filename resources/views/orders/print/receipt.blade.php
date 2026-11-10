@extends('layouts/print')

@section('title', 'Adisyon '.$order->code())

@php
  $paid = (float) $order->paid_total;
  $remaining = $order->remainingAmount();
  $itemCount = $lines->sum('quantity');
@endphp

@section('content')
  <header class="center">
    <span class="logo">@include('_partials.macros', ['height' => 34])</span>
    <div class="xl bold upper">{{ config('kafe.name') }}</div>
    @if (config('kafe.address'))
      <div class="small">{{ config('kafe.address') }}</div>
    @endif
    @if (config('kafe.phone'))
      <div class="small">Tel: {{ config('kafe.phone') }}</div>
    @endif
  </header>

  <hr class="rule-solid">
  <div class="center bold large upper">Adisyon</div>
  <hr class="rule">

  <div class="row"><span>Sipariş</span><span class="bold">{{ $order->code() }}</span></div>
  <div class="row">
    <span>{{ $order->diningTable ? 'Masa' : 'Tür' }}</span>
    <span class="bold">{{ $order->diningTable ? $order->diningTable->label() : $order->type->label() }}</span>
  </div>
  <div class="row"><span>Garson</span><span>{{ $order->user->name }}</span></div>
  <div class="row"><span>Açılış</span><span>{{ $order->opened_at->format('d.m.Y H:i') }}</span></div>
  @if ($order->closed_at && $order->status === \App\Enums\OrderStatus::Kapandi)
    <div class="row"><span>Kapanış</span><span>{{ $order->closed_at->format('d.m.Y H:i') }}</span></div>
  @endif

  @if ($order->status === \App\Enums\OrderStatus::Iptal)
    <div class="box center bold large upper">İptal edildi</div>
  @endif

  <hr class="rule">

  @if ($lines->isEmpty())
    <p class="center muted">Siparişte ürün yok.</p>
  @else
    <table>
      <thead>
        <tr>
          <th class="qty">Ad.</th>
          <th>Ürün</th>
          <th class="amount">Tutar</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($lines as $line)
          <tr>
            <td class="qty">{{ $line['quantity'] }}</td>
            <td>
              {{ $line['name'] }}
              @if ($line['quantity'] > 1)
                <div class="small muted">{{ $line['quantity'] }} × {{ money($line['unit_price']) }}</div>
              @endif
            </td>
            <td class="amount">{{ money($line['total']) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <hr class="rule">
  <div class="row small"><span>Ürün adedi</span><span>{{ $itemCount }}</span></div>
  <div class="row xl bold"><span>TOPLAM</span><span>{{ money($order->total) }}</span></div>

  @if ($order->payments->isNotEmpty())
    <hr class="rule">
    @foreach ($order->payments as $payment)
      <div class="row">
        <span>{{ $payment->method->label() }} · {{ $payment->paid_at->format('H:i') }}</span>
        <span>{{ money($payment->amount) }}</span>
      </div>
    @endforeach
    <div class="row bold"><span>Ödenen</span><span>{{ money($paid) }}</span></div>
  @endif

  @if ($order->status !== \App\Enums\OrderStatus::Iptal && $lines->isNotEmpty())
    @if ($remaining > 0)
      <div class="box row large bold"><span>Kalan</span><span>{{ money($remaining) }}</span></div>
    @else
      <div class="box center large bold upper">Ödendi</div>
    @endif
  @endif

  <hr class="rule">
  <footer class="center">
    @if (config('kafe.receipt_footer'))
      <div>{{ config('kafe.receipt_footer') }}</div>
    @endif
    <div class="small muted" style="margin-top: 6px">Bu belge bilgi amaçlıdır, mali değeri yoktur.</div>
    <div class="small muted">Yazdırma: {{ now()->format('d.m.Y H:i') }}</div>
  </footer>
@endsection
