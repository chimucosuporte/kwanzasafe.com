<x-app-layout>

@php
    $user = auth()->user();
    $emailOk = (bool) $user->email_verified_at;

    // Avisos acionáveis derivados do estado da conta (topo da lista).
    $pending = [];
    if (! $emailOk) {
        $pending[] = ['title' => 'Confirma o teu email', 'body' => 'Introduz o código para ativar a conta.', 'href' => route('otp.email.verify')];
    } elseif (! $user->is_fully_verified) {
        $pending[] = ['title' => 'Verificação pendente', 'body' => 'Conclui o KYC para poderes transacionar.', 'href' => route('profile.edit') . '#kyc'];
    }

    // Tom/ícone por tipo de notificação.
    $meta = [
        'security'    => ['bg' => '#fffbeb', 'fg' => '#b45309'],
        'transaction' => ['bg' => '#f0faf4', 'fg' => '#007a34'],
        'recourse'    => ['bg' => '#eff6ff', 'fg' => '#1d4ed8'],
        'info'        => ['bg' => '#f5f5f5', 'fg' => '#404040'],
    ];
@endphp

@push('head')
<title>Notificações — KwanzaSafe</title>
<style>
    body { background:var(--ks-off-white); }
    .nt { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; color:var(--ks-black); }
    @media(min-width:1024px) { .nt { padding-bottom:0; } }

    .nt-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff; border-bottom:1px solid var(--ks-gray-200);
        padding:0 1.25rem; height:60px;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    .nt-back { display:inline-flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:600; color:var(--ks-gray-600); text-decoration:none; padding:0.5rem 0.625rem; border-radius:8px; transition:background 0.15s, color 0.15s; }
    .nt-back:hover { background:var(--ks-gray-100); color:var(--ks-black); }
    .nt-topbar__title { font-family:'Syne',sans-serif; font-weight:700; font-size:0.95rem; }
    .nt-topbar__logo { height:24px; width:auto; }

    .nt-main { max-width:680px; margin:0 auto; padding:1.25rem; }
    @media(min-width:768px) { .nt-main { padding:2rem 1.5rem; } }

    .nt-flash { padding:0.875rem 1rem; border-radius:12px; font-size:0.875rem; margin-bottom:1.25rem; background:#fff; border:1px solid var(--ks-gray-200); border-left:3px solid var(--ks-green); color:var(--ks-green-dark); }

    .nt-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .nt-head__title { font-family:'Syne',sans-serif; font-weight:700; font-size:1.1rem; }
    .nt-head__count { font-size:0.8rem; color:var(--ks-gray-500); font-weight:600; }
    .nt-markall { display:inline-flex; align-items:center; gap:0.4rem; background:none; border:1px solid var(--ks-gray-300); color:var(--ks-gray-700); padding:0.45rem 0.75rem; border-radius:8px; font-family:'DM Sans',sans-serif; font-size:0.8rem; font-weight:600; cursor:pointer; transition:background 0.15s; }
    .nt-markall:hover { background:var(--ks-gray-100); }

    .nt-list { display:flex; flex-direction:column; gap:0.625rem; }

    /* cada notificação é um form-botão de largura total */
    .nt-item { display:block; margin:0; }
    .nt-item__btn {
        width:100%; text-align:left; cursor:pointer;
        display:flex; align-items:center; gap:0.875rem;
        background:#fff; border:1px solid var(--ks-gray-200); border-radius:12px;
        padding:1rem; font-family:'DM Sans',sans-serif;
        transition:border-color 0.15s, background 0.15s;
    }
    .nt-item__btn:hover { border-color:var(--ks-gray-300); background:var(--ks-gray-50); }
    .nt-item--unread .nt-item__btn { background:#fcfefc; border-color:#cdeada; }
    .nt-icon { width:40px; height:40px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
    .nt-texts { flex:1; min-width:0; }
    .nt-title { font-family:'DM Sans',sans-serif; font-weight:700; font-size:0.9rem; color:var(--ks-black); }
    .nt-body { font-size:0.8rem; color:var(--ks-gray-600); margin-top:2px; line-height:1.4; }
    .nt-time { font-size:0.72rem; color:var(--ks-gray-400); margin-top:4px; }
    .nt-dot { width:9px; height:9px; border-radius:50%; background:var(--ks-green); flex-shrink:0; }

    .nt-pending .nt-item__btn { background:#fffbeb; border-color:#fde68a; }

    .nt-empty { text-align:center; padding:3rem 1.5rem; color:var(--ks-gray-500); }
    .nt-empty__icon { width:56px; height:56px; border-radius:50%; background:var(--ks-gray-100); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; color:var(--ks-gray-400); }
    .nt-empty__title { font-family:'Syne',sans-serif; font-weight:700; font-size:1rem; color:var(--ks-black); }
    .nt-empty__sub { font-size:0.85rem; margin-top:0.375rem; }
</style>
@endpush

<div class="nt">

    <div class="nt-topbar">
        <a href="{{ route('dashboard') }}" class="nt-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Voltar ao Painel
        </a>
        <span class="nt-topbar__title">Notificações</span>
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="nt-topbar__logo">
    </div>

    <main class="nt-main">

        @if(session('success'))
            <div class="nt-flash" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3500)">{{ session('success') }}</div>
        @endif

        <div class="nt-head">
            <div>
                <div class="nt-head__title">As tuas notificações</div>
                <div class="nt-head__count">{{ $unread }} por ler</div>
            </div>
            @if($unread > 0)
                <form method="POST" action="{{ route('notifications.read_all') }}">
                    @csrf
                    <button type="submit" class="nt-markall">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M1.5 12.5l4 4L14 8m4.5 4.5l-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Marcar todas como lidas
                    </button>
                </form>
            @endif
        </div>

        @if(count($pending) === 0 && $notifications->isEmpty())
            <div class="nt-empty">
                <div class="nt-empty__icon">
                    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="nt-empty__title">Sem notificações</div>
                <div class="nt-empty__sub">Avisos de acesso, transações e recursos aparecem aqui.</div>
            </div>
        @else
            <div class="nt-list">

                {{-- Avisos acionáveis (email/KYC) --}}
                @foreach($pending as $p)
                    <div class="nt-item nt-pending">
                        <a href="{{ $p['href'] }}" class="nt-item__btn">
                            <div class="nt-icon" style="background:#fef3c7; color:#b45309;">
                                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
                            </div>
                            <div class="nt-texts">
                                <div class="nt-title">{{ $p['title'] }}</div>
                                <div class="nt-body">{{ $p['body'] }}</div>
                            </div>
                            <svg width="18" height="18" fill="none" stroke="#a3a3a3" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                @endforeach

                {{-- Feed real --}}
                @foreach($notifications as $n)
                    @php $m = $meta[$n->type] ?? $meta['info']; @endphp
                    <div class="nt-item {{ $n->is_read ? '' : 'nt-item--unread' }}">
                        <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                            @csrf
                            <button type="submit" class="nt-item__btn">
                                <div class="nt-icon" style="background:{{ $m['bg'] }}; color:{{ $m['fg'] }};">
                                    @if($n->type === 'security')
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @elseif($n->type === 'transaction')
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 10h13l-3-3m3 7H7l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @elseif($n->type === 'recourse')
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @endif
                                </div>
                                <div class="nt-texts">
                                    <div class="nt-title">{{ $n->title }}</div>
                                    <div class="nt-body">{{ $n->body }}</div>
                                    @if($n->created_at)
                                        <div class="nt-time">{{ $n->created_at->diffForHumans() }}</div>
                                    @endif
                                </div>
                                @if(!$n->is_read)
                                    <span class="nt-dot"></span>
                                @endif
                            </button>
                        </form>
                    </div>
                @endforeach

            </div>
        @endif

    </main>
</div>

</x-app-layout>
