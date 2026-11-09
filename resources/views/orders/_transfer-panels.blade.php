{{-- Masa taşıma ve hesap birleştirme panelleri (sipariş ekranı) --}}
@php
  $activeCount = $order->items->where('status', \App\Enums\OrderItemStatus::Aktif)->sum('quantity');
@endphp

@if ($order->dining_table_id)
  <!-- Masayı değiştir -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="moveTablePanel" aria-labelledby="moveTablePanelLabel">
    <div class="offcanvas-header border-bottom">
      <div>
        <h5 id="moveTablePanelLabel" class="offcanvas-title mb-0">Masayı Değiştir</h5>
        <small class="text-body-secondary">{{ $order->label() }}</small>
      </div>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body p-6">
      <p class="mb-6">Müşteriler başka bir masaya geçtiyse siparişi olduğu gibi taşıyın. Kalemler, ödemeler ve hazırlık durumu değişmez; <strong>{{ $order->diningTable->label() }}</strong> boşalır.</p>
      @if ($emptyTables->isEmpty())
        <div class="alert alert-warning mb-0">Şu anda boş masa yok.</div>
      @else
        <form method="POST" action="{{ route('orders.move', $order) }}">
          @csrf
          <div class="mb-6">
            <label class="form-label" for="move-table">Yeni masa</label>
            <select name="dining_table_id" id="move-table" class="select2 form-select" data-placeholder="Boş masa seçin" required>
              <option value=""></option>
              @foreach ($emptyTables->groupBy(fn ($table) => $table->area ?: 'Diğer') as $area => $tables)
                <optgroup label="{{ $area }}">
                  @foreach ($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->label() }} · {{ $table->capacity }} kişilik</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>
          <button type="submit" class="btn btn-primary me-3">
            <i class="icon-base ti tabler-arrows-right-left icon-16px me-1"></i> Taşı
          </button>
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
        </form>
      @endif
    </div>
  </div>
@endif

<!-- Hesap birleştir -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mergePanel" aria-labelledby="mergePanelLabel">
  <div class="offcanvas-header border-bottom">
    <div>
      <h5 id="mergePanelLabel" class="offcanvas-title mb-0">Hesap Birleştir</h5>
      <small class="text-body-secondary">{{ $order->label() }}</small>
    </div>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    <p class="mb-6">Bu siparişin kalemleri seçeceğiniz hesaba eklenir ve hesap tek adisyonda ödenir. Bu sipariş "Birleştirildi" olarak kapanır{{ $order->diningTable ? ', '.$order->diningTable->label().' boşalır' : '' }}.</p>

    @if ($mergeTargets->isEmpty())
      <div class="alert alert-warning mb-0">Birleştirilebilecek başka açık hesap yok.</div>
    @else
      <form method="POST" action="{{ route('orders.merge', $order) }}" id="mergeForm"
        data-confirm="Hesaplar birleştirilsin mi?" data-confirm-text="Birleştirme geri alınamaz; kalemler seçilen hesaba taşınır." data-confirm-button="Evet, birleştir">
        @csrf
        <div class="mb-6">
          <label class="form-label" for="merge-target">Eklenecek hesap</label>
          <select name="target_order_id" id="merge-target" class="select2 form-select" data-placeholder="Açık hesap seçin" required>
            <option value=""></option>
            @foreach ($mergeTargets as $target)
              <option value="{{ $target->id }}" data-label="{{ $target->label() }}" data-total="{{ $target->total }}">
                {{ $target->label() }} · {{ money($target->total) }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="bg-lighter rounded p-4 mb-6 d-none" data-merge-preview aria-live="polite">
          <div class="d-flex justify-content-between mb-1"><span>Bu siparişten</span><span>{{ $activeCount }} ürün · {{ money($order->total) }}</span></div>
          <div class="d-flex justify-content-between mb-2"><span data-merge-target-label>Hedef hesap</span><span data-merge-target-total></span></div>
          <div class="d-flex justify-content-between border-top pt-2">
            <span class="fw-medium">Birleşik toplam</span>
            <span class="fw-medium text-heading" data-merge-sum></span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary me-3">
          <i class="icon-base ti tabler-git-merge icon-16px me-1"></i> Birleştir
        </button>
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
      </form>
    @endif
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('merge-target');
    if (!select) return;

    const preview = document.querySelector('[data-merge-preview]');
    const own = {{ (float) $order->total }};
    const format = v => v.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';

    // Select2 seçimleri jQuery "change" olayıyla bildirir
    $(select).on('change', () => {
      const option = select.selectedOptions[0];
      if (!option || !option.value) {
        preview.classList.add('d-none');
        return;
      }
      const total = parseFloat(option.dataset.total);
      preview.querySelector('[data-merge-target-label]').textContent = option.dataset.label;
      preview.querySelector('[data-merge-target-total]').textContent = format(total);
      preview.querySelector('[data-merge-sum]').textContent = format(total + own);
      preview.classList.remove('d-none');
    });
  });
</script>
@endpush
