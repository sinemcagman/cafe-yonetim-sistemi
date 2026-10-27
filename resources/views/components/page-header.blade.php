@props(['title', 'subtitle' => null, 'icon' => null])

{{-- Sayfa başlığı: başlık, kısa açıklama, breadcrumb ve sağda işlem butonları --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-6">
  <div class="d-flex align-items-center gap-3">
    @if ($icon)
      <div class="avatar avatar-md flex-shrink-0">
        <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti {{ $icon }} icon-26px"></i></span>
      </div>
    @endif
    <div>
      @isset($breadcrumb)
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb breadcrumb-custom-icon mb-1">
            <li class="breadcrumb-item">
              <a href="{{ route('dashboard') }}">Panel</a>
              <i class="breadcrumb-icon icon-base ti tabler-chevron-right align-middle icon-xs"></i>
            </li>
            {{ $breadcrumb }}
          </ol>
        </nav>
      @endisset
      <h4 class="mb-0">{{ $title }}</h4>
      @if ($subtitle)
        <p class="mb-0 text-body-secondary">{{ $subtitle }}</p>
      @endif
    </div>
  </div>
  @isset($actions)
    <div class="d-flex flex-wrap align-items-center gap-2">
      {{ $actions }}
    </div>
  @endisset
</div>
