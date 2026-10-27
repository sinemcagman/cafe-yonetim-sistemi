@if ($errors->any())
  <div class="alert alert-danger d-flex align-items-start gap-3" role="alert">
    <span class="alert-icon rounded"><i class="icon-base ti tabler-alert-circle icon-md"></i></span>
    <div>
      <h6 class="alert-heading mb-1">Kaydedilemedi, lütfen işaretli alanları kontrol edin.</h6>
      <ul class="mb-0 ps-4">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif
