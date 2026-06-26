@props(['title', 'meta' => null, 'back' => null])

@once
@push('head')
<style>
    .ks-atb { background:#000; color:#fff; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    .ks-atb__back { display:inline-flex; align-items:center; gap:0.4rem; color:rgba(255,255,255,0.85); text-decoration:none; font-size:0.78rem; font-weight:600; padding:6px 10px; border-radius:8px; transition:background 0.15s; white-space:nowrap; flex-shrink:0; }
    .ks-atb__back:hover { background:rgba(255,255,255,0.12); color:#fff; }
    .ks-atb__brand { display:flex; align-items:center; gap:0.6rem; min-width:0; }
    .ks-atb__logo { height:28px; width:auto; flex-shrink:0; }
    .ks-atb__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .ks-atb__meta { font-size:0.7rem; color:rgba(255,255,255,0.55); white-space:nowrap; flex-shrink:0; }
    @media (max-width:560px){ .ks-atb__back span { display:none; } .ks-atb__meta { display:none; } .ks-atb { padding:0.75rem 1rem; } }
</style>
@endpush
@endonce

<header class="ks-atb">
    <a href="{{ $back ?? route('admin.dashboard') }}" class="ks-atb__back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Painel</span>
    </a>
    <div class="ks-atb__brand">
        <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="KwanzaSafe" class="ks-atb__logo">
        <span class="ks-atb__title">{{ $title }}</span>
    </div>
    @if($meta)
        <div class="ks-atb__meta">{{ $meta }}</div>
    @else
        <span style="width:1px;"></span>
    @endif
</header>
