<x-app-layout>

@push('head')
<title>Canal de Staff — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.st-app { font-family:'DM Sans',sans-serif; min-height:100dvh; }
.st-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.st-back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.st-back:hover { color:white; }
.st-logo { height:28px; }
.st-title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }

.st-wrap { max-width:1000px; margin:1.5rem auto; padding:0 1.5rem; display:grid; grid-template-columns:280px 1fr; gap:1.25rem; }
@media(max-width:760px){ .st-wrap { grid-template-columns:1fr; } }

.st-flash { background:#d1f2e0;border:1px solid #a7f3d0;color:#007a34;padding:0.75rem 1rem;border-radius:10px;font-size:0.82rem;font-weight:600;margin-bottom:0.875rem;grid-column:1/-1; }
.st-flash.error { background:#fee2e2;border-color:#fca5a5;color:#991b1b; }

/* Contactos */
.st-contacts { background:white;border:1px solid #e5e5e5;border-radius:16px;overflow:hidden;align-self:start; }
.st-contacts__head { padding:0.875rem 1.125rem;border-bottom:1px solid #f0f0f0;font-family:'Syne',sans-serif;font-weight:800;font-size:0.85rem; }
.st-contact { display:flex;align-items:center;gap:0.625rem;padding:0.75rem 1.125rem;border-bottom:1px solid #f5f5f5;text-decoration:none;color:inherit;transition:background 0.15s; }
.st-contact:last-child { border-bottom:none; }
.st-contact:hover { background:#fafafa; }
.st-contact.active { background:#f0faf4; }
.st-contact__avatar { width:38px;height:38px;border-radius:50%;background:#d1f2e0;color:#007a34;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:0.8rem;flex-shrink:0; }
.st-contact.super .st-contact__avatar { background:#000;color:#22c55e; }
.st-contact__info { flex:1;min-width:0; }
.st-contact__name { font-weight:700;font-size:0.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.st-contact__role { font-size:0.62rem;color:#a3a3a3;text-transform:uppercase;letter-spacing:0.06em;font-weight:700; }
.st-contact__badge { background:#dc2626;color:white;font-size:0.6rem;font-weight:800;min-width:18px;height:18px;border-radius:10px;display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0; }
.st-contacts__empty { padding:2rem 1.125rem;text-align:center;color:#a3a3a3;font-size:0.82rem; }

/* Conversa */
.st-conv { background:white;border:1px solid #e5e5e5;border-radius:16px;display:flex;flex-direction:column;min-height:480px; }
.st-conv__head { padding:1rem 1.25rem;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:0.625rem; }
.st-conv__avatar { width:40px;height:40px;border-radius:50%;background:#d1f2e0;color:#007a34;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;flex-shrink:0; }
.st-conv__name { font-family:'Syne',sans-serif;font-weight:800;font-size:0.95rem; }
.st-conv__role { font-size:0.65rem;color:#a3a3a3;text-transform:uppercase;letter-spacing:0.06em;font-weight:700; }
.st-body { flex:1;padding:1.25rem;overflow-y:auto;background:#fcfcfd;max-height:480px; }
.st-msg { max-width:75%;margin-bottom:0.75rem;padding:0.625rem 0.875rem;border-radius:14px;font-size:0.875rem;line-height:1.5; }
.st-msg.mine { margin-left:auto;background:#065f46;color:white;border-bottom-right-radius:4px; }
.st-msg.theirs { background:#f1f5f9;color:#0f172a;border-bottom-left-radius:4px;border:1px solid #e2e8f0; }
.st-msg__time { font-size:0.6rem;opacity:0.6;margin-top:3px; }
.st-empty { text-align:center;padding:3rem 1rem;color:#94a3b8; }
.st-empty__icon { font-size:2.5rem;margin-bottom:0.5rem;opacity:0.6; }
.st-form { padding:0.875rem 1rem;border-top:1px solid #f0f0f0;display:flex;gap:0.5rem;align-items:flex-end; }
.st-form textarea { flex:1;border:2px solid #e5e5e5;border-radius:12px;padding:0.625rem 0.875rem;font-family:'DM Sans',sans-serif;font-size:0.875rem;resize:none;outline:none;max-height:120px;min-height:42px; }
.st-form textarea:focus { border-color:#065f46; }
.st-form button { width:42px;height:42px;border-radius:50%;background:#065f46;color:white;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.st-form button:hover { background:#064e3b; }
.st-placeholder { flex:1;display:flex;align-items:center;justify-content:center;color:#94a3b8;text-align:center;padding:2rem;font-size:0.9rem; }
</style>
@endpush

<div class="st-app">

<x-admin-topbar title="Canal de Staff" />

<div class="st-wrap">

    @if(session('success'))<div class="st-flash">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="st-flash error">{{ session('error') }}</div>@endif

    {{-- CONTACTOS --}}
    <aside class="st-contacts">
        <div class="st-contacts__head">Equipa</div>
        @forelse($contacts as $c)
            <a href="{{ route('admin.staff_chat.index', ['with' => $c->id]) }}"
               class="st-contact {{ $c->isSuperAdmin() ? 'super' : '' }} {{ $active && $active->id === $c->id ? 'active' : '' }}">
                <div class="st-contact__avatar">{{ strtoupper(substr($c->full_name ?? $c->email, 0, 1)) }}</div>
                <div class="st-contact__info">
                    <div class="st-contact__name">{{ $c->full_name ?? $c->email }}</div>
                    <div class="st-contact__role">{{ $c->roleLabel() }}</div>
                </div>
                @if(($unread[$c->id] ?? 0) > 0)
                    <span class="st-contact__badge">{{ $unread[$c->id] }}</span>
                @endif
            </a>
        @empty
            <div class="st-contacts__empty">Sem contactos disponíveis.</div>
        @endforelse
    </aside>

    {{-- CONVERSA --}}
    <section class="st-conv" @if($active) x-data="staffChat({{ $active->id }}, {{ $messages->max('id') ?? 0 }})" x-init="init()" @endif>
        @if($active)
            <div class="st-conv__head">
                <div class="st-conv__avatar" @if($active->isSuperAdmin()) style="background:#000;color:#22c55e;" @endif>{{ strtoupper(substr($active->full_name ?? $active->email, 0, 1)) }}</div>
                <div>
                    <div class="st-conv__name">{{ $active->full_name ?? $active->email }}</div>
                    <div class="st-conv__role">{{ $active->roleLabel() }}</div>
                </div>
            </div>

            <div class="st-body" id="st-body">
                @forelse($messages as $m)
                    <div class="st-msg {{ $m->sender_id === auth()->id() ? 'mine' : 'theirs' }}">
                        {{ $m->body }}
                        <div class="st-msg__time">{{ $m->created_at->format('d/m H:i') }}</div>
                    </div>
                @empty
                    <div class="st-empty">
                        <div class="st-empty__icon">💬</div>
                        <div>Sem mensagens ainda. Escreve a primeira abaixo.</div>
                    </div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.staff_chat.send', $active->id) }}" class="st-form">
                @csrf
                <textarea name="body" rows="1" required placeholder="Escreve uma mensagem..."
                          @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>
                <button type="submit" title="Enviar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>
        @else
            <div class="st-placeholder">
                <div>
                    <div style="font-size:2.5rem;margin-bottom:0.5rem;opacity:0.6;">👥</div>
                    Escolhe um contacto à esquerda para começar a conversar.
                </div>
            </div>
        @endif
    </section>

</div>

</div>

@if($active)
@push('scripts')
<script>
function staffChat(contactId, lastId) {
    return {
        contactId, lastId, _t: null,
        init() {
            this.scroll();
            this._t = setInterval(() => this.poll(), 5000);
        },
        destroy() { clearInterval(this._t); },
        scroll() { const b = document.getElementById('st-body'); if (b) b.scrollTop = b.scrollHeight; },
        async poll() {
            try {
                const r = await fetch(`/admin/staff-chat/${this.contactId}/poll?after=${this.lastId}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!r.ok) return;
                const { messages } = await r.json();
                if (!messages || !messages.length) return;
                const body = document.getElementById('st-body');
                const empty = body.querySelector('.st-empty'); if (empty) empty.remove();
                messages.forEach(m => {
                    const d = document.createElement('div');
                    d.className = 'st-msg ' + (m.is_mine ? 'mine' : 'theirs');
                    d.textContent = m.body;
                    const t = document.createElement('div'); t.className = 'st-msg__time'; t.textContent = m.time;
                    d.appendChild(t); body.appendChild(d);
                });
                this.lastId = messages[messages.length - 1].id;
                this.scroll();
            } catch (e) {}
        },
    };
}
</script>
@endpush
@endif

</x-app-layout>
