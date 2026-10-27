@props(['action', 'confirm' => 'Bu kaydı silmek istediğinize emin misiniz?'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))">
  @csrf
  @method('DELETE')
  <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
    <i class="fi fi-rr-trash"></i> Sil
  </button>
</form>
