@extends('layouts/layoutMaster')

@section('title', 'Çalışanlar')

@php
  use App\Enums\UserRole;
  $roleIcons = [UserRole::Yonetici->value => 'tabler-user-shield', UserRole::Garson->value => 'tabler-tools-kitchen-2', UserRole::Kasiyer->value => 'tabler-cash-register'];
  $filtered = collect(['ara', 'rol', 'durum'])->contains(fn ($key) => request()->filled($key));
@endphp

@section('content')
<x-page-header title="Çalışanlar" subtitle="Çalışan hesaplarını, rollerini ve erişimlerini yönetin." icon="tabler-users">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">Çalışanlar</li>
  </x-slot:breadcrumb>
  <x-slot:actions>
    <a href="{{ route('activity-logs.index') }}" class="btn btn-label-secondary">
      <i class="icon-base ti tabler-history icon-16px me-1"></i> İşlem Geçmişi
    </a>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
      <i class="icon-base ti tabler-user-plus icon-16px me-1"></i> Çalışan Ekle
    </a>
  </x-slot:actions>
</x-page-header>

<!-- Role göre aktif çalışan sayıları -->
<div class="row g-6 mb-6">
  @foreach ($roles as $role)
    <div class="col-sm-6 col-xl-3">
      <a href="{{ route('users.index', ['rol' => $role->value]) }}" class="card h-100 text-reset">
        <div class="card-body d-flex justify-content-between align-items-start">
          <div>
            <p class="mb-1">{{ $role->label() }}</p>
            <h4 class="mb-0">{{ $roleCounts[$role->value] ?? 0 }}</h4>
            <small class="text-body-secondary">aktif çalışan</small>
          </div>
          <span class="avatar"><span class="avatar-initial rounded bg-label-{{ $role->color() }}"><i class="icon-base ti {{ $roleIcons[$role->value] }} icon-26px"></i></span></span>
        </div>
      </a>
    </div>
  @endforeach
  <div class="col-sm-6 col-xl-3">
    <a href="{{ route('users.index', ['durum' => 'pasif']) }}" class="card h-100 text-reset">
      <div class="card-body d-flex justify-content-between align-items-start">
        <div>
          <p class="mb-1">Pasif</p>
          <h4 class="mb-0">{{ $inactiveCount }}</h4>
          <small class="text-body-secondary">giriş yapamaz</small>
        </div>
        <span class="avatar"><span class="avatar-initial rounded bg-label-secondary"><i class="icon-base ti tabler-user-off icon-26px"></i></span></span>
      </div>
    </a>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom">
    <form method="GET" action="{{ route('users.index') }}" class="row g-4 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="filter-user">Ara</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
          <input type="search" name="ara" id="filter-user" value="{{ request('ara') }}" class="form-control" placeholder="Ad ya da e-posta">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-role">Rol</label>
        <select name="rol" id="filter-role" class="select2 form-select" data-placeholder="Tüm roller" data-allow-clear="true" data-search="false" data-auto-submit>
          <option value=""></option>
          @foreach ($roles as $role)
            <option value="{{ $role->value }}" @selected(request('rol') === $role->value)>{{ $role->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter-state">Durum</label>
        <select name="durum" id="filter-state" class="select2 form-select" data-placeholder="Tümü" data-allow-clear="true" data-search="false" data-auto-submit>
          <option value=""></option>
          <option value="aktif" @selected(request('durum') === 'aktif')>Aktif</option>
          <option value="pasif" @selected(request('durum') === 'pasif')>Pasif</option>
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-icon" aria-label="Filtrele"><i class="icon-base ti tabler-filter"></i></button>
        @if ($filtered)
          <a href="{{ route('users.index') }}" class="btn btn-label-secondary btn-icon" data-bs-toggle="tooltip" title="Filtreleri temizle"><i class="icon-base ti tabler-x"></i></a>
        @endif
      </div>
    </form>
  </div>

  @if ($users->isEmpty())
    <x-empty-state icon="tabler-users" title="Çalışan bulunamadı" text="Filtreleri değiştirin ya da yeni bir çalışan ekleyin." />
  @else
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Çalışan</th>
            <th>Telefon</th>
            <th>Rol</th>
            <th>Durum</th>
            <th>Son giriş</th>
            <th class="text-end">İşlemler</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($users as $user)
            <tr @class(['opacity-75' => ! $user->is_active])>
              <td>
                <div class="d-flex align-items-center">
                  <div class="avatar avatar-sm me-3">
                    <span class="avatar-initial rounded-circle bg-label-{{ $user->role->color() }}">{{ $user->initials() }}</span>
                  </div>
                  <div class="d-flex flex-column">
                    <a href="{{ route('users.edit', $user) }}" class="fw-medium text-heading">
                      {{ $user->name }}
                      @if ($user->is(auth()->user()))
                        <span class="badge bg-label-primary ms-1">Siz</span>
                      @endif
                    </a>
                    <small class="text-body-secondary">{{ $user->email }}</small>
                  </div>
                </div>
              </td>
              <td>{{ $user->phone ?: '—' }}</td>
              <td><x-status-badge :status="$user->role" /></td>
              <td><x-status-badge :active="$user->is_active" /></td>
              <td>
                @if ($user->last_login_at)
                  <span title="{{ $user->last_login_at->format('d.m.Y H:i') }}">{{ $user->last_login_at->diffForHumans() }}</span>
                @else
                  <span class="text-body-secondary">Hiç giriş yapmadı</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('activity-logs.index', ['calisan' => $user->id]) }}" class="btn btn-icon btn-text-secondary rounded-pill" data-bs-toggle="tooltip" title="İşlem geçmişi">
                  <i class="icon-base ti tabler-history icon-md"></i>
                </a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-icon btn-text-secondary rounded-pill" data-bs-toggle="tooltip" title="Düzenle">
                  <i class="icon-base ti tabler-edit icon-md"></i>
                </a>
                @unless ($user->is(auth()->user()))
                  <div class="d-inline-block">
                    <button type="button" class="btn btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-label="Diğer işlemler">
                      <i class="icon-base ti tabler-dots-vertical icon-md"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <x-delete-button :action="route('users.destroy', $user)" :confirm="$user->name.' silinsin mi?'"
                        text="Sipariş ya da kasa kaydı olan çalışanlar silinemez; bunun yerine pasif yapabilirsiniz." />
                    </div>
                  </div>
                @endunless
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <x-list-footer :paginator="$users" unit="çalışan" />
  @endif
</div>
@endsection
