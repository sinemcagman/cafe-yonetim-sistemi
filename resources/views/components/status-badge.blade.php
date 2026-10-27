@props(['status' => null, 'active' => null, 'activeText' => 'Aktif', 'inactiveText' => 'Pasif'])

@if ($status)
  <span {{ $attributes->class(['badge', "bg-label-{$status->color()}"]) }}>{{ $status->label() }}</span>
@else
  <span {{ $attributes->class(['badge', $active ? 'bg-label-success' : 'bg-label-secondary']) }}>{{ $active ? $activeText : $inactiveText }}</span>
@endif
