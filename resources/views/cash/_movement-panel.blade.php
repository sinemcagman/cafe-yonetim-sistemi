{{-- Ödeme dışı kasa girişi / çıkışı (yalnızca nakit) --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="cashMovementPanel" aria-labelledby="cashMovementPanelLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="cashMovementPanelLabel" class="offcanvas-title">Kasa Hareketi</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    <form method="POST" action="{{ route('cash.movement') }}">
      @csrf
      <x-form-errors />
      <label class="form-label d-block">Hareket türü</label>
      <div class="row g-3 mb-6">
        @foreach ([\App\Enums\CashMovementType::Giris->value => ['tabler-arrow-down-left', 'success', 'Kasaya para kondu'], \App\Enums\CashMovementType::Cikis->value => ['tabler-arrow-up-right', 'danger', 'Kasadan para alındı']] as $value => [$icon, $color, $hint])
          <div class="col-6">
            <input type="radio" class="btn-check" name="type" id="move-{{ $value }}" value="{{ $value }}" @checked(old('type', 'cikis') === $value)>
            <label class="btn btn-outline-{{ $color }} w-100 d-flex flex-column align-items-center py-3" for="move-{{ $value }}">
              <i class="icon-base ti {{ $icon }} icon-24px mb-1"></i>
              <span class="fw-medium">{{ \App\Enums\CashMovementType::from($value)->label() }}</span>
              <small class="opacity-75">{{ $hint }}</small>
            </label>
          </div>
        @endforeach
      </div>
      <div class="mb-6">
        <label class="form-label" for="move-amount">Tutar</label>
        <div class="input-group">
          <input type="text" inputmode="decimal" name="amount" id="move-amount" class="form-control" value="{{ old('amount') }}" placeholder="0,00" required>
          <span class="input-group-text">₺</span>
        </div>
        <div class="form-text">Kasada şu an olması gereken nakit: {{ money($summary['expected_cash']) }}</div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="move-description">Açıklama</label>
        <select name="description" id="move-description" class="select2 form-select" data-placeholder="Seçin ya da yazın" data-tags="true" required>
          <option value=""></option>
          @foreach (['Market alışverişi', 'Tedarikçi ödemesi', 'Bozuk para eklendi', 'Bankaya yatırıldı', 'Personel avansı'] as $reason)
            <option value="{{ $reason }}" @selected(old('description') === $reason)>{{ $reason }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary me-3">Kaydet</button>
      <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">Vazgeç</button>
    </form>
  </div>
</div>
