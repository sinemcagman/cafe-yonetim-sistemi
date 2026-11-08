{{-- Yeni sipariş paneli: tür seçimi (masa / paket / gel-al), masa seçimi ve not --}}
@php
  use App\Enums\OrderType;
  $selectedType = old('type', request('tur', OrderType::Masa->value));
  $typeOptions = [
    OrderType::Masa->value => ['icon' => 'tabler-armchair', 'hint' => 'Salondaki bir masa'],
    OrderType::Paket->value => ['icon' => 'tabler-package', 'hint' => 'Kurye / adrese'],
    OrderType::GelAl->value => ['icon' => 'tabler-walk', 'hint' => 'Müşteri gelip alır'],
  ];
@endphp

<div class="offcanvas offcanvas-end" tabindex="-1" id="openOrderPanel" aria-labelledby="openOrderPanelLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="openOrderPanelLabel" class="offcanvas-title">Yeni Sipariş</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    <form method="POST" action="{{ route('orders.store') }}" id="openOrderForm">
      @csrf
      <x-form-errors />

      <label class="form-label d-block">Sipariş türü</label>
      <div class="row g-3 mb-6">
        @foreach ($typeOptions as $value => $option)
          <div class="col-4">
            <input type="radio" class="btn-check" name="type" id="order-type-{{ $value }}" value="{{ $value }}" @checked($selectedType === $value)>
            <label class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center py-3 px-1" for="order-type-{{ $value }}">
              <i class="icon-base ti {{ $option['icon'] }} icon-26px mb-1"></i>
              <span class="fw-medium">{{ OrderType::from($value)->label() }}</span>
              <small class="opacity-75 lh-sm">{{ $option['hint'] }}</small>
            </label>
          </div>
        @endforeach
      </div>

      <div class="mb-6" data-table-field>
        <label class="form-label" for="order-table">Masa</label>
        <select name="dining_table_id" id="order-table" @class(['select2 form-select', 'is-invalid' => $errors->has('dining_table_id')])
          data-placeholder="Boş masa seçin">
          <option value=""></option>
          @foreach ($availableTables->groupBy(fn ($table) => $table->area ?: 'Diğer') as $area => $tables)
            <optgroup label="{{ $area }}">
              @foreach ($tables as $table)
                <option value="{{ $table->id }}" @selected(old('dining_table_id') == $table->id)>{{ $table->label() }} · {{ $table->capacity }} kişilik</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
        @if ($availableTables->isEmpty())
          <div class="form-text text-warning">Şu anda boş masa yok.</div>
        @else
          <div class="form-text">Yalnızca boş ve aktif masalar listelenir.</div>
        @endif
        @error('dining_table_id')
          <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-6">
        <label class="form-label" for="order-open-note">Sipariş notu <span class="text-body-secondary">(isteğe bağlı)</span></label>
        <textarea name="note" id="order-open-note" rows="3" maxlength="500" class="form-control"
          placeholder="Örn. Tost soğansız olsun">{{ old('note') }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary me-3">
        <i class="icon-base ti tabler-check icon-16px me-1"></i> Siparişi Aç
      </button>
      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const panelEl = document.getElementById('openOrderPanel');
    const form = document.getElementById('openOrderForm');
    const tableField = form.querySelector('[data-table-field]');
    const panel = new bootstrap.Offcanvas(panelEl);

    // Masa alanı yalnızca "Masa" türünde görünür
    const sync = () => {
      const isTable = form.querySelector('[name=type]:checked')?.value === @json(\App\Enums\OrderType::Masa->value);
      tableField.classList.toggle('d-none', !isTable);
    };
    form.querySelectorAll('[name=type]').forEach(radio => radio.addEventListener('change', sync));
    sync();

    document.querySelectorAll('[data-open-order]').forEach(button => button.addEventListener('click', () => {
      const type = button.dataset.openOrder;
      if (type) {
        const radio = form.querySelector(`[name=type][value="${type}"]`);
        if (radio) radio.checked = true;
        sync();
      }
      panel.show();
    }));

    @if ($errors->any() || request()->boolean('yeni'))
      panel.show();
    @endif
  });
</script>
@endpush
