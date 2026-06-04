<x-app-layout>

@php
    $user = auth()->user();
    $firstName = $user->full_name ? explode(' ', trim($user->full_name))[0] : explode('@', $user->email)[0];

    $checks = [
        'email'    => (bool) $user->email_verified_at,
        'phone'    => (bool) $user->phone_verified_at,
        'identity' => (bool) $user->identity_verified_at,
        'data'     => (bool) $user->data_verified,
    ];
    $verifiedCount = count(array_filter($checks));
@endphp

@push('head')
<title>Definições da Conta — KwanzaSafe</title>
<style>
    body { background:var(--ks-off-white); }
    .pf { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; }
    @media(min-width:1024px) { .pf { padding-bottom:0; } }
    .pf-font-display { font-family:'Syne',sans-serif; }

    /* ===== TOPBAR ===== */
    .pf-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff; border-bottom:1px solid var(--ks-gray-200);
        padding:0 1.25rem; height:60px;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    .pf-back {
        display:inline-flex; align-items:center; gap:0.5rem;
        font-size:0.825rem; font-weight:600; color:var(--ks-gray-600);
        text-decoration:none; padding:0.5rem 0.75rem; border-radius:8px;
        transition:all 0.15s;
    }
    .pf-back:hover { background:var(--ks-gray-100); color:var(--ks-black); }
    .pf-topbar__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.9rem; color:var(--ks-black); }
    .pf-topbar__logo { height:26px; width:auto; }

    /* ===== LAYOUT ===== */
    .pf-main { max-width:720px; margin:0 auto; padding:1.25rem; }
    @media(min-width:768px) { .pf-main { padding:2rem 1.5rem; } }

    /* ===== FLASH ===== */
    .pf-flash {
        padding:0.875rem 1.125rem; border-radius:12px;
        font-size:0.85rem; font-weight:500; margin-bottom:1.25rem;
        display:flex; align-items:center; gap:0.5rem;
    }
    .pf-flash.success { background:var(--ks-green-light); border:1px solid #a7f3d0; color:var(--ks-green-dark); }

    /* ===== HERO ===== */
    .pf-hero {
        background:linear-gradient(135deg,#000 0%,#171717 100%);
        border-radius:20px; padding:1.75rem 1.5rem; color:#fff;
        text-align:center; margin-bottom:1.5rem;
        position:relative; overflow:hidden;
    }
    .pf-hero::before {
        content:''; position:absolute; top:-40px; right:-40px;
        width:160px; height:160px;
        background:radial-gradient(circle,rgba(0,157,68,0.25),transparent);
    }
    .pf-hero__avatar {
        width:84px; height:84px; border-radius:50%;
        background:var(--ks-green); color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.75rem;
        margin:0 auto 0.75rem; overflow:hidden;
        border:3px solid rgba(255,255,255,0.2); position:relative;
    }
    .pf-hero__avatar img { width:100%; height:100%; object-fit:cover; }
    .pf-hero__name { font-family:'Syne',sans-serif; font-weight:800; font-size:1.2rem; position:relative; }
    .pf-hero__email { font-size:0.8rem; opacity:0.7; margin-top:2px; position:relative; }
    .pf-hero__badge {
        display:inline-flex; align-items:center; gap:0.375rem;
        margin-top:0.75rem; padding:0.375rem 0.875rem; border-radius:999px;
        font-size:0.7rem; font-weight:700; position:relative;
    }
    .pf-hero__badge.full { background:rgba(0,157,68,0.2); color:#22c55e; }
    .pf-hero__badge.partial { background:rgba(245,158,11,0.2); color:#fbbf24; }

    /* ===== CARD ===== */
    .pf-card {
        background:#fff; border:1px solid var(--ks-gray-200);
        border-radius:16px; padding:1.5rem;
        margin-bottom:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,0.03);
    }
    .pf-card.danger { border-color:#fca5a5; }
    .pf-card__head { margin-bottom:1.25rem; }
    .pf-card__title {
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.05rem;
        color:var(--ks-black); display:flex; align-items:center; gap:0.5rem;
    }
    .pf-card__title svg { color:var(--ks-green); }
    .pf-card.danger .pf-card__title { color:var(--ks-danger); }
    .pf-card.danger .pf-card__title svg { color:var(--ks-danger); }
    .pf-card__sub { font-size:0.825rem; color:var(--ks-gray-500); margin-top:0.25rem; line-height:1.5; }

    /* ===== FORM ===== */
    .pf-field { margin-bottom:1rem; }
    .pf-label {
        display:block; font-size:0.7rem; font-weight:700;
        color:var(--ks-gray-700); text-transform:uppercase;
        letter-spacing:0.08em; margin-bottom:0.375rem;
    }
    .pf-input {
        width:100%; padding:12px 14px;
        border:2px solid var(--ks-gray-200); border-radius:12px;
        font-family:'DM Sans',sans-serif; font-size:0.95rem; color:var(--ks-black);
        background:var(--ks-gray-50); outline:none; transition:all 0.2s;
        -webkit-appearance:none;
    }
    .pf-input:focus { border-color:var(--ks-green); background:#fff; box-shadow:0 0 0 4px rgba(0,157,68,0.1); }
    .pf-input::placeholder { color:var(--ks-gray-300); }
    .pf-input.error { border-color:var(--ks-danger); background:var(--ks-danger-light); }
    .pf-err { font-size:0.75rem; color:var(--ks-danger); margin-top:0.375rem; font-weight:500; }

    /* ===== BUTTONS ===== */
    .pf-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
        background:var(--ks-green); color:#fff; border:none;
        padding:0.75rem 1.5rem; border-radius:12px;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem;
        text-transform:uppercase; letter-spacing:0.05em; cursor:pointer;
        transition:all 0.2s; box-shadow:0 4px 12px rgba(0,157,68,0.2);
    }
    .pf-btn:hover { background:var(--ks-green-dark); transform:translateY(-1px); }
    .pf-btn.dark { background:#000; box-shadow:0 4px 12px rgba(0,0,0,0.15); }
    .pf-btn.dark:hover { background:var(--ks-green); }
    .pf-btn.danger { background:var(--ks-danger); box-shadow:0 4px 12px rgba(220,38,38,0.2); }
    .pf-btn.danger:hover { background:#b91c1c; }
    .pf-btn.ghost { background:var(--ks-gray-100); color:var(--ks-gray-600); box-shadow:none; }
    .pf-btn.ghost:hover { background:var(--ks-gray-200); color:var(--ks-black); transform:none; }

    .pf-saved { font-size:0.8rem; color:var(--ks-green-dark); font-weight:600; display:inline-flex; align-items:center; gap:0.375rem; }

    /* ===== STATUS PILL ===== */
    .pf-status {
        display:flex; align-items:center; gap:0.5rem;
        padding:0.75rem 1rem; border-radius:12px;
        font-size:0.825rem; font-weight:600; margin-bottom:1rem;
    }
    .pf-status.ok { background:var(--ks-green-light); border:1px solid #a7f3d0; color:var(--ks-green-dark); }
    .pf-status.warn { background:var(--ks-warn-light); border:1px solid #fde68a; color:#92400e; }
    .pf-status svg { flex-shrink:0; }

    .pf-inline-form { display:flex; gap:0.5rem; flex-wrap:wrap; }
    .pf-inline-form .pf-input { flex:1; min-width:180px; }

    .pf-actions { display:flex; align-items:center; gap:1rem; flex-wrap:wrap; margin-top:0.5rem; }

    [x-cloak] { display:none !important; }
</style>
@endpush

<div class="pf">

    {{-- TOPBAR --}}
    <div class="pf-topbar">
        <a href="{{ route('dashboard') }}" class="pf-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Voltar ao Painel
        </a>
        <span class="pf-topbar__title">Definições da Conta</span>
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="pf-topbar__logo">
    </div>

    <main class="pf-main">

        @if(session('status') === 'profile-updated' || session('success'))
            <div class="pf-flash success" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,3000)">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') ?? 'Perfil actualizado com sucesso.' }}
            </div>
        @endif

        {{-- HERO --}}
        <div class="pf-hero">
            <div class="pf-hero__avatar">
                @if(ks_file($user->profile_photo_path))
                    <img src="{{ ks_file($user->profile_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($firstName, 0, 1)) }}
                @endif
            </div>
            <div class="pf-hero__name">{{ $user->full_name ?? $firstName }}</div>
            <div class="pf-hero__email">{{ $user->email }}</div>
            @if($user->is_fully_verified)
                <span class="pf-hero__badge full">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Conta Verificada
                </span>
            @else
                <span class="pf-hero__badge partial">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
                    Verificação {{ $verifiedCount }}/4
                </span>
            @endif
        </div>

        {{-- INFORMAÇÃO DE PERFIL --}}
        <div class="pf-card">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- VERIFICAÇÃO DE TELEFONE --}}
        <div class="pf-card" id="kyc-phone">
            <div class="pf-card__head">
                <div class="pf-card__title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Verificação de Telefone
                </div>
                <p class="pf-card__sub">O teu número é necessário para completar o KYC. Ao submeteres, recebes um código de confirmação no teu email.</p>
            </div>

            @if($user->phone_verified_at)
                <div class="pf-status ok">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Telefone <strong>{{ $user->phone_number }}</strong> verificado em {{ $user->phone_verified_at->format('d/m/Y') }}.</span>
                </div>
            @endif

            {{-- Número --}}
            <form method="POST" action="{{ route('verify.phone') }}" style="margin-bottom:0.875rem;">
                @csrf
                <div class="pf-inline-form">
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone_number) }}"
                           placeholder="+351 912 345 678"
                           class="pf-input @error('phone') error @enderror">
                    <button type="submit" class="pf-btn">
                        {{ $user->phone_number ? 'Reenviar Código' : 'Enviar Código' }}
                    </button>
                </div>
                @error('phone') <div class="pf-err">{{ $message }}</div> @enderror
            </form>

            {{-- OTP --}}
            @if($user->phone_number && !$user->phone_verified_at)
                <form method="POST" action="{{ route('verify.phone.submit') }}">
                    @csrf
                    <p class="pf-card__sub" style="margin-bottom:0.5rem;">Introduz o código de 6 dígitos enviado para o teu email:</p>
                    <div class="pf-inline-form">
                        <input type="text" name="phone_otp" maxlength="6" inputmode="numeric"
                               placeholder="• • • • • •"
                               class="pf-input @error('phone_otp') error @enderror"
                               style="max-width:180px; text-align:center; letter-spacing:0.3em; font-family:'JetBrains Mono',monospace; flex:0;">
                        <button type="submit" class="pf-btn dark">Confirmar</button>
                    </div>
                    @error('phone_otp') <div class="pf-err">{{ $message }}</div> @enderror
                </form>
            @endif
        </div>

        {{-- ALTERAR PASSWORD --}}
        <div class="pf-card">
            @include('profile.partials.update-password-form')
        </div>

        {{-- ELIMINAR CONTA --}}
        <div class="pf-card danger">
            @include('profile.partials.delete-user-form')
        </div>

    </main>
</div>

</x-app-layout>
