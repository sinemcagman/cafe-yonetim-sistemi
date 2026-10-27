@csrf
<div class="row g-4">
  <div class="col-md-4">
    <label class="form-label" for="number">Masa Numarası</label>
    <input type="number" min="1" name="number" id="number" value="{{ old('number', $table->number) }}" @class(['form-control', 'is-invalid' => $errors->has('number')]) required>
    @error('number')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label" for="area">Bölüm</label>
    <input type="text" name="area" id="area" list="areaOptions" value="{{ old('area', $table->area) }}" @class(['form-control', 'is-invalid' => $errors->has('area')]) placeholder="Örn. Salon, Bahçe">
    <datalist id="areaOptions">
      <option value="Salon"></option>
      <option value="Bahçe"></option>
      <option value="Teras"></option>
    </datalist>
    @error('area')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label" for="capacity">Kapasite (kişi)</label>
    <input type="number" min="1" name="capacity" id="capacity" value="{{ old('capacity', $table->capacity) }}" @class(['form-control', 'is-invalid' => $errors->has('capacity')]) required>
    @error('capacity')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1" @checked(old('is_active', $table->is_active))>
      <label class="form-check-label" for="is_active">Aktif (sipariş açılabilir)</label>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-4">
  <button type="submit" class="btn btn-primary waves-effect waves-light">Kaydet</button>
  <a href="{{ route('tables.index') }}" class="btn btn-outline-light btn-shadow waves-effect">Vazgeç</a>
</div>
