{{-- Stok güncelleme paneli: ürün listesindeki "Stok" butonlarıyla açılır --}}
@php
  use App\Enums\StockMovementType;
@endphp

<div class="offcanvas offcanvas-end" tabindex="-1" id="stockPanel" aria-labelledby="stockPanelLabel">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 id="stockPanelLabel" class="offcanvas-title mb-0">Stok Güncelle</h5>
      <small class="text-body-secondary" data-stock-name></small>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    <form method="POST" action="" id="stockForm">
      @csrf

      <!-- Mevcut → yeni adet önizlemesi -->
      <div class="d-flex align-items-center justify-content-around bg-lighter rounded p-4 mb-6 text-center">
        <div>
          <small class="d-block text-body-secondary">Mevcut</small>
          <h4 class="mb-0" data-stock-current>0</h4>
        </div>
        <i class="icon-base ti tabler-arrow-right icon-24px text-body-secondary"></i>
        <div>
          <small class="d-block text-body-secondary">Yeni adet</small>
          <h4 class="mb-0 text-primary" data-stock-result>0</h4>
        </div>
      </div>

      <div class="mb-6">
        <label class="form-label d-block">Ne yapmak istiyorsunuz?</label>
        <div class="row g-3">
          <div class="col-6">
            <input type="radio" class="btn-check" name="type" id="stock-type-in" value="{{ StockMovementType::Giris->value }}" checked>
            <label class="btn btn-outline-success w-100 d-flex flex-column py-3" for="stock-type-in">
              <i class="icon-base ti tabler-truck-delivery icon-24px mb-1"></i>
              <span class="fw-medium">Ürün geldi</span>
              <small class="opacity-75">Stok girişi</small>
            </label>
          </div>
          <div class="col-6">
            <input type="radio" class="btn-check" name="type" id="stock-type-fix" value="{{ StockMovementType::Duzeltme->value }}">
            <label class="btn btn-outline-warning w-100 d-flex flex-column py-3" for="stock-type-fix">
              <i class="icon-base ti tabler-adjustments icon-24px mb-1"></i>
              <span class="fw-medium">Düzeltme</span>
              <small class="opacity-75">Fire, sayım farkı</small>
            </label>
          </div>
        </div>
      </div>

      <div class="mb-6">
        <label class="form-label" for="stock-quantity">Adet</label>
        <div class="input-group">
          <button type="button" class="btn btn-outline-secondary" data-stock-step="-1" aria-label="Azalt"><i class="icon-base ti tabler-minus"></i></button>
          <input type="number" name="quantity" id="stock-quantity" class="form-control text-center fw-medium" value="1" required>
          <button type="button" class="btn btn-outline-secondary" data-stock-step="1" aria-label="Artır"><i class="icon-base ti tabler-plus"></i></button>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-2" data-stock-chips>
          @foreach ([6, 12, 24, 50] as $chip)
            <button type="button" class="btn btn-xs btn-label-secondary" data-stock-set="{{ $chip }}">+{{ $chip }}</button>
          @endforeach
        </div>
        <div class="form-text" data-stock-hint>Gelen ürün adedini girin.</div>
      </div>

      <div class="mb-6">
        <label class="form-label" for="stock-reason">Neden</label>
        <select name="reason" id="stock-reason" class="select2 form-select" data-placeholder="Neden seçin ya da yazın"
          data-tags="true" required>
          <option value=""></option>
          @foreach (['Tedarikçiden teslimat', 'Market alışverişi', 'Gün sonu sayım farkı', 'Fire / bozulma', 'Personel tüketimi'] as $reason)
            <option value="{{ $reason }}">{{ $reason }}</option>
          @endforeach
        </select>
        <div class="form-text">Listede yoksa yazıp Enter'a basın. Neden, stok hareketlerinde saklanır.</div>
      </div>

      <button type="submit" class="btn btn-primary me-3">
        <i class="icon-base ti tabler-device-floppy icon-16px me-1"></i> Kaydet
      </button>
      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const panelEl = document.getElementById('stockPanel');
    if (!panelEl) return;

    const panel = new bootstrap.Offcanvas(panelEl);
    const form = document.getElementById('stockForm');
    const quantity = document.getElementById('stock-quantity');
    const currentEl = panelEl.querySelector('[data-stock-current]');
    const resultEl = panelEl.querySelector('[data-stock-result]');
    const hint = panelEl.querySelector('[data-stock-hint]');
    const chips = panelEl.querySelector('[data-stock-chips]');
    let current = 0;

    const isEntry = () => form.querySelector('[name=type]:checked').value === @json(StockMovementType::Giris->value);

    const refresh = () => {
      const qty = parseInt(quantity.value, 10) || 0;
      const result = current + qty;
      resultEl.textContent = result;
      resultEl.classList.toggle('text-danger', result < 0);
      resultEl.classList.toggle('text-primary', result >= 0);
      quantity.min = isEntry() ? 1 : -current;
      chips.classList.toggle('d-none', !isEntry());
      hint.textContent = isEntry()
        ? 'Gelen ürün adedini girin.'
        : 'Stoğu azaltmak için eksi değer girin (örn. -2). Stok sıfırın altına inemez.';
    };

    document.querySelectorAll('[data-stock-open]').forEach(button => {
      button.addEventListener('click', () => {
        current = parseInt(button.dataset.stock, 10) || 0;
        form.action = button.dataset.action;
        panelEl.querySelector('[data-stock-name]').textContent = button.dataset.name;
        currentEl.textContent = current;
        form.querySelector('#stock-type-in').checked = true;
        quantity.value = 1;
        $(form.querySelector('[name=reason]')).val(null).trigger('change');
        refresh();
        panel.show();
        setTimeout(() => quantity.select(), 300);
      });
    });

    panelEl.querySelectorAll('[data-stock-step]').forEach(button => {
      button.addEventListener('click', () => {
        quantity.value = (parseInt(quantity.value, 10) || 0) + parseInt(button.dataset.stockStep, 10);
        refresh();
      });
    });

    panelEl.querySelectorAll('[data-stock-set]').forEach(button => {
      button.addEventListener('click', () => {
        quantity.value = button.dataset.stockSet;
        refresh();
      });
    });

    form.querySelectorAll('[name=type]').forEach(radio => radio.addEventListener('change', () => {
      if (isEntry() && (parseInt(quantity.value, 10) || 0) < 1) quantity.value = 1;
      if (!isEntry() && (parseInt(quantity.value, 10) || 0) > 0) quantity.value = -1;
      refresh();
    }));

    quantity.addEventListener('input', refresh);
  });
</script>
@endpush
