@props(['variant' => null, 'container' => true])
<section {{ $attributes->merge(['class' => 'ks-sec' . ($variant ? ' ks-sec--' . $variant : '')]) }}>
    @if($container)
        <div class="ks-container">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</section>
