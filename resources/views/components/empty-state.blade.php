@props(['icon' => 'tabler-mood-empty', 'title', 'text' => null])

{{-- Liste boş olduğunda kullanıcıyı bir sonraki adıma yönlendiren alan --}}
<div {{ $attributes->class(['text-center py-10 px-4']) }}>
  <div class="avatar avatar-xl mx-auto mb-4">
    <span class="avatar-initial rounded-circle bg-label-secondary"><i class="icon-base ti {{ $icon }} icon-32px"></i></span>
  </div>
  <h5 class="mb-1">{{ $title }}</h5>
  @if ($text)
    <p class="text-body-secondary mb-4">{{ $text }}</p>
  @endif
  {{ $slot }}
</div>
