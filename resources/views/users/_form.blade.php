{{-- Çalışan formu: create ve edit ortak kullanır --}}
@php
  use App\Enums\UserRole;
  $isSelf = $user->exists && $user->is(auth()->user());
  $roleHints = [
    UserRole::Yonetici->value => 'Menü, stok, masalar, çalışanlar ve işlem geçmişi; kasa ve siparişler dahil her şey.',
    UserRole::Garson->value => 'Sipariş açar, ürün ekler, kalem iptal eder ve hazırlık durumunu ilerletir.',
    UserRole::Kasiyer->value => 'Ödeme alır, kasayı açar/kapatır; siparişleri ve hazırlığı görüntüler.',
  ];
@endphp

@csrf
<x-form-errors />

<div class="row g-6">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><h5 class="card-title mb-0">Kişisel bilgiler</h5></div>
      <div class="card-body">
        <div class="mb-6">
          <label class="form-label" for="user-name">Ad soyad</label>
          <input type="text" name="name" id="user-name" value="{{ old('name', $user->name) }}" maxlength="100"
            @class(['form-control', 'is-invalid' => $errors->has('name')]) placeholder="Örn. Ayşe Demir" required autofocus>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
        <div class="row g-4 mb-6">
          <div class="col-md-7">
            <label class="form-label" for="user-email">E-posta (giriş için)</label>
            <input type="email" name="email" id="user-email" value="{{ old('email', $user->email) }}" maxlength="150" autocomplete="off"
              @class(['form-control', 'is-invalid' => $errors->has('email')]) placeholder="ornek@kafe.test" required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-5">
            <label class="form-label" for="user-phone">Telefon <span class="text-body-secondary">(isteğe bağlı)</span></label>
            <input type="tel" name="phone" id="user-phone" value="{{ old('phone', $user->phone) }}" maxlength="20"
              @class(['form-control', 'is-invalid' => $errors->has('phone')]) placeholder="05xx xxx xx xx">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="row g-4">
          <div class="col-md-6 form-password-toggle">
            <label class="form-label" for="user-password">{{ $user->exists ? 'Yeni şifre' : 'Şifre' }}</label>
            <div class="input-group input-group-merge">
              <input type="password" name="password" id="user-password" autocomplete="new-password"
                @class(['form-control', 'is-invalid' => $errors->has('password')]) placeholder="••••••••" @required(! $user->exists)>
              <span class="input-group-text cursor-pointer" title="Göster/gizle"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
            <div class="form-text">{{ $user->exists ? 'Değiştirmeyecekseniz boş bırakın. ' : '' }}En az 8 karakter, harf ve rakam.</div>
            @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6 form-password-toggle">
            <label class="form-label" for="user-password-confirmation">Şifre tekrarı</label>
            <div class="input-group input-group-merge">
              <input type="password" name="password_confirmation" id="user-password-confirmation" autocomplete="new-password" class="form-control" placeholder="••••••••">
              <span class="input-group-text cursor-pointer" title="Göster/gizle"><i class="icon-base ti tabler-eye-off"></i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card mb-6">
      <div class="card-header"><h5 class="card-title mb-0">Rol ve erişim</h5></div>
      <div class="card-body">
        <label class="form-label" for="user-role">Rol</label>
        <select name="role" id="user-role" class="select2 form-select" data-search="false" @disabled($isSelf)>
          @foreach (UserRole::cases() as $role)
            <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
          @endforeach
        </select>
        @if ($isSelf)
          <input type="hidden" name="role" value="{{ $user->role->value }}">
          <div class="form-text">Kendi rolünüzü değiştiremezsiniz.</div>
        @endif
        <div class="bg-lighter rounded p-3 mt-3 small" data-role-hint aria-live="polite">
          {{ $roleHints[old('role', $user->role?->value)] ?? '' }}
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0">Aktif</h6>
          <small class="text-body-secondary">Pasif çalışan giriş yapamaz; açık oturumu hemen kapanır.</small>
        </div>
        <div class="form-check form-switch mb-0">
          <input type="hidden" name="is_active" value="{{ $isSelf ? 1 : 0 }}">
          <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="user-active" value="1"
            @checked(old('is_active', $user->is_active)) @disabled($isSelf) aria-label="Aktif">
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const hints = @json($roleHints);
    const hint = document.querySelector('[data-role-hint]');
    $('#user-role').on('change', function () { hint.textContent = hints[this.value] || ''; });
  });
</script>
@endpush
