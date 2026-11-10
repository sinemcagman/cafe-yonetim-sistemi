@extends('layouts/print')

@section('title', 'Mutfak Fişi '.$order->code())

@php
  $query = array_filter(['kaynak' => request('kaynak')]);
@endphp

@if ($lateIds->isNotEmpty())
  @section('toolbar')
    <a href="{{ route('orders.print.kitchen', [$order, ...$query]) }}" @if (! $onlyLate) aria-current="page" @endif>Tüm ürünler</a>
    <a href="{{ route('orders.print.kitchen', [$order, 'sadece' => 'yeni', ...$query]) }}" @if ($onlyLate) aria-current="page" @endif>Yalnızca sonradan eklenenler ({{ $lateIds->count() }})</a>
  @endsection
@endif

@section('content')
  <div class="center bold upper">{{ $order->type->label() }} siparişi</div>
  <div class="center bold upper" style="font-size: 28px; line-height: 1.15; margin: 2px 0 4px">
    {{ $order->diningTable ? $order->diningTable->label() : $order->type->label() }}
  </div>
  <div class="row large bold"><span>{{ $order->code() }}</span><span>{{ now()->format('H:i') }}</span></div>
  <div class="row small"><span>{{ $order->user->name }}</span><span>açılış {{ $order->opened_at->format('H:i') }}</span></div>

  @if ($onlyLate)
    <div class="box center bold large upper">Ek sipariş</div>
  @endif

  <hr class="rule-solid">

  @forelse ($items as $item)
    @php $late = ! $onlyLate && $lateIds->contains($item->id); @endphp
    <div class="row xl" style="justify-content: flex-start; gap: 10px; padding: 3px 0">
      <span class="bold" style="min-width: 2.4em">{{ $item->quantity }}×</span>
      <span class="bold" style="text-align: left; white-space: normal">{{ $item->product->name }}</span>
    </div>
    @if ($late)
      <div class="small bold upper" style="padding-left: 3.4em">+ sonradan eklendi</div>
    @endif
  @empty
    <p class="center">Hazırlanacak ürün yok.</p>
  @endforelse

  <hr class="rule-solid">

  @if ($order->note)
    <div class="box">
      <div class="small bold upper">Not</div>
      <div class="large bold">{{ $order->note }}</div>
    </div>
  @endif

  <div class="row small"><span>Toplam {{ $items->sum('quantity') }} ürün</span><span>{{ now()->format('d.m.Y') }}</span></div>
@endsection
