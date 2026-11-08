@extends('layouts/layoutMaster')

@section('title', 'Hazırlık')

@php
  use App\Enums\OrderType;
@endphp

@section('content')
<x-page-header title="Hazırlık" subtitle="Mutfak ve bar için sipariş panosu. Ekran kendini otomatik yeniler." icon="tabler-chef-hat">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Hazırlık</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <span class="small text-body-secondary d-flex align-items-center gap-1 me-2" aria-live="polite">
      <span class="badge badge-dot bg-success" data-refresh-dot></span>
      Son güncelleme <span data-updated-at>{{ now()->format('H:i:s') }}</span>
    </span>
    <button type="button" class="btn btn-label-secondary" data-sound-toggle aria-pressed="false">
      <i class="icon-base ti tabler-volume-off icon-16px me-1" data-sound-icon></i> <span data-sound-label>Ses kapalı</span>
    </button>
    <button type="button" class="btn btn-label-secondary btn-icon" data-fullscreen data-bs-toggle="tooltip" title="Tam ekran" aria-label="Tam ekran">
      <i class="icon-base ti tabler-maximize icon-18px"></i>
    </button>
  </x-slot:actions>
</x-page-header>

<!-- Tür filtresi -->
<div class="d-flex flex-wrap gap-2 mb-6" role="group" aria-label="Sipariş türü">
  <a href="{{ route('preparation.index') }}" @class(['btn btn-sm rounded-pill', 'btn-primary' => ! request('tur'), 'btn-label-secondary' => request('tur')])>Tümü</a>
  @foreach ($types as $type)
    <a href="{{ route('preparation.index', ['tur' => $type->value]) }}"
      @class(['btn btn-sm rounded-pill', 'btn-primary' => request('tur') === $type->value, 'btn-label-secondary' => request('tur') !== $type->value])>{{ $type->label() }}</a>
  @endforeach
</div>

<div id="preparation-board">
  @include('preparation._board')
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const board = document.getElementById('preparation-board');
    const updatedAt = document.querySelector('[data-updated-at]');
    const dot = document.querySelector('[data-refresh-dot]');
    const soundButton = document.querySelector('[data-sound-toggle]');
    const REFRESH_MS = 15000;
    let newCount = parseInt(document.querySelector('[data-column-count="yeni"]')?.textContent || '0', 10);

    // Ses tercihi yalnızca bu tarayıcıda saklanır
    let soundOn = false;
    try { soundOn = localStorage.getItem('demlio-hazirlik-ses') === '1'; } catch (e) {}
    const renderSound = () => {
      soundButton.setAttribute('aria-pressed', soundOn ? 'true' : 'false');
      soundButton.querySelector('[data-sound-icon]').className = `icon-base ti ${soundOn ? 'tabler-volume' : 'tabler-volume-off'} icon-16px me-1`;
      soundButton.querySelector('[data-sound-label]').textContent = soundOn ? 'Ses açık' : 'Ses kapalı';
    };
    soundButton.addEventListener('click', () => {
      soundOn = !soundOn;
      try { localStorage.setItem('demlio-hazirlik-ses', soundOn ? '1' : '0'); } catch (e) {}
      renderSound();
      if (soundOn) beep();
    });
    renderSound();

    // Yeni sipariş geldiğinde kısa bir uyarı sesi (dosya gerektirmez)
    const beep = () => {
      try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [0, 0.18].forEach(offset => {
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();
          osc.frequency.value = 880;
          gain.gain.setValueAtTime(0.15, ctx.currentTime + offset);
          gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + offset + 0.15);
          osc.connect(gain).connect(ctx.destination);
          osc.start(ctx.currentTime + offset);
          osc.stop(ctx.currentTime + offset + 0.16);
        });
      } catch (e) {}
    };

    const initTooltips = () => board.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    const refresh = async () => {
      dot.classList.replace('bg-success', 'bg-warning');
      try {
        const response = await fetch(window.location.href, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error(response.status);
        const data = await response.json();
        board.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => bootstrap.Tooltip.getInstance(el)?.dispose());
        board.innerHTML = data.board;
        initTooltips();
        updatedAt.textContent = data.updatedAt;
        if (soundOn && data.counts.yeni > newCount) beep();
        newCount = data.counts.yeni;
        dot.classList.replace('bg-warning', 'bg-success');
      } catch (e) {
        dot.classList.replace('bg-warning', 'bg-danger');
      }
    };

    setInterval(() => { if (!document.hidden) refresh(); }, REFRESH_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });

    // Durum butonları: sayfa yenilenmeden ilerlet, panoyu hemen güncelle
    board.addEventListener('submit', async event => {
      const form = event.target.closest('[data-advance]');
      if (!form) return;
      event.preventDefault();
      const button = form.querySelector('button');
      button.disabled = true;
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: new FormData(form)
        });
        const data = await response.json();
        window.notify(data.ok ? 'success' : 'error', data.message);
      } catch (e) {
        form.submit();
        return;
      }
      refresh();
    });

    // Tablet için tam ekran
    document.querySelector('[data-fullscreen]').addEventListener('click', () => {
      if (document.fullscreenElement) document.exitFullscreen();
      else document.documentElement.requestFullscreen?.();
    });
  });
</script>
@endpush
