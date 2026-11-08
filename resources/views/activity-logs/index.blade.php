@extends('layouts/layoutMaster')

@section('title', 'İşlem Geçmişi')

@php
  $filtered = collect(['calisan', 'grup', 'ara', 'tarih'])->contains(fn ($key) => request()->filled($key));
  $propertyLabels = [
    'urun' => 'Ürün', 'adet' => 'Adet', 'birim_fiyat' => 'Birim fiyat', 'neden' => 'Neden', 'tutar' => 'Tutar',
    'yontem' => 'Yöntem', 'kalan' => 'Kalan', 'onceki' => 'Önceki durum', 'yeni' => 'Yeni durum', 'tur' => 'Tür', 'not' => 'Not',
  ];
@endphp

@section('content')
<x-page-header title="İşlem Geçmişi" subtitle="Siparişler, kasa, stok ve çalışan işlemlerinin kaydı: kim, ne zaman, ne yaptı." icon="tabler-history">
  <x-slot:breadcrumb>
    <li class="breadcrumb-item active">İşlem Geçmişi</li>
  </x-slot:breadcrumb>
</x-page-header>

<div class="card mb-6">
  <div class="card-body">
    <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-4 align-items-end">
      <div class="col-md-3">
        <label class="form-label" for="log-user">Çalışan</label>
        <select name="calisan" id="log-user" class="select2 form-select" data-placeholder="Tüm çalışanlar" data-allow-clear="true" data-auto-submit>
          <option value=""></option>
          @foreach ($users as $user)
            <option value="{{ $user->id }}" @selected(request('calisan') == $user->id)>{{ $user->name }} · {{ $user->role->label() }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="log-group">İşlem türü</label>
        <select name="grup" id="log-group" class="select2 form-select" data-placeholder="Tüm işlemler" data-allow-clear="true" data-search="false" data-auto-submit>
          <option value=""></option>
          @foreach ($groups as $key => $group)
            <option value="{{ $key }}" @selected(request('grup') === $key)>{{ $group['label'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="log-date">Tarih aralığı</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
          <input type="text" name="tarih" id="log-date" class="form-control" value="{{ request('tarih') }}" placeholder="Tarih seçin"
            data-datepicker="range" data-max-date="{{ today()->toDateString() }}" data-auto-submit>
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="log-search">Açıklamada ara</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
          <input type="search" name="ara" id="log-search" class="form-control" value="{{ request('ara') }}" placeholder="Örn. Masa 4">
        </div>
      </div>
      @if (request()->filled('adet'))
        <input type="hidden" name="adet" value="{{ request('adet') }}">
      @endif
      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="icon-base ti tabler-filter icon-16px me-1"></i> Filtrele</button>
        @if ($filtered)
          <a href="{{ route('activity-logs.index') }}" class="btn btn-label-secondary">Temizle</a>
        @endif
      </div>
    </form>
  </div>
</div>

<div class="card">
  @if ($logs->isEmpty())
    <x-empty-state icon="tabler-history" title="Kayıt bulunamadı" text="Filtrelere uyan işlem yok." />
  @else
    <div class="card-body">
      @foreach ($logs->groupBy(fn ($log) => $log->created_at->toDateString()) as $day => $dayLogs)
        <h6 class="text-body-secondary text-uppercase small fw-medium {{ $loop->first ? '' : 'mt-6' }} mb-4">
          {{ \Illuminate\Support\Carbon::parse($day)->isToday() ? 'Bugün' : (\Illuminate\Support\Carbon::parse($day)->isYesterday() ? 'Dün' : \Illuminate\Support\Carbon::parse($day)->translatedFormat('d F Y, l')) }}
        </h6>
        <ul class="timeline mb-0">
          @foreach ($dayLogs as $log)
            <li class="timeline-item timeline-item-transparent {{ $loop->last ? 'border-transparent pb-0' : '' }}">
              <span class="timeline-indicator-advanced timeline-indicator-{{ $log->color() }}">
                <i class="icon-base ti {{ $log->icon() }} icon-14px"></i>
              </span>
              <div class="timeline-event {{ $loop->last ? 'pb-0' : '' }}">
                <div class="timeline-header mb-1">
                  <h6 class="mb-0">{{ $log->description }}</h6>
                  <small class="text-body-secondary text-nowrap">{{ $log->created_at->format('H:i:s') }}</small>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                  @if ($log->user)
                    <a href="{{ request()->fullUrlWithQuery(['calisan' => $log->user_id, 'page' => null]) }}" class="d-inline-flex align-items-center gap-1 text-body">
                      <span class="avatar avatar-xs"><span class="avatar-initial rounded-circle bg-label-{{ $log->user->role->color() }}">{{ $log->user->initials() }}</span></span>
                      {{ $log->user->name }}
                    </a>
                  @else
                    <span class="text-body-secondary">Sistem</span>
                  @endif
                  @if ($log->group())
                    <span class="badge bg-label-secondary">{{ $groups[$log->group()]['label'] }}</span>
                  @endif
                  @if ($log->subject_type === 'App\\Models\\Order' && $log->subject_id)
                    <a href="{{ route('orders.show', $log->subject_id) }}" class="d-inline-flex align-items-center gap-1">
                      <i class="icon-base ti tabler-external-link icon-12px"></i> Siparişe git
                    </a>
                  @endif
                </div>
                @if ($log->properties)
                  <details class="mt-2">
                    <summary class="small text-body-secondary">Ayrıntılar</summary>
                    <dl class="row small mb-0 mt-2">
                      @foreach ($log->properties as $key => $value)
                        <dt class="col-sm-3 fw-normal text-body-secondary">{{ $propertyLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                        <dd class="col-sm-9 mb-1">{{ is_scalar($value) || $value === null ? ($value ?? '—') : json_encode($value, JSON_UNESCAPED_UNICODE) }}</dd>
                      @endforeach
                    </dl>
                  </details>
                @endif
              </div>
            </li>
          @endforeach
        </ul>
      @endforeach
    </div>
    <x-list-footer :paginator="$logs" unit="işlem" />
  @endif
</div>
@endsection
