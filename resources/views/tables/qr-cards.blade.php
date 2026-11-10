@extends('layouts/print')

@section('title', 'Masa QR Kartları')
@section('paper-size', 'A4')
@section('paper-margin', '10mm')
@section('paper-width', '210mm')
@section('paper-label', 'A4')

@section('styles')
<style>
  .paper { padding: 10mm; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }

  .cards { display: grid; grid-template-columns: repeat(2, 1fr); }

  /* Kesim çizgileri: kartlar makasla ayrılsın diye kesikli kenar */
  .card {
    height: 92mm;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2mm;
    padding: 6mm;
    border: 1px dashed #b9a99a;
    margin: 0 -1px -1px 0;
    text-align: center;
    break-inside: avoid;
  }

  .card .brand { display: flex; align-items: center; gap: 2mm; }
  .card .brand .demlio-mark-cup,
  .card .brand .demlio-wordmark { fill: #4a3022; }
  .card .brand .demlio-mark-steam { fill: #b2622a; }

  .card .qr { width: 44mm; height: 44mm; }
  .card .qr svg { width: 100%; height: 100%; display: block; }

  .card .scan { font-size: 11pt; color: #4a3022; letter-spacing: .02em; }
  .card .table { font-size: 20pt; font-weight: 700; color: #2a1d15; line-height: 1.1; }
  .card .area { font-size: 9pt; color: #6b5a4d; }

  .notice {
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    max-width: 210mm;
    margin: 16px auto 0;
    padding: 10px 14px;
    border-radius: 6px;
    background: #fff4e5;
    color: #6b3d00;
    font-size: 14px;
  }

  .empty { padding: 30mm 0; text-align: center; font-size: 14px; }

  @media print {
    .notice { display: none; }
    .paper { padding: 0; }
  }
</style>
@endsection

@section('content')
  @if ($cards->isEmpty())
    <p class="empty">Kart basılacak aktif masa yok.</p>
  @else
    <div class="cards">
      @foreach ($cards as $card)
        <section class="card">
          <div class="brand">
            @include('_partials.macros', ['height' => 30])
            @include('_partials.wordmark', ['height' => 20])
          </div>
          <div class="qr">{!! $card['svg'] !!}</div>
          <div class="scan">Menü için kamerayla okutun</div>
          <div class="table">{{ $card['title'] }}</div>
          @if ($card['subtitle'])
            <div class="area">{{ $card['subtitle'] }}</div>
          @endif
        </section>
      @endforeach
    </div>
  @endif
@endsection

@if ($localHost)
  @section('notice')
    <div class="notice" role="alert">
      QR kodlar <strong>{{ route('menu') }}</strong> adresini açıyor. Bu adrese yalnızca bu bilgisayardan ulaşılır;
      kartları basmadan önce uygulamayı müşterilerin telefonlarının erişebildiği adresten açın.
    </div>
  @endsection
@endif
