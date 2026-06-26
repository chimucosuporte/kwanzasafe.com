@props(['variant' => 'primary', 'href' => null, 'size' => null, 'block' => false])
@php
    $cls = 'ks-btn ks-btn--' . $variant
        . ($size === 'lg' ? ' ks-btn--lg' : '')
        . ($block ? ' ks-btn--block' : '');
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $cls, 'type' => 'button']) }}>{{ $slot }}</button>
@endif
