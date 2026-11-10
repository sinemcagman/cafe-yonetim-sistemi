{{--
  80 mm termal yazıcı düzeni. Vuexy stillerini yüklemez: çıktı yalnızca siyah metin ve çizgilerden oluşur.
  Ekranda kâğıt önizlemesi ve araç çubuğu görünür; yazdırırken yalnızca kâğıt basılır.
  ?yazdir=1 ile açılırsa yazdırma penceresi kendiliğinden açılır.
--}}
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>@yield('title') | {{ config('kafe.name') }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('assets/img/branding/favicon.svg') }}?v=2">
  <style>
    @page { size: 80mm auto; margin: 0; }

    * { box-sizing: border-box; }

    html, body { margin: 0; padding: 0; }

    body {
      background: #ece6df;
      color: #000;
      font-family: "Courier New", Courier, monospace;
      font-size: 12px;
      line-height: 1.35;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .toolbar {
      position: sticky;
      top: 0;
      display: flex;
      gap: 8px;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      padding: 12px 16px;
      background: #3b2a20;
      font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
      font-size: 14px;
      z-index: 1;
    }

    .toolbar .hint { color: #e9dccf; margin-right: 8px; }

    .toolbar button,
    .toolbar a {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border: 1px solid #c58b5a;
      border-radius: 6px;
      background: transparent;
      color: #f5ebe0;
      font: inherit;
      text-decoration: none;
      cursor: pointer;
    }

    .toolbar button.primary { background: #c58b5a; border-color: #c58b5a; color: #2a1d15; font-weight: 600; }

    .toolbar a[aria-current="page"] { background: rgba(197, 139, 90, .25); }

    .toolbar button:focus-visible,
    .toolbar a:focus-visible { outline: 2px solid #f5ebe0; outline-offset: 2px; }

    .paper {
      width: 80mm;
      margin: 24px auto;
      padding: 5mm 4mm 8mm;
      background: #fff;
      box-shadow: 0 2px 12px rgba(59, 42, 32, .25);
    }

    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: 700; }
    .small { font-size: 10.5px; }
    .large { font-size: 15px; }
    .xl { font-size: 20px; line-height: 1.2; }
    .upper { text-transform: uppercase; }
    .muted { color: #333; }

    .rule { border: 0; border-top: 1px dashed #000; margin: 6px 0; }
    .rule-solid { border: 0; border-top: 2px solid #000; margin: 6px 0; }

    .row { display: flex; justify-content: space-between; gap: 8px; }
    .row > :last-child { text-align: right; white-space: nowrap; }

    .logo { display: block; margin: 0 auto 4px; }
    .logo .demlio-mark-cup,
    .logo .demlio-mark-steam { fill: #000; }

    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 2px 0; vertical-align: top; }
    th { font-weight: 700; text-align: left; border-bottom: 1px solid #000; }
    td.qty, th.qty { width: 2.6em; }
    td.amount, th.amount { text-align: right; white-space: nowrap; padding-left: 6px; }

    .box { border: 2px solid #000; padding: 4px 6px; margin: 6px 0; }

    @media print {
      body { background: #fff; }
      .toolbar { display: none; }
      .paper { width: auto; margin: 0; padding: 2mm 4mm 6mm; box-shadow: none; }
    }
  </style>
</head>
<body>
  <nav class="toolbar" aria-label="Yazdırma">
    <span class="hint">@yield('title') · 80 mm önizleme</span>
    @yield('toolbar')
    <button type="button" class="primary" onclick="window.print()" autofocus>Yazdır</button>
    <a href="{{ $backUrl ?? url()->previous() }}">Geri dön</a>
  </nav>

  <main class="paper">
    @yield('content')
  </main>

  @if (request()->boolean('yazdir'))
    <script>
      window.addEventListener('load', () => setTimeout(() => window.print(), 150));
    </script>
  @endif
</body>
</html>
