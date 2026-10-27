{{-- Masa formu: hem yan panelde (offcanvas) hem ayrı sayfada kullanılır --}}
@csrf
<div class="row g-4 mb-6">
  <div class="col-6">
    <label class="form-label" for="table-number">Masa numarası</label>
    <input type="number" min="1" name="number" id="table-number" value="{{ old('number', $table->number) }}"
      @class(['form-control', 'is-invalid' => $errors->has('number')]) required>
    @error('number')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-6">
    <label class="form-label" for="table-capacity">Kapasite</label>
    <div class="input-group">
      <input type="number" min="1" max="50" name="capacity" id="table-capacity" value="{{ old('capacity', $table->capacity) }}"
        @class(['form-control', 'is-invalid' => $errors->has('capacity')]) required>
      <span class="input-group-text">kişi</span>
      @error('capacity')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>

<div class="mb-6">
  @php
    $selectedArea = old('area', $table->area);
    $areaOptions = \App\Models\DiningTable::query()->whereNotNull('area')->distinct()->orderBy('area')->pluck('area')
      ->merge(['Salon', 'Bahçe', 'Teras', 'Üst Kat'])
      ->when($selectedArea, fn ($areas) => $areas->push($selectedArea))
      ->unique()->values();
  @endphp
  <label class="form-label" for="table-area">Bölüm</label>
  <select name="area" id="table-area" @class(['select2 form-select', 'is-invalid' => $errors->has('area')])
    data-placeholder="Bölüm seçin ya da yazın" data-allow-clear="true" data-tags="true">
    <option value=""></option>
    @foreach ($areaOptions as $area)
      <option value="{{ $area }}" @selected($selectedArea === $area)>{{ $area }}</option>
    @endforeach
  </select>
  <div class="form-text">Listede yoksa yazıp Enter'a basarak yeni bölüm ekleyebilirsiniz. Masalar ekranında bölümlere göre gruplanır.</div>
  @error('area')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror
</div>

<div class="mb-6">
  <div class="form-check form-switch">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="table-active" value="1"
      @checked(old('is_active', $table->is_active))>
    <label class="form-check-label" for="table-active">Aktif — bu masaya sipariş açılabilsin</label>
  </div>
</div>
