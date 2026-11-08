{{-- Ödeme paneli: sipariş ekranındaki "Ödeme Al" butonuyla açılır --}}
@php
  use App\Enums\OrderStatus;
  use App\Enums\PaymentMethod;
  $remaining = $order->remainingAmount();
  $inPreparation = in_array($order->status, [OrderStatus::Yeni, OrderStatus::Hazirlaniyor], true);
@endphp

<div class="offcanvas offcanvas-end" tabindex="-1" id="paymentPanel" aria-labelledby="paymentPanelLabel">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 id="paymentPanelLabel" class="offcanvas-title mb-0">Ödeme Al</h5>
      <small class="text-body-secondary">{{ $order->label() }}</small>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    @if (! $cashSession)
      <div class="alert alert-warning d-flex gap-2" role="alert">
        <i class="icon-base ti tabler-cash-register icon-20px flex-shrink-0"></i>
        <div>
          Ödeme almak için önce kasanın açılması gerekiyor.
          <a href="{{ route('cash.index') }}" class="alert-link d-block mt-1">Kasaya git</a>
        </div>
      </div>
    @else
      <div class="bg-lighter rounded p-4 mb-6 text-center">
        <small class="text-body-secondary d-block">Ödenecek tutar</small>
        <h3 class="mb-0 text-primary">{{ money($remaining) }}</h3>
        @if ((float) $order->paid_total > 0)
          <small class="text-body-secondary">Toplam {{ money($order->total) }} · ödenen {{ money($order->paid_total) }}</small>
        @endif
      </div>

      @if ($inPreparation)
        <div class="alert alert-info d-flex gap-2 small" role="note">
          <i class="icon-base ti tabler-info-circle icon-18px flex-shrink-0"></i>
          <span>Sipariş henüz {{ mb_strtolower($order->status->label()) }}. Tutarın tamamı ödenirse sipariş kapanır ve hazırlık ekranından düşer.</span>
        </div>
      @endif

      <form method="POST" action="{{ route('orders.payments.store', $order) }}" id="paymentForm" novalidate>
        @csrf
        <x-form-errors />

        <label class="form-label d-block">Ödeme yöntemi</label>
        <div class="row g-3 mb-6">
          @foreach ([PaymentMethod::Nakit->value => 'tabler-cash', PaymentMethod::Kart->value => 'tabler-credit-card'] as $value => $icon)
            <div class="col-6">
              <input type="radio" class="btn-check" name="method" id="pay-{{ $value }}" value="{{ $value }}"
                @checked(old('method', PaymentMethod::Nakit->value) === $value)>
              <label class="btn btn-outline-primary w-100 d-flex flex-column align-items-center py-3" for="pay-{{ $value }}">
                <i class="icon-base ti {{ $icon }} icon-26px mb-1"></i>
                <span class="fw-medium">{{ PaymentMethod::from($value)->label() }}</span>
              </label>
            </div>
          @endforeach
        </div>

        <div class="mb-6">
          <label class="form-label" for="pay-amount">Tahsil edilecek tutar</label>
          <div class="input-group">
            <input type="text" inputmode="decimal" name="amount" id="pay-amount" class="form-control form-control-lg fw-medium"
              value="{{ old('amount', number_format($remaining, 2, ',', '')) }}" required data-remaining="{{ $remaining }}">
            <span class="input-group-text">₺</span>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-2">
            <button type="button" class="btn btn-xs btn-label-secondary" data-pay-fraction="1">Tamamı</button>
            <button type="button" class="btn btn-xs btn-label-secondary" data-pay-fraction="0.5">Yarısı</button>
            <button type="button" class="btn btn-xs btn-label-secondary" data-pay-fraction="0.3333">⅓</button>
          </div>
          <div class="form-text">Hesap bölünecekse tutarı küçültün; kalan tutar siparişte açık kalır.</div>
        </div>

        <!-- Nakitte para üstü hesabı (yalnızca ekranda; kaydedilen tutar yukarıdakidir) -->
        <div class="mb-6 border rounded p-4" data-cash-only>
          <label class="form-label" for="pay-received">Müşteriden alınan nakit</label>
          <div class="input-group mb-2">
            <input type="text" inputmode="decimal" id="pay-received" class="form-control" placeholder="0,00">
            <span class="input-group-text">₺</span>
          </div>
          <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ([50, 100, 200, 500, 1000] as $bill)
              <button type="button" class="btn btn-xs btn-label-success" data-pay-bill="{{ $bill }}">{{ $bill }} ₺</button>
            @endforeach
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-body-secondary">Para üstü</span>
            <h4 class="mb-0" data-pay-change>—</h4>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
          <i class="icon-base ti tabler-check icon-18px me-1"></i> <span data-pay-submit-label>Ödemeyi Kaydet</span>
        </button>
        <button type="button" class="btn btn-label-secondary w-100" data-bs-dismiss="offcanvas">Vazgeç</button>
      </form>
    @endif
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('paymentForm');
    if (!form) return;

    const amount = document.getElementById('pay-amount');
    const received = document.getElementById('pay-received');
    const change = form.querySelector('[data-pay-change]');
    const cashBox = form.querySelector('[data-cash-only]');
    const submitLabel = form.querySelector('[data-pay-submit-label]');
    const remaining = parseFloat(amount.dataset.remaining);

    const parse = value => {
      value = String(value || '').trim();
      if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
      const number = parseFloat(value);
      return Number.isFinite(number) ? number : 0;
    };
    const format = number => number.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const refresh = () => {
      const isCash = form.querySelector('[name=method]:checked').value === 'nakit';
      cashBox.classList.toggle('d-none', !isCash);

      const pay = parse(amount.value);
      const got = parse(received.value);
      if (!isCash || !got) {
        change.textContent = '—';
        change.className = 'mb-0';
      } else {
        const diff = got - pay;
        change.textContent = (diff < 0 ? 'Eksik ' : '') + format(Math.abs(diff)) + ' ₺';
        change.className = 'mb-0 ' + (diff < 0 ? 'text-danger' : 'text-success');
      }

      submitLabel.textContent = pay >= remaining - 0.004 ? 'Ödemeyi Kaydet ve Hesabı Kapat' : 'Kısmi Ödemeyi Kaydet';
    };

    form.querySelectorAll('[data-pay-fraction]').forEach(button => button.addEventListener('click', () => {
      const value = Math.round(remaining * parseFloat(button.dataset.payFraction) * 100) / 100;
      amount.value = format(value).replace(/\./g, '');
      refresh();
    }));
    form.querySelectorAll('[data-pay-bill]').forEach(button => button.addEventListener('click', () => {
      received.value = format(parse(received.value) + parseFloat(button.dataset.payBill)).replace(/\./g, '');
      refresh();
    }));
    [amount, received].forEach(input => input.addEventListener('input', refresh));
    form.querySelectorAll('[name=method]').forEach(radio => radio.addEventListener('change', refresh));

    document.getElementById('paymentPanel').addEventListener('shown.bs.offcanvas', () => amount.select());
    refresh();
  });
</script>
@endpush
