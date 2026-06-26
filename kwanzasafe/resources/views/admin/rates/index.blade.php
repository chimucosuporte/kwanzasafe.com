<x-app-layout>

@push('head')
<title>Taxas de Câmbio — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.rt-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.rt-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.rt-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.rt-back:hover { color:white; }
.rt-logo { height:28px; }
.rt-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.rt-container { max-width:1100px; margin:0 auto; padding:1.5rem; }

.rt-flash { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; font-weight:600; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }

.rt-head { margin-bottom:1.25rem; }
.rt-head h1 { font-family:'Syne',sans-serif; font-weight:800; font-size:1.35rem; margin:0; }
.rt-head p { font-size:0.8rem; color:#737373; margin:2px 0 0; }

.rt-grid { display:grid; gap:0.875rem; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); }
.rt-card { background:white; border:1px solid #e5e5e5; border-radius:16px; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.03); position:relative; overflow:hidden; }
.rt-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:#009d44; }
.rt-card.off::before { background:#d4d4d4; }
.rt-card__pair { display:flex; align-items:center; gap:0.625rem; margin-bottom:0.875rem; }
.rt-card__icon { width:40px; height:40px; border-radius:12px; background:#f0faf4; color:#009d44; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; flex-shrink:0; }
.rt-card.off .rt-card__icon { background:#f5f5f5; color:#a3a3a3; }
.rt-card__pair-name { font-family:'Syne',sans-serif; font-weight:800; font-size:1rem; }
.rt-card__pair-sub { font-size:0.65rem; color:#a3a3a3; text-transform:uppercase; letter-spacing:0.08em; font-weight:700; }
.rt-card__rate { font-family:'Syne',sans-serif; font-weight:800; font-size:1.75rem; letter-spacing:-0.02em; color:#000; }
.rt-card__rate small { font-size:0.8rem; color:#737373; font-family:'JetBrains Mono',monospace; }
.rt-card__foot { display:flex; align-items:center; justify-content:space-between; margin-top:1rem; }
.rt-badge { font-size:0.6rem; font-weight:800; padding:4px 10px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.rt-badge.on { background:#d1f2e0; color:#007a34; }
.rt-badge.off { background:#fee2e2; color:#991b1b; }
.rt-edit { background:#000; color:white; text-decoration:none; padding:0.5rem 1rem; border-radius:8px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.7rem; text-transform:uppercase; letter-spacing:0.05em; transition:background 0.15s; }
.rt-edit:hover { background:#009d44; }
.rt-empty { text-align:center; padding:4rem 1.5rem; color:#a3a3a3; background:white; border:1px solid #e5e5e5; border-radius:16px; }
</style>
@endpush

<div class="rt-app">

<x-admin-topbar title="Taxas de Câmbio" meta="{{ count($rates) }} pares" />

<div class="rt-container">

    @if(session('success'))
        <div class="rt-flash">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="rt-head">
        <h1>Gestão de Câmbio</h1>
        <p>Define a taxa em Kwanzas (AOA) por cada unidade de moeda e controla a disponibilidade na calculadora do cliente.</p>
    </div>

    @if(count($rates) === 0)
        <div class="rt-empty">
            <div style="font-size:2.5rem;margin-bottom:0.5rem;">💱</div>
            <div style="font-family:'Syne',sans-serif;font-weight:800;">Sem taxas configuradas</div>
        </div>
    @else
        <div class="rt-grid">
            @foreach($rates as $r)
                <div class="rt-card {{ $r->is_active ? '' : 'off' }}">
                    <div class="rt-card__pair">
                        <div class="rt-card__icon">{{ substr($r->currency_from, 0, 1) }}</div>
                        <div>
                            <div class="rt-card__pair-name">{{ $r->currency_from }} → {{ $r->currency_to }}</div>
                            <div class="rt-card__pair-sub">Por 1 {{ $r->currency_from }}</div>
                        </div>
                    </div>
                    <div class="rt-card__rate">{{ number_format($r->rate, 2, ',', '.') }} <small>KZ</small></div>
                    <div class="rt-card__foot">
                        @if($r->is_active)
                            <span class="rt-badge on">Operacional</span>
                        @else
                            <span class="rt-badge off">Pausado</span>
                        @endif
                        <a href="{{ route('admin.rates.edit', $r->id) }}" class="rt-edit">Editar</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

</div>

</x-app-layout>
