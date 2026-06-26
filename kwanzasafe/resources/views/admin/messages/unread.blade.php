<x-app-layout>

@push('head')
<title>Mensagens Por Ler — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.msg-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.msg-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.msg-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.msg-back:hover { color:white; }
.msg-logo { height:28px; }
.msg-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.msg-container { max-width:900px; margin:0 auto; padding:1.5rem; }
.msg-intro { background:linear-gradient(135deg,#dc2626,#991b1b); color:white; border-radius:14px; padding:1.5rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; }
.msg-intro__icon { width:48px; height:48px; border-radius:12px; background:rgba(255,255,255,0.15); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.msg-intro__title { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; }
.msg-intro__sub { font-size:0.85rem; opacity:0.9; margin-top:2px; }

.msg-card { background:white; border-radius:14px; border:1px solid #e5e5e5; padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; margin-bottom:0.625rem; text-decoration:none; color:inherit; transition:all 0.2s; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.msg-card:hover { transform:translateX(3px); border-color:#dc2626; box-shadow:0 4px 16px rgba(220,38,38,0.1); }

.msg-card__avatar { width:44px; height:44px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:1rem; overflow:hidden; flex-shrink:0; position:relative; }
.msg-card__avatar img { width:100%; height:100%; object-fit:cover; }
.msg-card__badge { position:absolute; top:-4px; right:-4px; min-width:20px; height:20px; background:#dc2626; color:white; font-size:0.65rem; font-weight:800; border-radius:20px; padding:0 6px; display:flex; align-items:center; justify-content:center; border:2px solid white; }

.msg-card__info { flex:1; min-width:0; }
.msg-card__name { font-family:'Syne',sans-serif; font-size:0.95rem; font-weight:800; }
.msg-card__sub { font-size:0.75rem; color:#737373; margin-top:1px; display:flex; gap:0.5rem; flex-wrap:wrap; }
.msg-card__ref { font-family:'JetBrains Mono',monospace; font-size:0.7rem; background:#f5f5f5; padding:1px 6px; border-radius:5px; color:#525252; }
.msg-card__time { font-size:0.7rem; color:#dc2626; font-weight:700; margin-top:3px; }

.msg-card__cta { background:#000; color:white; padding:0.5rem 0.875rem; border-radius:10px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; flex-shrink:0; }

.msg-empty { background:white; border-radius:14px; border:2px dashed #d4d4d4; padding:4rem 1.5rem; text-align:center; }
.msg-empty__icon { font-size:3rem; margin-bottom:0.75rem; }
.msg-empty__text { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:#737373; }
.msg-empty__sub { font-size:0.85rem; color:#a3a3a3; margin-top:0.25rem; }

.msg-pagination { display:flex; justify-content:center; align-items:center; gap:0.5rem; padding:1.25rem; }
.msg-pagination a, .msg-pagination span { padding:0.4rem 0.75rem; border-radius:6px; font-size:0.75rem; font-weight:600; text-decoration:none; color:#737373; }
.msg-pagination a:hover { background:#fafafa; color:#000; }
.msg-pagination .active span { background:#000; color:white; }

@media(max-width:600px) {
    .msg-card__cta { display:none; }
}
</style>
@endpush

<div class="msg-app">

<x-admin-topbar title="Mensagens Por Ler" />

<div class="msg-container">

    @if($transactions->isEmpty())
        <div class="msg-empty">
            <div class="msg-empty__icon">✅</div>
            <div class="msg-empty__text">Nenhuma mensagem por ler</div>
            <div class="msg-empty__sub">Tudo em dia! Os clientes não têm mensagens pendentes.</div>
        </div>
    @else
        <div class="msg-intro">
            <div class="msg-intro__icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <div class="msg-intro__title">{{ $transactions->total() }} conversa{{ $transactions->total() !== 1 ? 's' : '' }} aguarda{{ $transactions->total() !== 1 ? 'm' : '' }} resposta</div>
                <div class="msg-intro__sub">Responde aos clientes o mais rápido possível para manter alta qualidade de serviço.</div>
            </div>
        </div>

        @foreach($transactions as $tx)
            <a href="{{ route('admin.transaction.show', $tx->id) }}" class="msg-card">
                <div class="msg-card__avatar">
                    @if(ks_file(optional($tx->user)->profile_photo_path))
                        <img loading="lazy" decoding="async" src="{{ ks_file($tx->user->display_photo_path) }}" alt="">
                    @else
                        {{ strtoupper(substr(optional($tx->user)->full_name ?? optional($tx->user)->email ?? '?', 0, 1)) }}
                    @endif
                    <span class="msg-card__badge">{{ $tx->unread_count }}</span>
                </div>
                <div class="msg-card__info">
                    <div class="msg-card__name">{{ optional($tx->user)->full_name ?? optional($tx->user)->email ?? '—' }}</div>
                    <div class="msg-card__sub">
                        <span class="msg-card__ref">#{{ $tx->reference_id }}</span>
                        <span>{{ number_format($tx->amount_sent, 2, ',', '.') }} {{ $tx->currency_from }}</span>
                    </div>
                    <div class="msg-card__time">⚠️ {{ $tx->updated_at->diffForHumans() }}</div>
                </div>
                <span class="msg-card__cta">Responder →</span>
            </a>
        @endforeach

        <div class="msg-pagination">{{ $transactions->links() }}</div>
    @endif
</div>

</div>

</x-app-layout>
