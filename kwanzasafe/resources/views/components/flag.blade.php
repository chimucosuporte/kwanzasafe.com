@props(['code' => 'eur', 'size' => 28])
@php
    $code = strtolower($code);
    $labels = ['eur' => 'Euro', 'brl' => 'Real brasileiro', 'aoa' => 'Kwanza angolano', 'usdc' => 'USD Coin', 'usdt' => 'Tether'];
@endphp
<span style="display:inline-flex;width:{{ $size }}px;height:{{ $size }}px;border-radius:50%;overflow:hidden;flex-shrink:0;box-shadow:0 0 0 1px rgba(15,23,42,.08);" role="img" aria-label="{{ $labels[$code] ?? strtoupper($code) }}">
    @switch($code)
        @case('eur')
            <svg viewBox="0 0 32 32" width="{{ $size }}" height="{{ $size }}" aria-hidden="true"><rect width="32" height="32" fill="#003399"/>@for($i = 0; $i < 12; $i++)<circle cx="{{ round(16 + 9 * cos($i * M_PI / 6 - M_PI / 2), 2) }}" cy="{{ round(16 + 9 * sin($i * M_PI / 6 - M_PI / 2), 2) }}" r="1.5" fill="#ffcc00"/>@endfor</svg>
            @break
        @case('brl')
            <svg viewBox="0 0 32 32" width="{{ $size }}" height="{{ $size }}" aria-hidden="true"><rect width="32" height="32" fill="#009c3b"/><path d="M16 5 L29 16 L16 27 L3 16 Z" fill="#ffdf00"/><circle cx="16" cy="16" r="6" fill="#002776"/></svg>
            @break
        @case('aoa')
            <svg viewBox="0 0 32 32" width="{{ $size }}" height="{{ $size }}" aria-hidden="true"><rect width="32" height="16" fill="#cc092f"/><rect y="16" width="32" height="16" fill="#0a0a0a"/><g transform="translate(16,16)" stroke="#ffcb00" fill="none" stroke-width="1.5"><circle r="4.3"/><path d="M-4.5 -1.5 L4.5 1.5" stroke-linecap="round"/></g><path d="M16 12.6l.8 1.7 1.8.2-1.4 1.3.4 1.8-1.6-.9-1.6.9.4-1.8-1.4-1.3 1.8-.2z" fill="#ffcb00"/></svg>
            @break
        @case('usdc')
        @case('usdt')
            <svg viewBox="0 0 32 32" width="{{ $size }}" height="{{ $size }}" aria-hidden="true"><circle cx="16" cy="16" r="16" fill="{{ $code === 'usdt' ? '#26a17b' : '#2775ca' }}"/><text x="16" y="22" font-size="16" font-family="Arial, sans-serif" font-weight="bold" fill="#fff" text-anchor="middle">$</text></svg>
            @break
        @default
            <svg viewBox="0 0 32 32" width="{{ $size }}" height="{{ $size }}" aria-hidden="true"><circle cx="16" cy="16" r="16" fill="#94a3b8"/></svg>
    @endswitch
</span>
