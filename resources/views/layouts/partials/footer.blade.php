    <!-- begin::GXON Footer -->
    <footer class="footer-wrapper bg-body">
      <div class="container">
        <div class="row g-2">
          <div class="col-lg-6 col-md-7 text-center text-md-start">
            <p class="mb-0">© <span class="currentYear">{{ now()->year }}</span> {{ config('app.name') }}</p>
          </div>
          <div class="col-lg-6 col-md-5">
            <ul class="d-flex list-inline mb-0 gap-3 flex-wrap justify-content-center justify-content-md-end">
              <li>
                <a class="text-body" href="{{ route('dashboard') }}">Panel</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
    <!-- end::GXON Footer -->
