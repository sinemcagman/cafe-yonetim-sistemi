    <!-- begin::GXON Page Header -->
    <header class="app-header">
      <div class="app-header-inner">
        <button class="app-toggler" type="button" aria-label="Menüyü aç/kapat">
          <span></span>
          <span></span>
          <span></span>
        </button>
        <div class="app-header-start d-none d-md-flex">
          <span class="text-body">
            <i class="fi fi-rr-calendar me-1"></i> {{ now()->translatedFormat('d F Y, l') }}
          </span>
        </div>
        <div class="app-header-end">
          <div class="px-lg-3 px-2 ps-0 d-flex align-items-center">
            <div class="dropdown">
              <button class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative" id="ld-theme" type="button" data-bs-auto-close="outside" aria-expanded="false" data-bs-toggle="dropdown">
                <i class="fi fi-rr-brightness scale-1x theme-icon-active"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="light" aria-pressed="false">
                    <i class="fi fi-rr-brightness scale-1x" data-theme="light"></i> Açık
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                    <i class="fi fi-rr-moon scale-1x" data-theme="dark"></i> Koyu
                  </button>
                </li>
                <li>
                  <button type="button" class="dropdown-item d-flex gap-2 align-items-center" data-bs-theme-value="auto" aria-pressed="true">
                    <i class="fi fi-br-circle-half-stroke scale-1x" data-theme="auto"></i> Otomatik
                  </button>
                </li>
              </ul>
            </div>
          </div>
          <div class="vr my-3"></div>
          @auth
            <div class="dropdown text-end ms-sm-3 ms-2 ms-lg-4">
              <a href="#" class="d-flex align-items-center py-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                <div class="text-end me-2 d-none d-lg-inline-block">
                  <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                  <small class="text-body d-block lh-sm">
                    <i class="fi fi-rr-angle-down text-3xs me-1"></i> {{ auth()->user()->role->label() }}
                  </small>
                </div>
                <div class="avatar avatar-sm rounded-circle avatar-status-success bg-primary text-white">
                  {{ auth()->user()->initials() }}
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                  <div class="avatar avatar-sm rounded-circle bg-primary text-white">
                    {{ auth()->user()->initials() }}
                  </div>
                  <div class="ms-2">
                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                    <small class="text-body d-block lh-sm">{{ auth()->user()->email }}</small>
                  </div>
                </li>
                <li>
                  <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                      <i class="fi fi-sr-exit scale-1x"></i> Çıkış Yap
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          @endauth
        </div>
      </div>
    </header>
    <!-- end::GXON Page Header -->
