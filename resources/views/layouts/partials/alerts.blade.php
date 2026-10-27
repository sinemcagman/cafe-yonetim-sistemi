@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="fi fi-rr-check-circle"></i>
    <span>{{ session('success') }}</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
    <i class="fi fi-rr-cross-circle"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center gap-2 mb-1">
      <i class="fi fi-rr-exclamation"></i>
      <strong>Lütfen formdaki hataları düzeltin.</strong>
    </div>
    <ul class="mb-0 ps-4">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
  </div>
@endif
