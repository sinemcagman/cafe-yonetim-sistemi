@php
  $width = $width ?? '30';
  $height = $height ?? '30';
@endphp

{{-- Uygulama logosu: buharı tüten kahve fincanı (renk temanın ana renginden gelir) --}}
<span class="text-primary">
  <svg width="{{ $width }}" height="{{ $height }}" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true">
    <path d="M4 10h12v4.5a5.5 5.5 0 0 1 -5.5 5.5h-1a5.5 5.5 0 0 1 -5.5 -5.5z" fill="currentColor" />
    <path d="M16 11h1.5a2.5 2.5 0 0 1 0 5h-1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
      stroke-linejoin="round" />
    <path d="M7 3c-.6 .9 -.6 1.8 0 2.7c.6 .9 .6 1.8 0 2.7M10.5 3c-.6 .9 -.6 1.8 0 2.7c.6 .9 .6 1.8 0 2.7M14 3c-.6 .9 -.6 1.8 0 2.7c.6 .9 .6 1.8 0 2.7"
      stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity=".55" />
    <path d="M3 21h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
  </svg>
</span>
