@php
$containerFooter =
isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
? 'container-xxl'
: 'container-fluid';
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        &#169; {{ now()->year }} {{ config('variables.templateSuffix') }}
      </div>
      <div class="d-none d-lg-inline-block text-body-secondary">
        @auth
          <i class="icon-base ti tabler-user-circle icon-sm me-1"></i>{{ auth()->user()->name }}
          <span class="mx-2">·</span>{{ auth()->user()->role->label() }}
        @endauth
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
