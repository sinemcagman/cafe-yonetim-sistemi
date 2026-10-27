@extends('layouts.app')

@section('title', 'Stok Hareketleri')

@section('breadcrumb')
  <li class="breadcrumb-item active" aria-current="page">Stok Hareketleri</li>
@endsection

@section('content')
  <div class="card overflow-hidden">
    <div class="card-header border-0 pb-0">
      <form method="GET" action="{{ route('stock-movements.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
          <select name="urun" class="form-select">
            <option value="">Tüm ürünler</option>
            @foreach ($products as $product)
              <option value="{{ $product->id }}" @selected(request('urun') == $product->id)>{{ $product->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <select name="tur" class="form-select">
            <option value="">Tüm hareket türleri</option>
            @foreach ($types as $type)
              <option value="{{ $type->value }}" @selected(request('tur') === $type->value)>{{ $type->label() }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary waves-effect w-100">Filtrele</button>
          @if (request()->hasAny(['urun', 'tur']))
            <a href="{{ route('stock-movements.index') }}" class="btn btn-outline-light btn-shadow btn-icon waves-effect" title="Temizle">
              <i class="fi fi-rr-cross-small"></i>
            </a>
          @endif
        </div>
      </form>
    </div>
    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table display table-row-rounded mb-0">
          <thead class="table-light">
            <tr>
              <th>Tarih</th>
              <th class="minw-150px">Ürün</th>
              <th>Tür</th>
              <th class="text-end">Değişim</th>
              <th class="text-end">Önce → Sonra</th>
              <th class="minw-200px">Neden</th>
              <th>Çalışan</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($movements as $movement)
              <tr>
                <td class="text-nowrap">{{ $movement->created_at->format('d.m.Y H:i') }}</td>
                <td class="fw-semibold">{{ $movement->product->name }}</td>
                <td><x-status-badge :status="$movement->type" /></td>
                <td @class(['text-end fw-semibold', 'text-success' => $movement->quantity > 0, 'text-danger' => $movement->quantity < 0])>
                  {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                </td>
                <td class="text-end text-nowrap">{{ $movement->quantity_before }} → {{ $movement->quantity_after }}</td>
                <td>{{ $movement->reason ?? '—' }}</td>
                <td>{{ $movement->user?->name ?? 'Sistem' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Kayıtlı stok hareketi yok.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($movements->hasPages())
        <div class="px-2 pt-3">{{ $movements->links() }}</div>
      @endif
    </div>
  </div>
@endsection
