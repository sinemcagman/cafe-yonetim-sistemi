<div class="col-xxl-2 col-lg-3 col-md-4 col-6">
  <div @class([
      'card',
      'bg-success bg-opacity-05 border-0 shadow-none' => $table->is_active && $table->isAvailable(),
      'bg-danger bg-opacity-05 border-0 shadow-none' => ! $table->isAvailable(),
      'opacity-50' => ! $table->is_active,
  ])>
    <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0 p-3">
      @if ($table->is_active)
        <x-status-badge :status="$table->status" />
      @else
        <span class="badge badge-lg bg-secondary-subtle text-secondary">Pasif</span>
      @endif
      @if (auth()->user()->isManager())
        <div class="btn-group">
          <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fi fi-rr-menu-dots"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('tables.edit', $table) }}">
                <i class="fi fi-rr-edit"></i> Düzenle
              </a>
            </li>
            <li>
              <x-delete-button :action="route('tables.destroy', $table)" confirm="{{ $table->label() }} silinsin mi?" />
            </li>
          </ul>
        </div>
      @endif
    </div>
    <div class="card-body p-3 pt-2 text-center">
      <div @class([
          'avatar avatar-lg rounded-circle text-white mx-auto mb-2',
          'bg-success shadow-success' => $table->isAvailable(),
          'bg-danger shadow-danger' => ! $table->isAvailable(),
      ])>
        <i class="fi fi-rr-restaurant"></i>
      </div>
      <h5 class="mb-0 fw-bold">{{ $table->label() }}</h5>
      <small class="text-muted">{{ $table->capacity }} kişilik</small>
      @if ($table->openOrder)
        <hr class="border-dashed my-2">
        <div class="text-start small">
          <div class="d-flex justify-content-between">
            <span>Hesap</span>
            <strong>{{ money($table->openOrder->total) }}</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Açılış</span>
            <span>{{ $table->openOrder->opened_at->diffForHumans() }}</span>
          </div>
          <div class="d-flex justify-content-between">
            <span>Garson</span>
            <span>{{ $table->openOrder->user->name }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
