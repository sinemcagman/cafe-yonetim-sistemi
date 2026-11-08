@extends('layouts/layoutMaster')

@section('title', 'Kasa')

@section('content')
<x-page-header title="Kasa" icon="tabler-cash-register"
  :subtitle="$session ? 'Açık · '.$session->opener->name.' · '.$session->opened_at->translatedFormat('d F H:i').' itibarıyla' : 'Kasa şu anda kapalı.'">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Kasa</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <a href="{{ route('cash.history') }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-history icon-16px me-1"></i> Kasa Geçmişi
    </a>
    @if ($session)
      <button type="button" class="btn btn-label-primary" data-bs-toggle="offcanvas" data-bs-target="#cashMovementPanel">
        <i class="icon-base ti tabler-arrows-exchange icon-16px me-1"></i> Kasa Hareketi
      </button>
      <button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#cashClosePanel">
        <i class="icon-base ti tabler-lock icon-16px me-1"></i> Kasayı Kapat
      </button>
    @endif
  </x-slot:actions>
</x-page-header>

@if (! $session)
  <!-- Kasa kapalı: açma formu -->
  <div class="row g-6">
    <div class="col-lg-5">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            <div class="avatar avatar-lg">
              <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-cash-register icon-28px"></i></span>
            </div>
            <div>
              <h5 class="mb-0">Kasayı Aç</h5>
              <small class="text-body-secondary">Mesai başında kasadaki nakdi sayıp girin.</small>
            </div>
          </div>
          <x-form-errors />
          <form method="POST" action="{{ route('cash.open') }}">
            @csrf
            <div class="mb-4">
              <label class="form-label" for="opening-amount">Başlangıç nakdi</label>
              <div class="input-group">
                <input type="text" inputmode="decimal" name="opening_amount" id="opening-amount" class="form-control form-control-lg"
                  value="{{ old('opening_amount', $lastSession?->closing_amount !== null ? number_format((float) $lastSession->closing_amount, 2, ',', '') : '') }}"
                  placeholder="0,00" required autofocus>
                <span class="input-group-text">₺</span>
              </div>
              @if ($lastSession?->closing_amount !== null)
                <div class="form-text">Önceki kapanışta sayılan tutar önerildi: {{ money($lastSession->closing_amount) }}</div>
              @endif
            </div>
            <div class="mb-6">
              <label class="form-label" for="opening-note">Not <span class="text-body-secondary">(isteğe bağlı)</span></label>
              <input type="text" name="note" id="opening-note" class="form-control" maxlength="500" value="{{ old('note') }}" placeholder="Örn. Sabah vardiyası">
            </div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="icon-base ti tabler-lock-open icon-18px me-1"></i> Kasayı Aç
            </button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Son kasa oturumu</h5>
        </div>
        <div class="card-body">
          @if ($lastSession)
            @php $diff = (float) $lastSession->difference; @endphp
            <dl class="row mb-4">
              <dt class="col-6 fw-normal text-body-secondary">Kapanış</dt>
              <dd class="col-6 text-end">{{ $lastSession->closed_at?->format('d.m.Y H:i') }}</dd>
              <dt class="col-6 fw-normal text-body-secondary">Beklenen nakit</dt>
              <dd class="col-6 text-end">{{ money($lastSession->expected_amount) }}</dd>
              <dt class="col-6 fw-normal text-body-secondary">Sayılan nakit</dt>
              <dd class="col-6 text-end">{{ money($lastSession->closing_amount) }}</dd>
              <dt class="col-6 fw-normal text-body-secondary">Fark</dt>
              <dd @class(['col-6 text-end fw-medium', 'text-success' => $diff == 0, 'text-danger' => $diff != 0])>
                {{ $diff == 0 ? 'Yok' : ($diff > 0 ? '+' : '−').money(abs($diff)) }}
              </dd>
            </dl>
            <a href="{{ route('cash.sessions.show', $lastSession) }}" class="btn btn-sm btn-label-secondary">Oturum detayı</a>
          @else
            <x-empty-state icon="tabler-history" title="Henüz kasa oturumu yok" text="İlk kasayı açtığınızda özetler burada görünür." class="py-6" />
          @endif
        </div>
      </div>
    </div>
  </div>
@else
  <!-- Oturum özeti -->
  <div class="card mb-6">
    <div class="card-widget-separator-wrapper">
      <div class="card-body card-widget-separator">
        <div class="row gy-4 gy-sm-1">
          @foreach ([
            ['Açılış nakdi', $summary['opening'], 'tabler-wallet', 'secondary', null],
            ['Nakit tahsilat', $summary['cash_sales'], 'tabler-cash', 'success', $summary['payment_count'].' ödeme'],
            ['Kart tahsilat', $summary['card_sales'], 'tabler-credit-card', 'info', null],
            ['Kasada olması gereken', $summary['expected_cash'], 'tabler-cash-register', 'primary', 'Elle giriş '.money($summary['manual_in']).' · çıkış '.money($summary['manual_out'])],
          ] as [$label, $value, $icon, $color, $hint])
            <div class="col-sm-6 col-lg-3">
              <div @class(['d-flex justify-content-between align-items-start pb-4 pb-sm-0', 'border-end' => ! $loop->last])>
                <div>
                  <p class="mb-1">{{ $label }}</p>
                  <h4 @class(['mb-1', 'text-primary' => $loop->last])>{{ money($value) }}</h4>
                  @if ($hint)
                    <p class="mb-0 small text-body-secondary">{{ $hint }}</p>
                  @endif
                </div>
                <span class="avatar me-sm-6"><span class="avatar-initial rounded bg-label-{{ $color }}"><i class="icon-base ti {{ $icon }} icon-26px"></i></span></span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="row g-6">
    <!-- Hareketler -->
    <div class="col-xl-8">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-bottom">
          <h5 class="card-title mb-0">Kasa hareketleri</h5>
          <span class="badge bg-label-primary">{{ $movements->total() }} kayıt</span>
        </div>
        @if ($movements->isEmpty())
          <x-empty-state icon="tabler-arrows-exchange" title="Henüz hareket yok" text="Alınan ödemeler ve elle girilen kasa hareketleri burada listelenir." />
        @else
          <div class="table-responsive text-nowrap">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Saat</th>
                  <th>Açıklama</th>
                  <th>Yöntem</th>
                  <th>Çalışan</th>
                  <th class="text-end">Tutar</th>
                </tr>
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
          <x-list-footer :paginator="$movements" unit="hareket" />
        @endif
      </div>
    </div>

    <!-- Ödeme bekleyen siparişler -->
    <div class="col-xl-4">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-1">Ödeme bekleyen</h5>
            <p class="card-subtitle mb-0">Açık hesaplar</p>
          </div>
          <span class="badge bg-label-warning">{{ money($awaitingPayment->sum(fn ($o) => $o->remainingAmount())) }}</span>
        </div>
        <div class="card-body">
          @forelse ($awaitingPayment as $order)
            <a href="{{ route('orders.show', $order) }}" class="d-flex align-items-center text-reset {{ $loop->last ? '' : 'mb-4' }}">
              <div class="avatar flex-shrink-0 me-3">
                <span class="avatar-initial rounded bg-label-{{ $order->status->color() }}"><i class="icon-base ti {{ $order->status->icon() }} icon-20px"></i></span>
              </div>
              <div class="d-flex w-100 justify-content-between align-items-center gap-2">
                <div>
                  <h6 class="mb-0">{{ $order->label() }}</h6>
                  <small class="text-body-secondary">{{ $order->status->label() }} · {{ $order->opened_at->diffForHumans() }}</small>
                </div>
                <span class="fw-medium text-heading text-nowrap">{{ money($order->remainingAmount()) }}</span>
              </div>
            </a>
          @empty
            <x-empty-state icon="tabler-circle-check" title="Bekleyen hesap yok" class="py-4" />
          @endforelse
        </div>
      </div>
    </div>
  </div>

  @include('cash._movement-panel')
  @include('cash._close-panel')
@endif
@endsection

@if ($session && $errors->any())
  @push('scripts')
  <script>
    // Hatalı gönderimde ilgili paneli girilen değerlerle tekrar aç
    document.addEventListener('DOMContentLoaded', () => {
      const panel = @json(old('closing_amount') !== null ? 'cashClosePanel' : 'cashMovementPanel');
      bootstrap.Offcanvas.getOrCreateInstance(document.getElementById(panel)).show();
    });
  </script>
  @endpush
@endif
