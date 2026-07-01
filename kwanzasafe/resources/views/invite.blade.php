<x-app-layout>

@php
    $user = auth()->user();
    $firstName = $user->full_name ? explode(' ', trim($user->full_name))[0] : null;
    $site = 'https://kwanzasafe.com';
    $shareMessage = ($firstName ? "$firstName recomenda a KwanzaSafe 💚" : 'Conhece a KwanzaSafe 💚')
        . "\n\nConverte Euros, Reais e USDC para Kwanzas com rapidez e total confiança — do mundo para Angola."
        . "\n\nJunta-te aqui: $site";
@endphp

@push('head')
<title>Convidar amigos — KwanzaSafe</title>
<style>
    body { background:var(--ks-off-white); }
    .iv { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; color:var(--ks-black); }
    @media(min-width:1024px) { .iv { padding-bottom:0; } }

    .iv-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff; border-bottom:1px solid var(--ks-gray-200);
        padding:0 1.25rem; height:60px;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    .iv-back { display:inline-flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:600; color:var(--ks-gray-600); text-decoration:none; padding:0.5rem 0.625rem; border-radius:8px; transition:background 0.15s, color 0.15s; }
    .iv-back:hover { background:var(--ks-gray-100); color:var(--ks-black); }
    .iv-topbar__title { font-family:'Syne',sans-serif; font-weight:700; font-size:0.95rem; }
    .iv-topbar__logo { height:24px; width:auto; }

    .iv-main { max-width:560px; margin:0 auto; padding:2rem 1.25rem; text-align:center; }

    .iv-icon { width:72px; height:72px; border-radius:50%; background:var(--ks-green-pale); color:var(--ks-green-dark); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; }
    .iv-title { font-family:'Syne',sans-serif; font-weight:700; font-size:1.4rem; color:var(--ks-black); }
    .iv-text { font-size:0.9rem; color:var(--ks-gray-600); line-height:1.6; margin:0.5rem auto 1.75rem; max-width:38ch; }

    .iv-linkcard { background:#fff; border:1px solid var(--ks-gray-200); border-radius:14px; padding:1.1rem 1.25rem; text-align:left; display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem; }
    .iv-linkcard__body { flex:1; min-width:0; }
    .iv-linkcard__label { font-size:0.7rem; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:var(--ks-gray-500); }
    .iv-linkcard__url { font-family:'DM Sans',sans-serif; font-weight:700; font-size:0.95rem; color:var(--ks-green-dark); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .iv-copy { flex-shrink:0; display:inline-flex; align-items:center; gap:0.4rem; border:1px solid var(--ks-gray-300); background:#fff; color:var(--ks-gray-700); padding:0.5rem 0.75rem; border-radius:9px; font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.8rem; cursor:pointer; transition:background 0.15s; }
    .iv-copy:hover { background:var(--ks-gray-100); }
    .iv-copy.copied { border-color:#a7f3d0; color:var(--ks-green-dark); background:var(--ks-green-pale); }

    .iv-btn { width:100%; display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; border:none; border-radius:11px; padding:0.85rem 1.25rem; font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.925rem; cursor:pointer; text-decoration:none; transition:background 0.15s; margin-bottom:0.625rem; }
    .iv-btn.wa { background:#25d366; color:#fff; }
    .iv-btn.wa:hover { background:#1eb555; }
    .iv-btn.share { background:var(--ks-green); color:#fff; }
    .iv-btn.share:hover { background:var(--ks-green-dark); }
</style>
@endpush

<div class="iv" x-data="{
        site: @js($site),
        msg: @js($shareMessage),
        copied: false,
        copy() {
            navigator.clipboard?.writeText(this.site).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },
        get canNativeShare() { return typeof navigator !== 'undefined' && !!navigator.share; },
        nativeShare() {
            if (navigator.share) navigator.share({ title: 'KwanzaSafe', text: this.msg }).catch(() => {});
            else this.copy();
        }
    }">

    <div class="iv-topbar">
        <a href="{{ route('dashboard') }}" class="iv-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Voltar ao Painel
        </a>
        <span class="iv-topbar__title">Convidar amigos</span>
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="iv-topbar__logo">
    </div>

    <main class="iv-main">
        <div class="iv-icon">
            <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 12v9H4v-9M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 class="iv-title">Partilha a KwanzaSafe</h1>
        <p class="iv-text">Convida amigos e familiares a enviar dinheiro para Angola com segurança e rapidez. Partilha o link e ajuda-os a começar.</p>

        <div class="iv-linkcard">
            <div class="iv-linkcard__body">
                <div class="iv-linkcard__label">O teu link</div>
                <div class="iv-linkcard__url">{{ $site }}</div>
            </div>
            <button type="button" class="iv-copy" :class="{ copied: copied }" @click="copy()">
                <svg x-show="!copied" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg x-show="copied" x-cloak width="15" height="15" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
            </button>
        </div>

        <a class="iv-btn wa" :href="'https://wa.me/?text=' + encodeURIComponent(msg)" target="_blank" rel="noopener">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
            Partilhar no WhatsApp
        </a>

        <button type="button" class="iv-btn share" x-show="canNativeShare" x-cloak @click="nativeShare()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Partilhar…
        </button>
    </main>
</div>

</x-app-layout>
