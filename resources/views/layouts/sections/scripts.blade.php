<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js'])

@if ($configData['hasCustomizer'])
  @vite('resources/assets/vendor/libs/pickr/pickr.js')
@endif

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/js/menu.js'])

<!-- Uygulama geneli: silme onayı ve bildirimler -->
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/notyf/notyf.js'])

<!-- Uygulama geneli form bileşenleri: tüm seçim kutuları Select2, tüm tarih alanları flatpickr -->
@vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- Sayfaya özel satır içi script'ler -->
@stack('scripts')

<!-- Oturum mesajları: resources/js/app.js tarafından bildirim olarak gösterilir -->
<script>
  window.flashMessages = @json(['success' => session('success'), 'error' => session('error')]);
</script>

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->
