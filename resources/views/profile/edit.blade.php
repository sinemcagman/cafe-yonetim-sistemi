@extends('layouts/layoutMaster')

@section('title', 'Profilim')

@section('content')
<x-page-header title="Profilim" subtitle="Kendi bilgilerinizi ve şifrenizi güncelleyin." icon="tabler-user-circle">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Profilim</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="row g-6">
  <!-- Kimlik kartı -->
  <div class="col-xl-4">
    <div class="card mb-6">
      <div class="card-body text-center pt-8">
        <div class="avatar avatar-xl mx-auto mb-4">
          <span class="avatar-initial rounded-circle bg-label-{{ $user->role->color() }} fs-3">{{ $user->initials() }}</span>
        </div>
        <h5 class="mb-1">{{ $user->name }}</h5>
        <span class="badge bg-label-{{ $user->role->color() }} mb-4">{{ $user->role->label() }}</span>
        <ul class="list-unstyled text-start mb-0">
          <li class="d-flex align-items-center gap-2 mb-2"><i class="icon-base ti tabler-mail icon-18px text-body-secondary"></i>{{ $user->email }}</li>
          <li class="d-flex align-items-center gap-2 mb-2"><i class="icon-base ti tabler-phone icon-18px text-body-secondary"></i>{{ $user->phone ?: '—' }}</li>
          <li class="d-flex align-items-center gap-2"><i class="icon-base ti tabler-calendar icon-18px text-body-secondary"></i>{{ $user->created_at->translatedFormat('d F Y') }} tarihinden beri</li>
        </ul>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h5 class="card-title mb-0">Son işlemlerim</h5></div>
      <div class="card-body">
        @forelse ($recentActivity as $log)
          <div class="d-flex gap-3 {{ $loop->last ? '' : 'mb-3' }}">
            <span class="badge badge-dot bg-{{ $log->color() }} mt-2 flex-shrink-0"></span>
            <div>
              <div class="small text-heading">{{ $log->description }}</div>
              <small class="text-body-secondary">{{ $log->created_at->diffForHumans() }}</small>
            </div>
          </div>
        @empty
          <p class="text-body-secondary mb-0 small">Henüz işlem yok.</p>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <!-- Bilgiler -->
    <div class="card mb-6">
      <div class="card-header"><h5 class="card-title mb-0">Bilgilerim</h5></div>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PATCH')
          <div class="row g-4 mb-6">
            <div class="col-md-6">
              <label class="form-label" for="profile-name">Ad soyad</label>
              <input type="text" name="name" id="profile-name" maxlength="100" value="{{ old('name', $user->name) }}"
                @class(['form-control', 'is-invalid' => $errors->profile->has('name')]) required>
              @error('name', 'profile') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="profile-phone">Telefon</label>
              <input type="tel" name="phone" id="profile-phone" maxlength="20" value="{{ old('phone', $user->phone) }}"
                @class(['form-control', 'is-invalid' => $errors->profile->has('phone')]) placeholder="05xx xxx xx xx">
              @error('phone', 'profile') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="profile-email">E-posta (giriş için)</label>
              <input type="email" name="email" id="profile-email" maxlength="150" value="{{ old('email', $user->email) }}"
                @class(['form-control', 'is-invalid' => $errors->profile->has('email')]) required>
              @error('email', 'profile') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="profile-role">Rol</label>
              <input type="text" id="profile-role" class="form-control" value="{{ $user->role->label() }}" disabled>
              <div class="form-text">Rolünüzü yalnızca yönetici değiştirebilir.</div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Bilgileri Kaydet</button>
        </form>
      </div>
    </div>

    <!-- Şifre -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-1">Şifre değiştir</h5>
        <p class="card-subtitle mb-0">Şifre değişince diğer cihazlardaki oturumlarınız kapatılır.</p>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.password') }}" autocomplete="off">
          @csrf
          @method('PUT')
          <div class="row g-4 mb-6">
            <div class="col-md-4 form-password-toggle">
              <label class="form-label" for="current-password">Mevcut şifre</label>
              <div class="input-group input-group-merge">
                <input type="password" name="current_password" id="current-password" autocomplete="current-password"
                  @class(['form-control', 'is-invalid' => $errors->password->has('current_password')]) required>
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
              @error('current_password', 'password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-password-toggle">
              <label class="form-label" for="new-password">Yeni şifre</label>
              <div class="input-group input-group-merge">
                <input type="password" name="password" id="new-password" autocomplete="new-password"
                  @class(['form-control', 'is-invalid' => $errors->password->has('password')]) required>
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
              @error('password', 'password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4 form-password-toggle">
              <label class="form-label" for="new-password-confirmation">Yeni şifre tekrarı</label>
              <div class="input-group input-group-merge">
                <input type="password" name="password_confirmation" id="new-password-confirmation" autocomplete="new-password" class="form-control" required>
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>
          </div>
          <div class="form-text mb-4">En az 8 karakter; en az bir harf ve bir rakam içermeli.</div>
          <button type="submit" class="btn btn-primary">Şifreyi Değiştir</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
