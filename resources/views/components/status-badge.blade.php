@props(['status' => null, 'active' => null])

@if ($status)
  <span {{ $attributes->class(['badge badge-lg', "bg-{$status->color()}-subtle text-{$status->color()}"]) }}>{{ $status->label() }}</span>
@else
  <span {{ $attributes->class(['badge badge-lg', $active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary']) }}>{{ $active ? 'Aktif' : 'Pasif' }}</span>
@endif
