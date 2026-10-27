{{-- Vuexy sayfalama görünümü (AppServiceProvider içinde varsayılan olarak ayarlanır) --}}
@if ($paginator->hasPages())
  <nav aria-label="Sayfalama">
    <ul class="pagination pagination-sm mb-0">
      {{-- İlk ve önceki sayfa --}}
      <li @class(['page-item first', 'disabled' => $paginator->onFirstPage()])>
        <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="İlk sayfa">
          <i class="icon-base ti tabler-chevrons-left icon-sm"></i>
        </a>
      </li>
      <li @class(['page-item prev', 'disabled' => $paginator->onFirstPage()])>
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Önceki sayfa">
          <i class="icon-base ti tabler-chevron-left icon-sm"></i>
        </a>
      </li>

      {{-- Sayfa numaraları --}}
      @foreach ($elements as $element)
        @if (is_string($element))
          <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
            @else
              <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Sonraki ve son sayfa --}}
      <li @class(['page-item next', 'disabled' => ! $paginator->hasMorePages()])>
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Sonraki sayfa">
          <i class="icon-base ti tabler-chevron-right icon-sm"></i>
        </a>
      </li>
      <li @class(['page-item last', 'disabled' => ! $paginator->hasMorePages()])>
        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Son sayfa">
          <i class="icon-base ti tabler-chevrons-right icon-sm"></i>
        </a>
      </li>
    </ul>
  </nav>
@endif
