<x-app-layout>

@push('head')
<title>Recursos — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.rc-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.rc-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.rc-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.rc-back:hover { color:white; }
.rc-logo { height:28px; }
.rc-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.rc-container { max-width:900px; margin:0 auto; padding:1.5rem; }

.rc-flash { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; font-weight:600; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }
.rc-head h1 { font-family:'Syne',sans-serif; font-weight:800; font-size:1.35rem; margin:0; }
.rc-head p { font-size:0.8rem; color:#737373; margin:2px 0 1.25rem; }

.rc-card { background:white; border:1px solid #e5e5e5; border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,0.03); margin-bottom:1.25rem; overflow:hidden; }
.rc-card__head { padding:1rem 1.25rem; border-bottom:1px solid #f0f0f0; display:flex; align-items:center; justify-content:space-between; gap:0.75rem; flex-wrap:wrap; }
.rc-client { font-family:'Syne',sans-serif; font-weight:800; font-size:0.9rem; }
.rc-ref { font-family:'JetBrains Mono',monospace; font-size:0.7rem; color:#737373; }
.rc-badge { font-size:0.6rem; font-weight:800; padding:3px 9px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.rc-badge.open { background:#fef3c7; color:#92400e; }
.rc-badge.review { background:#dbeafe; color:#1e40af; }
.rc-badge.resolved { background:#d1f2e0; color:#007a34; }
.rc-badge.rejected { background:#fee2e2; color:#991b1b; }
.rc-body { padding:1.25rem; }
.rc-reason { background:#f8fafc; border-left:3px solid #6d28d9; padding:0.75rem 1rem; border-radius:0 10px 10px 0; font-size:0.85rem; color:#334155; margin-bottom:1rem; }

.rc-thread { display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem; max-height:280px; overflow-y:auto; }
.rc-msg { padding:0.5rem 0.75rem; border-radius:10px; font-size:0.82rem; max-width:85%; }
.rc-msg.client { background:#f1f5f9; align-self:flex-start; }
.rc-msg.super  { background:#ede9fe; align-self:flex-end; }
.rc-msg.sys    { background:#fffbeb; align-self:center; font-weight:600; color:#92400e; text-align:center; }
.rc-msg__who { font-size:0.6rem; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; opacity:0.6; margin-bottom:2px; }
.rc-msg__time { font-size:0.6rem; opacity:0.5; margin-top:2px; }

.rc-input { width:100%; padding:0.625rem 0.875rem; border:2px solid #e5e5e5; border-radius:10px; font-family:'DM Sans',sans-serif; font-size:0.85rem; resize:vertical; outline:none; }
.rc-input:focus { border-color:#6d28d9; }
.rc-actions { display:flex; gap:0.5rem; margin-top:0.625rem; flex-wrap:wrap; }
.rc-btn { border:none; padding:0.6rem 1rem; border-radius:10px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.72rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; }
.rc-btn.reply { background:#6d28d9; color:white; }
.rc-btn.resolve { background:#009d44; color:white; }
.rc-btn.reject { background:#fee2e2; color:#991b1b; }
.rc-empty { padding:3rem 1.5rem; text-align:center; color:#a3a3a3; }
.rc-empty__icon { font-size:2.5rem; margin-bottom:0.5rem; }
.rc-resolved-row { display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding:0.75rem 1.25rem; border-bottom:1px solid #f5f5f5; font-size:0.82rem; }
.rc-resolved-row:last-child { border-bottom:none; }
[x-cloak] { display:none !important; }
</style>
@endpush

<div class="rc-app">

<x-admin-topbar title="Recursos" meta="{{ count($active) }} activo(s)" />

<div class="rc-container">

    @if(session('success'))
        <div class="rc-flash">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="rc-head">
        <h1>Recursos</h1>
        <p>Casos escalados pelos clientes para arbitragem. Responde, resolve ou indefere cada recurso.</p>
    </div>

    @forelse($active as $r)
        <div class="rc-card">
            <div class="rc-card__head">
                <div>
                    <div class="rc-client">{{ $r->openedBy?->full_name ?? $r->transaction->user?->full_name ?? 'Cliente' }}</div>
                    <div class="rc-ref">#{{ $r->transaction->reference_id }} · aberto {{ $r->created_at->diffForHumans() }}</div>
                </div>
                <span class="rc-badge {{ $r->status === 'open' ? 'open' : 'review' }}">{{ $r->status === 'open' ? 'Aberto' : 'Em análise' }}</span>
            </div>
            <div class="rc-body">
                <div class="rc-reason"><strong>Motivo:</strong> {{ $r->reason }}</div>

                @php $thread = $threads[$r->transaction_id] ?? collect(); @endphp
                @if($thread->count())
                    <div class="rc-thread">
                        @foreach($thread as $m)
                            @php
                                $who = is_null($m->sender_id) ? 'sys' : ($m->sender && $m->sender->isSuperAdmin() ? 'super' : 'client');
                            @endphp
                            <div class="rc-msg {{ $who }}">
                                @if($who !== 'sys')<div class="rc-msg__who">{{ $who === 'super' ? 'Super-Admin' : 'Cliente' }}</div>@endif
                                {{ $m->message_text }}
                                <div class="rc-msg__time">{{ $m->created_at->format('d/m H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Responder --}}
                <form method="POST" action="{{ route('admin.recourses.reply', $r->id) }}" style="margin-bottom:0.75rem;">
                    @csrf
                    <textarea name="message_text" rows="2" required placeholder="Responder ao cliente..." class="rc-input"></textarea>
                    <div class="rc-actions">
                        <button type="submit" class="rc-btn reply">Responder</button>
                    </div>
                </form>

                {{-- Resolver / Indeferir --}}
                <form method="POST">
                    @csrf
                    <textarea name="resolution" rows="2" required placeholder="Decisão / resolução final (visível ao cliente)..." class="rc-input"></textarea>
                    <div class="rc-actions">
                        <button type="submit" class="rc-btn resolve" formaction="{{ route('admin.recourses.resolve', $r->id) }}">✅ Resolver</button>
                        <button type="submit" class="rc-btn reject" formaction="{{ route('admin.recourses.reject', $r->id) }}">❌ Indeferir</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div class="rc-card">
            <div class="rc-empty">
                <div class="rc-empty__icon">⚖️</div>
                <div style="font-family:'Syne',sans-serif;font-weight:800;color:#525252;">Sem recursos activos</div>
                <div style="font-size:0.8rem;margin-top:3px;">Quando um cliente abrir um recurso, ele aparece aqui.</div>
            </div>
        </div>
    @endforelse

    @if(count($resolved))
        <div class="rc-card">
            <div class="rc-card__head"><span style="font-family:'Syne',sans-serif;font-weight:800;font-size:0.85rem;">Resolvidos recentemente</span></div>
            @foreach($resolved as $r)
                <div class="rc-resolved-row">
                    <div>
                        <span class="rc-ref">#{{ $r->transaction->reference_id }}</span>
                        <span style="color:#525252;"> · {{ $r->transaction->user?->full_name ?? '—' }}</span>
                    </div>
                    <span class="rc-badge {{ $r->status === 'resolved' ? 'resolved' : 'rejected' }}">{{ $r->status === 'resolved' ? 'Resolvido' : 'Indeferido' }}</span>
                </div>
            @endforeach
        </div>
    @endif

</div>

</div>

</x-app-layout>
