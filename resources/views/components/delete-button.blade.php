@props(['action', 'confirm' => 'Bu kayıt silinsin mi?', 'text' => 'Bu işlem geri alınamaz.'])

{{-- Silme işlemi: resources/js/app.js gönderimden önce SweetAlert2 ile onay ister --}}
<form method="POST" action="{{ $action }}" data-confirm="{{ $confirm }}" data-confirm-text="{{ $text }}" class="d-inline">
  @csrf
  @method('DELETE')
  <button type="submit" {{ $attributes->class(['dropdown-item text-danger d-flex align-items-center gap-2']) }}>
    <i class="icon-base ti tabler-trash icon-sm"></i> Sil
  </button>
</form>
