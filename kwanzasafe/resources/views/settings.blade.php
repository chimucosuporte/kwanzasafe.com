<x-app-layout>

@php
    $user = auth()->user();
    $appVersion = '1.0.0';
@endphp

@push('head')
<title>Definições — KwanzaSafe</title>
<style>
    body { background:var(--ks-off-white); }
    .st { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; color:var(--ks-black); }
    @media(min-width:1024px) { .st { padding-bottom:0; } }

    .st-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff; border-bottom:1px solid var(--ks-gray-200);
        padding:0 1.25rem; height:60px;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    .st-back { display:inline-flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:600; color:var(--ks-gray-600); text-decoration:none; padding:0.5rem 0.625rem; border-radius:8px; transition:background 0.15s, color 0.15s; }
    .st-back:hover { background:var(--ks-gray-100); color:var(--ks-black); }
    .st-topbar__title { font-family:'Syne',sans-serif; font-weight:700; font-size:0.95rem; }
    .st-topbar__logo { height:24px; width:auto; }

    .st-main { max-width:680px; margin:0 auto; padding:1.25rem; }
    @media(min-width:768px) { .st-main { padding:2rem 1.5rem; } }

    .st-section { font-family:'DM Sans',sans-serif; font-weight:700; font-size:0.72rem; letter-spacing:0.04em; text-transform:uppercase; color:var(--ks-gray-500); margin:1.5rem 0 0.5rem 0.25rem; }
    .st-section:first-child { margin-top:0; }

    .st-group { background:#fff; border:1px solid var(--ks-gray-200); border-radius:14px; overflow:hidden; }
    .st-row { display:flex; align-items:center; gap:0.875rem; padding:0.95rem 1.1rem; text-decoration:none; color:inherit; width:100%; background:none; border:none; cursor:pointer; text-align:left; font-family:'DM Sans',sans-serif; transition:background 0.12s; }
    .st-row + .st-row { border-top:1px solid var(--ks-gray-100); }
    a.st-row:hover, button.st-row:hover { background:var(--ks-gray-50); }
    .st-row__icon { width:36px; height:36px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:var(--ks-green-pale); color:var(--ks-green-dark); }
    .st-row__icon.danger { background:#fef2f2; color:#dc2626; }
    .st-row__icon.neutral { background:var(--ks-gray-100); color:var(--ks-gray-600); }
    .st-row__body { flex:1; min-width:0; }
    .st-row__label { font-weight:600; font-size:0.9rem; color:var(--ks-black); }
    .st-row.danger .st-row__label { color:#dc2626; }
    .st-row__desc { font-size:0.78rem; color:var(--ks-gray-500); margin-top:1px; line-height:1.35; }
    .st-row__value { font-size:0.82rem; color:var(--ks-gray-500); font-weight:600; }
    .st-row__chev { color:var(--ks-gray-300); flex-shrink:0; }

    .st-footer { text-align:center; font-size:0.75rem; color:var(--ks-gray-400); margin-top:2rem; }
</style>
@endpush

@php
    // Ícone chevron reutilizável
    $chev = '<svg class="st-row__chev" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

<div class="st">

    <div class="st-topbar">
        <a href="{{ route('dashboard') }}" class="st-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Voltar ao Painel
        </a>
        <span class="st-topbar__title">Definições</span>
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="st-topbar__logo">
    </div>

    <main class="st-main">

        {{-- CONTA --}}
        <div class="st-section">Conta</div>
        <div class="st-group">
            <a href="{{ route('profile.edit') }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Editar perfil</span>
                    <span class="st-row__desc">Nome, foto e dados da conta.</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="{{ route('profile.edit') }}#kyc" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Verificação de identidade (KYC)</span>
                    <span class="st-row__desc">{{ $user->is_fully_verified ? 'Conta verificada.' : 'Conclui a verificação para transacionar.' }}</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="{{ route('dashboard', ['tab' => 'iban']) }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14h2" stroke-linecap="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Beneficiários e carteiras</span>
                    <span class="st-row__desc">Contas e carteiras onde recebes.</span>
                </span>
                {!! $chev !!}
            </a>
        </div>

        {{-- SEGURANÇA --}}
        <div class="st-section">Segurança</div>
        <div class="st-group">
            <a href="{{ route('profile.edit') }}#email-change" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Alterar email</span>
                    <span class="st-row__desc">Com confirmação por código (atual + novo).</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="{{ route('profile.edit') }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4" stroke-linecap="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Alterar palavra-passe</span>
                    <span class="st-row__desc">Mantém a conta segura.</span>
                </span>
                {!! $chev !!}
            </a>
        </div>

        {{-- PREFERÊNCIAS --}}
        <div class="st-section">Preferências</div>
        <div class="st-group">
            <a href="{{ route('notifications.index') }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Notificações</span>
                    <span class="st-row__desc">Vê os teus avisos e lembretes.</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="{{ route('invite') }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Convidar amigos</span>
                    <span class="st-row__desc">Partilha a KwanzaSafe com quem envia para Angola.</span>
                </span>
                {!! $chev !!}
            </a>
            <div class="st-row" style="cursor:default;">
                <span class="st-row__icon neutral">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18M12 3a15 15 0 000 18" stroke-linecap="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Idioma</span>
                </span>
                <span class="st-row__value">Português (PT)</span>
            </div>
        </div>

        {{-- SUPORTE --}}
        <div class="st-section">Suporte</div>
        <div class="st-group">
            <a href="https://wa.me/5511933579009?text=Ol%C3%A1%2C+preciso+de+ajuda+com+o+KwanzaSafe." target="_blank" rel="noopener" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">WhatsApp</span>
                    <span class="st-row__desc">+55 11 93357-9009 · resposta rápida</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="mailto:geral@kwanzasafe.com" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Email</span>
                    <span class="st-row__desc">geral@kwanzasafe.com</span>
                </span>
                {!! $chev !!}
            </a>
            <a href="{{ url('/#faq') }}" class="st-row">
                <span class="st-row__icon">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 015 0c0 1.5-2.5 2-2.5 3.5M12 17h.01" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Perguntas frequentes</span>
                    <span class="st-row__desc">Respostas às dúvidas mais comuns.</span>
                </span>
                {!! $chev !!}
            </a>
        </div>

        {{-- SESSÃO --}}
        <div class="st-section">Sessão</div>
        <div class="st-group">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="st-row">
                    <span class="st-row__icon neutral">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <span class="st-row__body">
                        <span class="st-row__label">Terminar sessão</span>
                    </span>
                </button>
            </form>
            <a href="{{ route('profile.edit') }}#delete" class="st-row danger">
                <span class="st-row__icon danger">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span class="st-row__body">
                    <span class="st-row__label">Eliminar conta</span>
                    <span class="st-row__desc">Remove permanentemente a tua conta e dados.</span>
                </span>
                {!! $chev !!}
            </a>
        </div>

        <div class="st-footer">KwanzaSafe · versão {{ $appVersion }} · do mundo para Angola</div>

    </main>
</div>

</x-app-layout>
