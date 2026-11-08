{{-- Kasa kapama: sayılan nakit girilir, beklenen tutarla fark anında gösterilir --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="cashClosePanel" aria-labelledby="cashClosePanelLabel">
  <div class="offcanvas-header border-bottom">
    <h5 id="cashClosePanelLabel" class="offcanvas-title">Kasayı Kapat</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
  </div>
  <div class="offcanvas-body p-6">
    @if ($awaitingPayment->isNotEmpty())
      <div class="alert alert-warning d-flex gap-2 small" role="alert">
        <i class="icon-base ti tabler-alert-triangle icon-18px flex-shrink-0"></i>
        <span>{{ $awaitingPayment->count() }} siparişin ödemesi alınmadı ({{ money($awaitingPayment->sum(fn ($o) => $o->remainingAmount())) }}). Kasayı yine de kapatabilirsiniz; bu hesaplar sonraki kasada alınır.</span>
      </div>
    @endif

    <div class="bg-lighter rounded p-4 mb-6">
      <div class="d-flex justify-content-between mb-1"><span>Açılış nakdi</span><span>{{ money($summary['opening']) }}</span></div>
      <div class="d-flex justify-content-between mb-1"><span>Nakit tahsilat</span><span class="text-success">+{{ money($summary['cash_sales']) }}</span></div>
      <div class="d-flex justify-content-between mb-1"><span>Elle giriş</span><span class="text-success">+{{ money($summary['manual_in']) }}</span></div>
      <div class="d-flex justify-content-between mb-2"><span>Elle çıkış</span><span class="text-danger">−{{ money($summary['manual_out']) }}</span></div>
      <div class="d-flex justify-content-between border-top pt-2">
        <span class="fw-medium">Kasada olması gereken</span>
        <span class="fw-medium text-heading" data-expected="{{ $summary['expected_cash'] }}">{{ money($summary['expected_cash']) }}</span>
      </div>
    </div>

    <form method="POST" action="{{ route('cash.close') }}" id="cashCloseForm"
      data-confirm="Kasa kapatılsın mı?" data-confirm-text="Kapatılan kasaya yeni ödeme alınamaz." data-confirm-button="Evet, kapat">
      @csrf
      <x-form-errors />
      <div class="mb-4">
        <label class="form-label" for="closing-amount">Sayılan nakit</label>
        <div class="input-group">
          <input type="text" inputmode="decimal" name="closing_amount" id="closing-amount" class="form-control form-control-lg" placeholder="0,00" required>
          <span class="input-group-text">₺</span>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center rounded p-3 mb-6 bg-label-secondary" data-close-diff-box>
        <span>Fark</span>
        <h5 class="mb-0" data-close-diff>—</h5>
      </div>
      <div class="mb-6">
        <label class="form-label" for="closing-note">Not <span class="text-body-secondary">(isteğe bağlı)</span></label>
        <textarea name="note" id="closing-note" rows="2" maxlength="500" class="form-control" placeholder="Örn. Fark bozuk para eksikliğinden"></textarea>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        <i class="icon-base ti tabler-lock icon-18px me-1"></i> Kasayı Kapat
      </button>
    </form>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('closing-amount');
    const box = document.querySelector('[data-close-diff-box]');
    const output = document.querySelector('[data-close-diff]');
    const expected = parseFloat(document.querySelector('[data-expected]').dataset.expected);

    input.addEventListener('input', () => {
      let value = input.value.trim();
      if (value.includes(',')) value = value.replace(/\./g, '').replace(',', '.');
      const counted = parseFloat(value);
      if (!Number.isFinite(counted)) {
        output.textContent = '—';
        box.className = 'd-flex justify-content-between align-items-center rounded p-3 mb-6 bg-label-secondary';
        return;
      }
      const diff = Math.round((counted - expected) * 100) / 100;
      const text = Math.abs(diff).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) + ' ₺';
      output.textContent = diff === 0 ? 'Tutuyor' : (diff > 0 ? '+' : '−') + text + (diff > 0 ? ' fazla' : ' eksik');
      box.className = 'd-flex justify-content-between align-items-center rounded p-3 mb-6 ' + (diff === 0 ? 'bg-label-success' : 'bg-label-danger');
    });
    document.getElementById('cashClosePanel').addEventListener('shown.bs.offcanvas', () => input.focus());
  });
</script>
@endpush
