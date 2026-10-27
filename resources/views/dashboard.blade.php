@extends('layouts.app')

@section('title', 'Panel')

@section('page-title')
  Merhaba, {{ auth()->user()->name }}
@endsection

@section('content')
  <div class="row">
    <div class="col-6 col-lg-3">
      <div class="card bg-success bg-opacity-05 shadow-none border-0">
        <div class="card-body">
          <div class="avatar bg-success shadow-success rounded-circle text-white mb-3">
            <i class="fi fi-sr-restaurant"></i>
          </div>
          <h3>{{ $stats['available_tables'] }} <small class="fs-6 text-muted">/ {{ $stats['total_tables'] }}</small></h3>
          <h6 class="mb-0">Boş Masa</h6>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card bg-info bg-opacity-05 shadow-none border-0">
        <div class="card-body">
          <div class="avatar bg-info shadow-info rounded-circle text-white mb-3">
            <i class="fi fi-sr-receipt"></i>
          </div>
          <h3>{{ $stats['open_orders'] }}</h3>
          <h6 class="mb-0">Açık Sipariş</h6>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card bg-primary bg-opacity-05 shadow-none border-0">
        <div class="card-body">
          <div class="avatar bg-primary shadow-primary rounded-circle text-white mb-3">
            <i class="fi fi-sr-wallet"></i>
          </div>
          <h3>{{ money($stats['today_revenue']) }}</h3>
          <h6 class="mb-0">Bugünkü Tahsilat</h6>
        </div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card bg-danger bg-opacity-05 shadow-none border-0">
        <div class="card-body">
          <div class="avatar bg-danger shadow-danger rounded-circle text-white mb-3">
            <i class="fi fi-sr-box-open"></i>
          </div>
          <h3>{{ $stats['low_stock'] }}</h3>
          <h6 class="mb-0">Kritik Stoktaki Ürün</h6>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div @class(['col-xxl-8' => auth()->user()->isManager(), 'col-12' => ! auth()->user()->isManager()])>
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
          <h6 class="card-title mb-0">Masa Durumu</h6>
          <a href="{{ route('tables.index') }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">Tüm Masalar</a>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2">
            @forelse ($tables as $table)
              <a href="{{ route('tables.index') }}" @class([
                  'btn waves-effect minw-100px py-3',
                  'btn-subtle-success' => $table->isAvailable(),
                  'btn-subtle-danger' => ! $table->isAvailable(),
              ]) title="{{ $table->status->label() }}">
                <i class="fi fi-rr-restaurant d-block mb-1"></i>
                {{ $table->label() }}
              </a>
            @empty
              <p class="text-muted mb-0">Henüz masa tanımlanmadı.</p>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    @if (auth()->user()->isManager())
      <div class="col-xxl-4">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between border-0 pb-0">
            <h6 class="card-title mb-0">Kritik Stok</h6>
            <a href="{{ route('products.index', ['kritik' => 1]) }}" class="btn btn-sm btn-outline-light btn-shadow waves-effect">Tümü</a>
          </div>
          <div class="card-body">
            <ul class="list-group list-group-hover list-group-smooth list-group-unlined">
              @forelse ($lowStockProducts as $product)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                  <div>
                    <h6 class="mb-0">{{ $product->name }}</h6>
                    <small class="text-muted">{{ $product->category->name }}</small>
                  </div>
                  <span class="badge badge-lg bg-danger-subtle text-danger">{{ $product->stock_quantity }} adet</span>
                </li>
              @empty
                <li class="list-group-item px-0 text-muted">Tüm ürünlerin adedi yeterli.</li>
              @endforelse
            </ul>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card overflow-hidden">
          <div class="card-header border-0 pb-0">
            <h6 class="card-title mb-0">Son İşlemler</h6>
          </div>
          <div class="card-body p-2">
            <div class="table-responsive">
              <table class="table display table-row-rounded mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Zaman</th>
                    <th>Çalışan</th>
                    <th class="minw-250px">İşlem</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($recentActivities as $activity)
                    <tr>
                      <td class="text-nowrap" title="{{ $activity->created_at->format('d.m.Y H:i') }}">{{ $activity->created_at->diffForHumans() }}</td>
                      <td>{{ $activity->user?->name ?? 'Sistem' }}</td>
                      <td>{{ $activity->description }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="3" class="text-center text-muted py-4">Henüz kayıtlı işlem yok.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
@endsection
