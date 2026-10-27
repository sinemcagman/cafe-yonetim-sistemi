@php
  $color = ! $table->is_active ? 'secondary' : ($table->isAvailable() ? 'success' : 'danger');
  $filterKey = ! $table->is_active ? 'pasif' : ($table->isAvailable() ? 'bos' : 'dolu');
@endphp

<div class="col-6 col-md-4 col-xl-3 col-xxl-2" data-table-card="{{ $filterKey }}" id="masa-{{ $table->number }}">
  <div @class(['card h-100 border-0 border-top border-3', "border-{$color}", 'opacity-75' => ! $table->is_active])>
    <div class="card-body d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="avatar avatar-md">
          <span class="avatar-initial rounded bg-label-{{ $color }} fs-5 fw-bold">{{ $table->number }}</span>
        </div>
        @if (auth()->user()->isManager())
          <div class="dropdown">
            <button type="button" class="btn btn-icon btn-text-secondary rounded-pill btn-sm dropdown-toggle hide-arrow p-0"
              data-bs-toggle="dropdown" aria-label="Masa işlemleri">
              <i class="icon-base ti tabler-dots-vertical icon-md"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-table-edit
                data-action="{{ route('tables.update', $table) }}"
                data-id="{{ $table->id }}"
                data-number="{{ $table->number }}"
                data-area="{{ $table->area }}"
                data-capacity="{{ $table->capacity }}"
                data-active="{{ $table->is_active ? 1 : 0 }}">
                <i class="icon-base ti tabler-edit icon-sm"></i> Düzenle
              </button>
              <div class="dropdown-divider"></div>
              <x-delete-button :action="route('tables.destroy', $table)" confirm="{{ $table->label() }} silinsin mi?"
                text="Sipariş geçmişi olan masalar silinemez; bunun yerine pasif yapabilirsiniz." />
            </div>
          </div>
        @endif
      </div>

      <h5 class="mb-1">{{ $table->label() }}</h5>
      <div class="d-flex align-items-center gap-2 mb-3 small text-body-secondary">
        <span><i class="icon-base ti tabler-users icon-14px me-1"></i>{{ $table->capacity }} kişilik</span>
      </div>

      <div class="mt-auto">
        @if (! $table->is_active)
          <span class="badge bg-label-secondary">Pasif</span>
        @elseif ($table->openOrder)
          <div class="bg-label-danger rounded p-2 small">
            <div class="d-flex justify-content-between">
              <span>Hesap</span><strong>{{ money($table->openOrder->total) }}</strong>
            </div>
            <div class="d-flex justify-content-between">
              <span><i class="icon-base ti tabler-clock icon-12px me-1"></i>{{ $table->openOrder->opened_at->diffForHumans(null, true) }}</span>
              <span class="text-truncate ms-2">{{ $table->openOrder->user->name }}</span>
            </div>
          </div>
        @else
          <x-status-badge :status="$table->status" />
        @endif
      </div>
    </div>
  </div>
</div>
