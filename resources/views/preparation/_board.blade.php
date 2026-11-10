{{-- Hazırlık panosu sütunları. PreparationController her yenilemede bu parçayı yeniden çizer. --}}
@php
  use App\Enums\OrderStatus;
  use App\Enums\OrderType;
  $canAdvance = auth()->user()->canTakeOrders();
  $typeIcons = [OrderType::Masa->value => 'tabler-armchair', OrderType::Paket->value => 'tabler-package', OrderType::GelAl->value => 'tabler-walk'];
@endphp

<div class="row g-6">
  @foreach ($columns as $statusValue => $orders)
    @php $status = OrderStatus::from($statusValue); @endphp
    <div class="col-lg-4">
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-2">
          <span class="avatar avatar-sm">
            <span class="avatar-initial rounded bg-label-{{ $status->color() }}"><i class="icon-base ti {{ $status->icon() }} icon-18px"></i></span>
          </span>
          <h5 class="mb-0">{{ $status->label() }}</h5>
        </div>
        <span class="badge rounded-pill bg-label-{{ $status->color() }}" data-column-count="{{ $statusValue }}">{{ $orders->count() }}</span>
      </div>

      @forelse ($orders as $order)
        @php
          $minutes = $order->minutesInStatus();
          $waitColor = $minutes <= 10 ? 'success' : ($minutes <= 20 ? 'warning' : 'danger');
          $lateIds = $order->lateItemIds();
          $activeItems = $order->items->where('status', \App\Enums\OrderItemStatus::Aktif);
          $next = $status->next();
        @endphp
        <div class="card mb-4 border-0 border-start border-4 border-{{ $waitColor }}" data-order-card="{{ $order->id }}">
          <div class="card-header d-flex justify-content-between align-items-start pb-2">
            <div>
              <h5 class="mb-1 d-flex align-items-center gap-2">
                <i class="icon-base ti {{ $typeIcons[$order->type->value] }} icon-20px text-body-secondary"></i>
                {{ $order->label() }}
              </h5>
              <small class="text-body-secondary">{{ $order->user->name }} · açılış {{ $order->opened_at->format('H:i') }}</small>
            </div>
            <span class="badge bg-label-{{ $waitColor }} d-flex align-items-center gap-1" title="Bu durumda geçen süre">
              <i class="icon-base ti tabler-clock icon-14px"></i> {{ $minutes }} dk
            </span>
          </div>
          <div class="card-body pt-2 pb-3">
            @if ($lateIds->isNotEmpty())
              <div class="small text-warning fw-medium mb-2">
                <i class="icon-base ti tabler-alert-circle icon-14px me-1"></i>Sonradan eklenen ürün var
              </div>
            @endif
            <ul class="list-unstyled mb-0">
              @foreach ($activeItems as $item)
                @php $late = $lateIds->contains($item->id); @endphp
                <li @class(['d-flex align-items-center gap-2 py-1 px-2 rounded', 'bg-label-warning' => $late])>
                  <span class="fw-bold text-heading" style="min-width: 2rem">{{ $item->quantity }}×</span>
                  <span class="flex-grow-1 text-heading">{{ $item->product->name }}</span>
                  @if ($late)
                    <span class="badge bg-warning">Yeni</span>
                  @endif
                </li>
              @endforeach
            </ul>
            @if ($order->note)
              <div class="alert alert-warning d-flex gap-2 py-2 px-3 mt-3 mb-0 small" role="note">
                <i class="icon-base ti tabler-note icon-16px flex-shrink-0 mt-1"></i>
                <span>{{ $order->note }}</span>
              </div>
            @endif
          </div>
          <div class="card-footer d-flex gap-2 pt-0">
            @if ($canAdvance && $next)
              <form method="POST" action="{{ route('orders.advance', $order) }}" class="flex-grow-1" data-advance>
                @csrf
                <input type="hidden" name="from" value="{{ $status->value }}">
                <button type="submit" class="btn btn-{{ $next->color() }} w-100">
                  <i class="icon-base ti {{ $next->icon() }} icon-18px me-1"></i> {{ $next->actionLabel() }}
                </button>
              </form>
            @endif
            <a href="{{ route('orders.print.kitchen', [$order, 'yazdir' => 1, 'kaynak' => 'hazirlik']) }}" target="_blank" rel="noopener"
              class="btn btn-icon btn-label-secondary" data-bs-toggle="tooltip"
              title="Mutfak fişi yazdır" aria-label="{{ $order->label() }} mutfak fişini yazdır">
              <i class="icon-base ti tabler-printer icon-18px"></i>
            </a>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-icon btn-label-secondary" data-bs-toggle="tooltip"
              title="Siparişi aç" aria-label="{{ $order->label() }} siparişini aç">
              <i class="icon-base ti tabler-external-link icon-18px"></i>
            </a>
          </div>
        </div>
      @empty
        <div class="card border border-dashed shadow-none">
          <div class="card-body text-center text-body-secondary py-8">
            <i class="icon-base ti {{ $status->icon() }} icon-28px mb-2 d-block mx-auto opacity-50"></i>
            <small>{{ $status->label() }} sipariş yok</small>
          </div>
        </div>
      @endforelse
    </div>
  @endforeach
</div>
