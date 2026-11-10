@php
  // Müşterinin telefonu hangi temadaysa menü de onu izler
  $pageConfigs = ['myTheme' => 'system'];
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Menü')

@section('page-style')
<style>
  .qr-menu { max-width: 720px; margin: 0 auto; padding: 0 1rem 3rem; }
  .qr-menu-hero { padding: 2rem 0 1.25rem; text-align: center; }
  .qr-menu-nav {
    position: sticky; top: 0; z-index: 5;
    margin: 0 -1rem; padding: .75rem 1rem;
    background: var(--bs-body-bg);
    border-bottom: 1px solid var(--bs-border-color);
  }
  .qr-menu-chips { display: flex; gap: .5rem; overflow-x: auto; scrollbar-width: none; scroll-snap-type: x proximity; }
  .qr-menu-chips::-webkit-scrollbar { display: none; }
  .qr-menu-chips .btn { flex-shrink: 0; scroll-snap-align: start; white-space: nowrap; }
  .qr-menu-section { scroll-margin-top: 8.5rem; padding-top: 1.75rem; }
  .qr-menu-item { display: flex; gap: 1rem; align-items: flex-start; padding: 1rem 0; border-bottom: 1px dashed var(--bs-border-color); }
  .qr-menu-item:last-child { border-bottom: 0; }
  .qr-menu-item.is-soldout { opacity: .55; }
  .qr-menu-item.is-soldout .qr-menu-price { text-decoration: line-through; }
  .qr-menu-price { white-space: nowrap; font-weight: 600; color: var(--bs-primary); font-size: 1.0625rem; }
</style>
@endsection

@section('content')
<div class="qr-menu">
  <header class="qr-menu-hero">
    <div class="d-inline-flex align-items-center gap-2 mb-3">
      @include('_partials.macros', ['height' => 44])
      @include('_partials.wordmark', ['height' => 30])
    </div>
    @if ($table)
      <div class="mb-2">
        <span class="badge bg-label-primary fs-6 px-3 py-2">
          <i class="icon-base ti tabler-armchair icon-16px me-1"></i>{{ $table->label() }}'e hoş geldiniz
        </span>
      </div>
    @endif
    <p class="text-body-secondary mb-0">Afiyetle seçin, siparişiniz için garsonumuza seslenmeniz yeterli.</p>
  </header>

  @if ($categories->isEmpty())
    <div class="card">
      <x-empty-state icon="tabler-coffee" title="Menü hazırlanıyor" text="Şu anda gösterilecek ürün yok. Lütfen garsonumuza danışın." />
    </div>
  @else
    <nav class="qr-menu-nav" aria-label="Kategoriler">
      <div class="input-group input-group-merge mb-3">
        <span class="input-group-text"><i class="icon-base ti tabler-search icon-18px"></i></span>
        <input type="search" class="form-control" placeholder="Menüde ara…" aria-label="Menüde ara" data-menu-search>
      </div>
      <div class="qr-menu-chips" data-menu-chips>
        @foreach ($categories as $category)
          <a href="#kategori-{{ $category->id }}" class="btn btn-sm rounded-pill {{ $loop->first ? 'btn-primary' : 'btn-label-secondary' }}" data-chip="kategori-{{ $category->id }}">
            <i class="{{ $category->iconClass() }} icon-16px me-1"></i>{{ $category->name }}
          </a>
        @endforeach
      </div>
    </nav>

    @foreach ($categories as $category)
      <section id="kategori-{{ $category->id }}" class="qr-menu-section" data-menu-section>
        <h2 class="h5 d-flex align-items-center gap-2 mb-1">
          <span class="avatar avatar-sm"><span class="avatar-initial rounded bg-label-primary"><i class="{{ $category->iconClass() }} icon-18px"></i></span></span>
          {{ $category->name }}
        </h2>
        <div>
          @foreach ($category->products as $product)
            @php $soldOut = $product->stock_quantity <= 0; @endphp
            <article @class(['qr-menu-item', 'is-soldout' => $soldOut]) data-menu-item="{{ mb_strtolower($product->name.' '.$product->description, 'UTF-8') }}">
              <div class="flex-grow-1">
                <h3 class="h6 mb-1 d-flex align-items-center gap-2 flex-wrap">
                  {{ $product->name }}
                  @if ($soldOut)
                    <span class="badge bg-label-secondary">Tükendi</span>
                  @endif
                </h3>
                @if ($product->description)
                  <p class="small text-body-secondary mb-0">{{ $product->description }}</p>
                @endif
              </div>
              <span class="qr-menu-price">{{ money($product->price) }}</span>
            </article>
          @endforeach
        </div>
      </section>
    @endforeach

    <p class="text-center text-body-secondary py-8 d-none" data-menu-empty>
      <i class="icon-base ti tabler-mood-empty icon-28px d-block mx-auto mb-2"></i>
      Aramanıza uygun ürün bulunamadı.
    </p>
  @endif

  <footer class="text-center text-body-secondary small pt-8">
    <div class="fw-medium text-heading">{{ config('kafe.name') }}</div>
    @if (config('kafe.address'))
      <div>{{ config('kafe.address') }}</div>
    @endif
    @if (config('kafe.phone'))
      <div><a href="tel:{{ preg_replace('/\D/', '', config('kafe.phone')) }}">{{ config('kafe.phone') }}</a></div>
    @endif
    <div class="mt-2">Fiyatlara KDV dahildir.</div>
  </footer>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const chips = document.querySelectorAll('[data-chip]');
    const sections = document.querySelectorAll('[data-menu-section]');
    const activate = id => chips.forEach(chip => {
      const active = chip.dataset.chip === id;
      chip.classList.toggle('btn-primary', active);
      chip.classList.toggle('btn-label-secondary', !active);
      if (active) chip.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    });

    // Kaydırdıkça görünen kategorinin çipi seçili olsun
    const observer = new IntersectionObserver(entries => {
      entries.filter(e => e.isIntersecting).forEach(e => activate(e.target.id));
    }, { rootMargin: '-40% 0px -55% 0px' });
    sections.forEach(section => observer.observe(section));

    // Ürün adı ya da açıklamasına göre anında filtre
    const search = document.querySelector('[data-menu-search]');
    const empty = document.querySelector('[data-menu-empty]');
    search?.addEventListener('input', () => {
      const term = search.value.trim().toLocaleLowerCase('tr-TR');
      let visible = 0;
      sections.forEach(section => {
        let shown = 0;
        section.querySelectorAll('[data-menu-item]').forEach(item => {
          const match = !term || item.dataset.menuItem.includes(term);
          item.classList.toggle('d-none', !match);
          shown += match;
        });
        section.classList.toggle('d-none', shown === 0);
        visible += shown;
      });
      empty.classList.toggle('d-none', visible > 0);
    });
  });
</script>
@endsection
