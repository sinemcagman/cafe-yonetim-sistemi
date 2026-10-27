@props(['paginator', 'unit' => 'kayıt'])

{{-- Liste altı: kaçıncı kayıtların gösterildiği, sayfa başına kayıt seçimi ve sayfalama --}}
@if ($paginator->total() > 0)
  <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-4 border-top">
    <div class="d-flex align-items-center gap-3">
      <small class="text-body-secondary text-nowrap">
        Toplam {{ $paginator->total() }} {{ $unit }} · {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} gösteriliyor
      </small>
      <form method="GET" class="d-flex align-items-center gap-2">
        @foreach (request()->except(['adet', 'page']) as $key => $value)
          @if (is_scalar($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
          @endif
        @endforeach
        <label for="per-page" class="small text-body-secondary text-nowrap mb-0">Sayfa başına</label>
        <div style="width: 90px">
          <select name="adet" id="per-page" class="select2 form-select form-select-sm" data-search="false" data-auto-submit>
            @foreach (\App\Http\Controllers\Controller::PER_PAGE_OPTIONS as $option)
              <option value="{{ $option }}" @selected($paginator->perPage() === $option)>{{ $option }}</option>
            @endforeach
          </select>
        </div>
      </form>
    </div>
    {{ $paginator->onEachSide(1)->links() }}
  </div>
@endif
