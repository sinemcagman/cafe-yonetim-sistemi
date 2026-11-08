{{-- Adisyon: sipariş kalemleri, not ve toplamlar. Ürün eklenince OrderItemController bu parçayı yeniden çizer. --}}
@php
  $editable = $order->isOpen() && auth()->user()->canTakeOrders();
  $activeItems = $order->items->where('status', \App\Enums\OrderItemStatus::Aktif);
  $cancelledItems = $order->items->where('status', \App\Enums\OrderItemStatus::Iptal);
@endphp

<div class="card-header d-flex justify-content-between align-items-start border-bottom">
  <div>
    <h5 class="card-title mb-1">Adisyon</h5>
    <small class="text-body-secondary">{{ $activeItems->sum('quantity') }} ürün · {{ $order->code() }}</small>
  </div>
  <x-status-badge :status="$order->status" />
</div>

@if ($order->isOpen())
  {{-- Hazırlık adımları: yeni → hazırlanıyor → hazır → teslim edildi --}}
  @php
    $steps = [\App\Enums\OrderStatus::Yeni, \App\Enums\OrderStatus::Hazirlaniyor, \App\Enums\OrderStatus::Hazir, \App\Enums\OrderStatus::TeslimEdildi];
    $currentIndex = array_search($order->status, $steps, true);
    $next = $order->status->next();
  @endphp
  <div class="card-body border-bottom py-4">
    <ol class="d-flex gap-1 list-unstyled mb-0" aria-label="Hazırlık durumu">
      @foreach ($steps as $index => $step)
        <li class="flex-fill text-center" @if ($index === $currentIndex) aria-current="step" @endif>
          <span @class([
              'd-block rounded-pill mb-2',
              "bg-{$order->status->color()}" => $index <= $currentIndex,
              'bg-label-secondary' => $index > $currentIndex,
          ]) style="height: 6px"></span>
          <small @class(['d-block lh-sm', 'fw-medium text-heading' => $index === $currentIndex, 'text-body-secondary' => $index !== $currentIndex])>{{ $step->label() }}</small>
        </li>
      @endforeach
    </ol>
    @if ($editable && $next)
      <form method="POST" action="{{ route('orders.advance', $order) }}" class="mt-3">
        @csrf
        <input type="hidden" name="from" value="{{ $order->status->value }}">
        <button type="submit" class="btn btn-sm btn-{{ $next->color() }} w-100" @disabled($activeItems->isEmpty())>
          <i class="icon-base ti {{ $next->icon() }} icon-16px me-1"></i> {{ $next->actionLabel() }}
        </button>
      </form>
    @endif
  </div>
@endif

<div class="card-body pt-4">
  @if ($activeItems->isEmpty())
    <div class="text-center py-6">
      <div class="avatar avatar-lg mx-auto mb-3">
        <span class="avatar-initial rounded-circle bg-label-secondary"><i class="icon-base ti tabler-receipt icon-26px"></i></span>
      </div>
      <h6 class="mb-1">Adisyon boş</h6>
      <p class="small text-body-secondary mb-0">
        {{ $editable ? 'Soldaki menüden ürüne dokunarak ekleyin.' : 'Bu siparişte ürün yok.' }}
      </p>
    </div>
  @else
    <ul class="list-unstyled mb-0" data-ticket-items>
      @foreach ($activeItems as $item)
        <li class="d-flex align-items-start gap-3 {{ $loop->last ? '' : 'mb-4' }}">
          <span class="badge bg-label-primary fs-6 px-2 flex-shrink-0" style="min-width: 2.25rem">{{ $item->quantity }}×</span>
          <div class="flex-grow-1" style="min-width: 0">
            <div class="d-flex justify-content-between gap-2">
              <h6 class="mb-0 text-truncate">{{ $item->product->name }}</h6>
              <span class="fw-medium text-heading text-nowrap">{{ money($item->line_total) }}</span>
            </div>
            <small class="text-body-secondary">
              {{ money($item->unit_price) }} · {{ $item->user->name }} · {{ $item->created_at->format('H:i') }}
            </small>
          </div>
          @if ($editable)
            <form method="POST" action="{{ route('orders.items.cancel', [$order, $item]) }}" class="flex-shrink-0"
              data-prompt-reason="{{ $item->quantity }} × {{ $item->product->name }} iptal edilsin mi?"
              data-prompt-text="Adet stoğa geri eklenir, iptal işlem geçmişine kaydedilir."
              data-prompt-placeholder="Örn. Yanlış girildi, müşteri vazgeçti">
              @csrf
              <input type="hidden" name="reason">
              <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded-pill" data-bs-toggle="tooltip"
                title="Kalemi iptal et" aria-label="{{ $item->product->name }} kalemini iptal et">
                <i class="icon-base ti tabler-x icon-18px"></i>
              </button>
            </form>
          @endif
        </li>
      @endforeach
    </ul>
  @endif

  @if ($cancelledItems->isNotEmpty())
    <details class="mt-4">
      <summary class="small text-body-secondary">İptal edilenler ({{ $cancelledItems->count() }})</summary>
      <ul class="list-unstyled small mt-2 mb-0">
        @foreach ($cancelledItems as $item)
          <li class="d-flex justify-content-between gap-2 mb-1 text-body-secondary">
            <span><s>{{ $item->quantity }} × {{ $item->product->name }}</s> — {{ $item->cancel_reason }}</span>
            <span class="text-nowrap">{{ $item->canceller?->name }} · {{ $item->cancelled_at?->format('H:i') }}</span>
          </li>
        @endforeach
      </ul>
    </details>
  @endif
</div>

<div class="card-body border-top">
  @if ($editable)
    <form method="POST" action="{{ route('orders.note', $order) }}">
      @csrf
      @method('PATCH')
      <label class="form-label" for="order-note">Sipariş notu</label>
      <textarea name="note" id="order-note" rows="2" maxlength="500" class="form-control mb-2"
        placeholder="Örn. Tost soğansız olsun">{{ $order->note }}</textarea>
      <button type="submit" class="btn btn-sm btn-label-secondary">Notu kaydet</button>
    </form>
  @elseif ($order->note)
    <small class="text-body-secondary d-block mb-1">Sipariş notu</small>
    <p class="mb-0">{{ $order->note }}</p>
  @endif
</div>

<div class="card-footer border-top bg-lighter">
  <div class="d-flex justify-content-between mb-1">
    <span>Ara toplam</span>
    <span>{{ money($order->total) }}</span>
  </div>
  @if ((float) $order->paid_total > 0)
    <div class="d-flex justify-content-between mb-1 text-success">
      <span>Ödenen</span>
      <span>− {{ money($order->paid_total) }}</span>
    </div>
  @endif
  <div class="d-flex justify-content-between align-items-center mt-2">
    <h6 class="mb-0">{{ $order->isOpen() ? 'Ödenecek' : 'Kalan' }}</h6>
    <h4 class="mb-0 text-primary" data-ticket-total>{{ money($order->remainingAmount()) }}</h4>
  </div>

  @php $payments = $order->payments()->with('user')->oldest('paid_at')->get(); @endphp
  @if ($payments->isNotEmpty())
    <ul class="list-unstyled small mt-3 mb-0 border-top pt-3">
      @foreach ($payments as $payment)
        <li class="d-flex justify-content-between gap-2 mb-1">
          <span>
            <i class="icon-base ti {{ $payment->method === \App\Enums\PaymentMethod::Nakit ? 'tabler-cash' : 'tabler-credit-card' }} icon-14px me-1"></i>
            {{ $payment->method->label() }} · {{ $payment->user->name }} · {{ $payment->paid_at->format('H:i') }}
          </span>
          <span class="fw-medium text-success">{{ money($payment->amount) }}</span>
        </li>
      @endforeach
    </ul>
  @endif

  @if ($order->isOpen() && auth()->user()->canHandlePayments())
    <button type="button" class="btn btn-primary w-100 mt-4" data-bs-toggle="offcanvas" data-bs-target="#paymentPanel"
      @disabled($order->remainingAmount() <= 0)>
      <i class="icon-base ti tabler-cash-register icon-18px me-1"></i> Ödeme Al
    </button>
  @elseif (! $order->isOpen())
    <div class="alert alert-{{ $order->status->color() }} d-flex align-items-center gap-2 mt-4 mb-0 py-2">
      <i class="icon-base ti {{ $order->status->icon() }} icon-18px"></i>
      <span>{{ $order->status->label() }} · {{ $order->closed_at?->format('d.m.Y H:i') }}</span>
    </div>
  @endif
</div>
