<x-app-layout>

@php
    $user = auth()->user();
    $firstName = $user->full_name ? explode(' ', trim($user->full_name))[0] : explode('@', $user->email)[0];

    $fmtDate = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d') : '';

    // Estado de cada passo do KYC
    $emailOk = (bool) $user->email_verified_at;
    $dataOk  = (bool) $user->data_verified;
    $phoneOk = (bool) $user->phone_verified_at;
    $docOk   = (bool) $user->identity_document_path;
    $selfieOk = (bool) $user->profile_photo_path;

    $steps = [$dataOk, $phoneOk, $docOk, $selfieOk];
    $doneCount = count(array_filter($steps));
    $totalSteps = count($steps);
    $kycProgress = (int) round(($doneCount / $totalSteps) * 100);
@endphp

@push('head')
<title>Definições da Conta — KwanzaSafe</title>
<style>
    body { background:var(--ks-off-white); }
    .pf { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; color:var(--ks-black); }
    @media(min-width:1024px) { .pf { padding-bottom:0; } }

    /* ===== TOPBAR ===== */
    .pf-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff; border-bottom:1px solid var(--ks-gray-200);
        padding:0 1.25rem; height:60px;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    .pf-back {
        display:inline-flex; align-items:center; gap:0.5rem;
        font-size:0.85rem; font-weight:600; color:var(--ks-gray-600);
        text-decoration:none; padding:0.5rem 0.625rem; border-radius:8px;
        transition:background 0.15s, color 0.15s;
    }
    .pf-back:hover { background:var(--ks-gray-100); color:var(--ks-black); }
    .pf-topbar__title { font-family:'Syne',sans-serif; font-weight:700; font-size:0.95rem; color:var(--ks-black); }
    .pf-topbar__logo { height:24px; width:auto; }

    /* ===== LAYOUT ===== */
    .pf-main { max-width:680px; margin:0 auto; padding:1.25rem; }
    @media(min-width:768px) { .pf-main { padding:2rem 1.5rem; } }

    /* ===== FLASH ===== */
    .pf-flash {
        padding:0.875rem 1rem; border-radius:12px;
        font-size:0.875rem; margin-bottom:1.25rem;
        display:flex; align-items:center; gap:0.5rem;
        background:#fff; border:1px solid var(--ks-gray-200);
    }
    .pf-flash.success { border-left:3px solid var(--ks-green); color:var(--ks-green-dark); }
    .pf-flash.error { border-left:3px solid var(--ks-danger); color:var(--ks-danger); }

    /* ===== HEADER (identidade sóbria) ===== */
    .pf-head {
        display:flex; align-items:center; gap:1rem;
        background:#fff; border:1px solid var(--ks-gray-200);
        border-radius:16px; padding:1.25rem 1.25rem;
        margin-bottom:1.25rem;
    }
    .pf-head__avatar {
        width:56px; height:56px; border-radius:50%; flex-shrink:0;
        background:var(--ks-green); color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:700; font-size:1.25rem;
        overflow:hidden;
    }
    .pf-head__avatar img { width:100%; height:100%; object-fit:cover; }
    .pf-head__info { min-width:0; flex:1; }
    .pf-head__name { font-family:'Syne',sans-serif; font-weight:700; font-size:1.05rem; color:var(--ks-black); }
    .pf-head__email { font-size:0.82rem; color:var(--ks-gray-500); margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .pf-head__tag {
        display:inline-flex; align-items:center; gap:0.375rem; margin-top:0.5rem;
        font-size:0.72rem; font-weight:600; padding:0.2rem 0.5rem; border-radius:6px;
        border:1px solid var(--ks-gray-200); color:var(--ks-gray-600);
    }
    .pf-head__tag.ok { border-color:#a7f3d0; color:var(--ks-green-dark); }

    /* ===== KYC OVERVIEW ===== */
    .pf-kyc-overview {
        background:#fff; border:1px solid var(--ks-gray-200);
        border-radius:16px; padding:1.25rem; margin-bottom:1.25rem;
    }
    .pf-kyc-overview__top { display:flex; align-items:baseline; justify-content:space-between; margin-bottom:0.75rem; }
    .pf-kyc-overview__title { font-family:'Syne',sans-serif; font-weight:700; font-size:1rem; }
    .pf-kyc-overview__count { font-size:0.85rem; color:var(--ks-gray-500); font-weight:600; }
    .pf-bar { height:6px; background:var(--ks-gray-100); border-radius:999px; overflow:hidden; }
    .pf-bar__fill { height:100%; background:var(--ks-green); border-radius:999px; transition:width 0.3s; }
    .pf-kyc-overview__note { font-size:0.8rem; color:var(--ks-gray-500); margin-top:0.75rem; line-height:1.5; }

    /* ===== CARD ===== */
    .pf-card {
        background:#fff; border:1px solid var(--ks-gray-200);
        border-radius:16px; padding:1.5rem; margin-bottom:1.25rem;
    }
    .pf-card.danger { border-color:#fca5a5; }
    .pf-card__head { margin-bottom:1.25rem; display:flex; align-items:flex-start; justify-content:space-between; gap:0.75rem; }
    .pf-card__head-l { min-width:0; }
    .pf-card__title {
        font-family:'Syne',sans-serif; font-weight:700; font-size:1rem;
        color:var(--ks-black); display:flex; align-items:center; gap:0.5rem;
    }
    .pf-card__title .pf-step-n {
        width:22px; height:22px; border-radius:6px; flex-shrink:0;
        background:var(--ks-gray-100); color:var(--ks-gray-600);
        display:inline-flex; align-items:center; justify-content:center;
        font-family:'DM Sans',sans-serif; font-size:0.75rem; font-weight:700;
    }
    .pf-card.danger .pf-card__title { color:var(--ks-danger); }
    .pf-card__sub { font-size:0.82rem; color:var(--ks-gray-500); margin-top:0.375rem; line-height:1.5; }

    /* pill de estado do passo */
    .pf-pill {
        flex-shrink:0; display:inline-flex; align-items:center; gap:0.3rem;
        font-size:0.7rem; font-weight:700; padding:0.25rem 0.55rem; border-radius:999px;
        border:1px solid var(--ks-gray-200); color:var(--ks-gray-500); background:var(--ks-gray-50);
    }
    .pf-pill.ok { border-color:#a7f3d0; color:var(--ks-green-dark); background:var(--ks-green-pale); }

    /* ===== FORM ===== */
    .pf-field { margin-bottom:1rem; }
    .pf-grid2 { display:grid; grid-template-columns:1fr; gap:0 1rem; }
    @media(min-width:520px) { .pf-grid2 { grid-template-columns:1fr 1fr; } }
    .pf-label {
        display:block; font-size:0.72rem; font-weight:600;
        color:var(--ks-gray-700); margin-bottom:0.375rem;
    }
    .pf-input, .pf-select {
        width:100%; padding:11px 13px;
        border:1px solid var(--ks-gray-300); border-radius:10px;
        font-family:'DM Sans',sans-serif; font-size:0.925rem; color:var(--ks-black);
        background:#fff; outline:none; transition:border-color 0.15s, box-shadow 0.15s;
        -webkit-appearance:none;
    }
    .pf-select { background:#fff; }
    .pf-input:focus, .pf-select:focus { border-color:var(--ks-green); box-shadow:0 0 0 3px rgba(0,157,68,0.12); }
    .pf-input::placeholder { color:var(--ks-gray-400); }
    .pf-input.error, .pf-select.error { border-color:var(--ks-danger); }
    .pf-err { font-size:0.75rem; color:var(--ks-danger); margin-top:0.375rem; }

    /* file input */
    .pf-file {
        display:flex; align-items:center; gap:0.75rem;
        border:1px dashed var(--ks-gray-300); border-radius:10px;
        padding:0.875rem 1rem; background:var(--ks-gray-50);
    }
    .pf-file input[type=file] { font-size:0.82rem; color:var(--ks-gray-600); width:100%; }
    .pf-file input[type=file]::file-selector-button {
        font-family:'DM Sans',sans-serif; font-size:0.8rem; font-weight:600;
        border:1px solid var(--ks-gray-300); background:#fff; color:var(--ks-black);
        padding:0.4rem 0.75rem; border-radius:8px; margin-right:0.75rem; cursor:pointer;
    }

    /* ===== BUTTONS (sóbrios) ===== */
    .pf-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:0.5rem;
        background:var(--ks-green); color:#fff; border:none;
        padding:0.7rem 1.25rem; border-radius:10px;
        font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.875rem;
        cursor:pointer; transition:background 0.15s;
    }
    .pf-btn:hover { background:var(--ks-green-dark); }
    .pf-btn.dark { background:var(--ks-black); }
    .pf-btn.dark:hover { background:var(--ks-gray-800); }
    .pf-btn.danger { background:var(--ks-danger); }
    .pf-btn.danger:hover { background:#b91c1c; }
    .pf-btn.ghost { background:var(--ks-gray-100); color:var(--ks-gray-700); }
    .pf-btn.ghost:hover { background:var(--ks-gray-200); }

    .pf-saved { font-size:0.82rem; color:var(--ks-green-dark); font-weight:600; display:inline-flex; align-items:center; gap:0.375rem; }

    /* ===== STATUS PILL ===== */
    .pf-status {
        display:flex; align-items:center; gap:0.5rem;
        padding:0.75rem 0.875rem; border-radius:10px;
        font-size:0.825rem; margin-bottom:1rem;
        background:var(--ks-gray-50); border:1px solid var(--ks-gray-200);
    }
    .pf-status.ok { border-left:3px solid var(--ks-green); color:var(--ks-green-dark); }
    .pf-status.warn { border-left:3px solid var(--ks-warn); color:#92400e; }
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
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Voltar ao Painel
        </a>
        <span class="pf-topbar__title">Definições da Conta</span>
        <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="pf-topbar__logo">
    </div>

    <main class="pf-main">

        @if(session('success'))
            <div class="pf-flash success" x-data="{s:true}" x-show="s" x-init="setTimeout(()=>s=false,4000)">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="pf-flash error">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v4m0 4h.01" stroke-linecap="round"/></svg>
                Há campos por corrigir. Vê as mensagens a vermelho abaixo.
            </div>
        @endif

        {{-- HEADER --}}
        <div class="pf-head">
            <div class="pf-head__avatar">
                @if(ks_file($user->display_photo_path))
                    <img loading="lazy" decoding="async" src="{{ ks_file($user->display_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($firstName, 0, 1)) }}
                @endif
            </div>
            <div class="pf-head__info">
                <div class="pf-head__name">{{ $user->full_name ?? $firstName }}</div>
                <div class="pf-head__email">{{ $user->email }}</div>
                @if($user->is_fully_verified)
                    <span class="pf-head__tag ok">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Conta verificada
                    </span>
                @else
                    <span class="pf-head__tag">Verificação {{ $doneCount }}/{{ $totalSteps }}</span>
                @endif
            </div>
        </div>

        {{-- VISÃO GERAL KYC --}}
        <div class="pf-kyc-overview" id="kyc">
            <div class="pf-kyc-overview__top">
                <span class="pf-kyc-overview__title">Verificação de identidade (KYC)</span>
                <span class="pf-kyc-overview__count">{{ $doneCount }}/{{ $totalSteps }} concluído</span>
            </div>
            <div class="pf-bar"><div class="pf-bar__fill" style="width:{{ $kycProgress }}%"></div></div>
            <p class="pf-kyc-overview__note">
                @if($user->is_fully_verified)
                    A tua identidade está aprovada. Já podes realizar transações.
                @elseif(!$emailOk)
                    Confirma primeiro o teu email e depois completa os passos abaixo para começares a transacionar.
                @else
                    Completa os passos abaixo para verificares a tua identidade e começares a transacionar.
                @endif
            </p>
            @if(!$emailOk)
                <div class="pf-status warn" style="margin-top:0.875rem; margin-bottom:0;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
                    <span>Email por confirmar. <a href="{{ route('otp.email.verify') }}" style="color:inherit;font-weight:700;text-decoration:underline;">Verificar email →</a></span>
                </div>
            @endif
        </div>

        {{-- INFORMAÇÃO DE PERFIL --}}
        <div class="pf-card">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- ===== PASSO 1 — DADOS PESSOAIS ===== --}}
        <div class="pf-card" id="kyc-data">
            <div class="pf-card__head">
                <div class="pf-card__head-l">
                    <div class="pf-card__title"><span class="pf-step-n">1</span> Dados pessoais</div>
                    <p class="pf-card__sub">Introduz os dados exatamente como aparecem no teu Bilhete de Identidade.</p>
                </div>
                <span class="pf-pill {{ $dataOk ? 'ok' : '' }}">{{ $dataOk ? 'Concluído' : 'Pendente' }}</span>
            </div>

            <form method="POST" action="{{ route('verify.data') }}">
                @csrf
                <div class="pf-field">
                    <label class="pf-label">Nome completo (como no BI)</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                           class="pf-input @error('full_name') error @enderror" placeholder="Nome completo">
                    @error('full_name') <div class="pf-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-grid2">
                    <div class="pf-field">
                        <label class="pf-label">Data de nascimento</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date', $fmtDate($user->birth_date)) }}"
                               class="pf-input @error('birth_date') error @enderror">
                        @error('birth_date') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Género</label>
                        <select name="gender" class="pf-select @error('gender') error @enderror">
                            <option value="">Selecionar…</option>
                            <option value="M" @selected(old('gender', $user->gender) === 'M')>Masculino</option>
                            <option value="F" @selected(old('gender', $user->gender) === 'F')>Feminino</option>
                        </select>
                        @error('gender') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-grid2">
                    <div class="pf-field">
                        <label class="pf-label">Número do BI</label>
                        <input type="text" name="bi_number" value="{{ old('bi_number', $user->bi_number) }}"
                               class="pf-input @error('bi_number') error @enderror" placeholder="000000000XX000">
                        @error('bi_number') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Validade do BI</label>
                        <input type="date" name="bi_expiry" value="{{ old('bi_expiry', $fmtDate($user->bi_expiry)) }}"
                               class="pf-input @error('bi_expiry') error @enderror">
                        @error('bi_expiry') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-grid2">
                    <div class="pf-field">
                        <label class="pf-label">Província</label>
                        <input type="text" name="province" value="{{ old('province', $user->province) }}"
                               class="pf-input @error('province') error @enderror" placeholder="Ex.: Luanda">
                        @error('province') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label">Município</label>
                        <input type="text" name="municipality" value="{{ old('municipality', $user->municipality) }}"
                               class="pf-input @error('municipality') error @enderror" placeholder="Ex.: Belas">
                        @error('municipality') <div class="pf-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label">Morada</label>
                    <input type="text" name="address" value="{{ old('address', $user->address) }}"
                           class="pf-input @error('address') error @enderror" placeholder="Rua, nº, bairro">
                    @error('address') <div class="pf-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-actions">
                    <button type="submit" class="pf-btn">{{ $dataOk ? 'Actualizar dados' : 'Guardar dados' }}</button>
                </div>
            </form>
        </div>

        {{-- ===== PASSO 2 — TELEFONE ===== --}}
        <div class="pf-card" id="kyc-phone">
            <div class="pf-card__head">
                <div class="pf-card__head-l">
                    <div class="pf-card__title"><span class="pf-step-n">2</span> Confirmação de telefone</div>
                    <p class="pf-card__sub">Ao submeteres, recebes um código de confirmação no teu email.</p>
                </div>
                <span class="pf-pill {{ $phoneOk ? 'ok' : '' }}">{{ $phoneOk ? 'Concluído' : 'Pendente' }}</span>
            </div>

            @if($phoneOk)
                <div class="pf-status ok">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Telefone <strong>{{ $user->phone_number }}</strong> verificado em {{ $user->phone_verified_at->format('d/m/Y') }}.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('verify.phone') }}" style="margin-bottom:0.875rem;">
                @csrf
                <div class="pf-inline-form">
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone_number) }}"
                           placeholder="+244 9XX XXX XXX"
                           class="pf-input @error('phone') error @enderror">
                    <button type="submit" class="pf-btn">
                        {{ $user->phone_number ? 'Reenviar código' : 'Enviar código' }}
                    </button>
                </div>
                @error('phone') <div class="pf-err">{{ $message }}</div> @enderror
            </form>

            @if($user->phone_number && !$phoneOk)
                <form method="POST" action="{{ route('verify.phone.submit') }}">
                    @csrf
                    <p class="pf-card__sub" style="margin-bottom:0.5rem;">Introduz o código de 6 dígitos enviado para o teu email:</p>
                    <div class="pf-inline-form">
                        <input type="text" name="phone_otp" maxlength="6" inputmode="numeric"
                               placeholder="000000"
                               class="pf-input @error('phone_otp') error @enderror"
                               style="max-width:180px; text-align:center; letter-spacing:0.3em; flex:0;">
                        <button type="submit" class="pf-btn dark">Confirmar</button>
                    </div>
                    @error('phone_otp') <div class="pf-err">{{ $message }}</div> @enderror
                </form>
            @endif
        </div>

        {{-- ===== PASSO 3 — DOCUMENTO DE IDENTIDADE ===== --}}
        <div class="pf-card" id="kyc-document">
            <div class="pf-card__head">
                <div class="pf-card__head-l">
                    <div class="pf-card__title"><span class="pf-step-n">3</span> Documento de identidade</div>
                    <p class="pf-card__sub">Envia uma foto nítida da frente do teu BI (JPG, PNG ou PDF, até 5 MB).</p>
                </div>
                <span class="pf-pill {{ $docOk ? 'ok' : '' }}">{{ $docOk ? 'Enviado' : 'Pendente' }}</span>
            </div>

            @if($docOk)
                <div class="pf-status ok">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Documento enviado. Podes substituí-lo abaixo se necessário.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('verify.document') }}" enctype="multipart/form-data">
                @csrf
                <div class="pf-field">
                    <div class="pf-file">
                        <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    @error('document') <div class="pf-err">{{ $message }}</div> @enderror
                </div>
                <div class="pf-actions">
                    <button type="submit" class="pf-btn">{{ $docOk ? 'Substituir documento' : 'Enviar documento' }}</button>
                </div>
            </form>
        </div>

        {{-- ===== PASSO 4 — SELFIE ===== --}}
        <div class="pf-card" id="kyc-selfie">
            <div class="pf-card__head">
                <div class="pf-card__head-l">
                    <div class="pf-card__title"><span class="pf-step-n">4</span> Selfie de verificação</div>
                    <p class="pf-card__sub">Foto do teu rosto, com boa luz, sem óculos escuros nem chapéu (JPG ou PNG, até 5 MB). O sistema confirma automaticamente que é uma pessoa real e coincide com o documento.</p>
                </div>
                <span class="pf-pill {{ $selfieOk ? 'ok' : '' }}">{{ $selfieOk ? 'Enviada' : 'Pendente' }}</span>
            </div>

            @if($selfieOk)
                <div class="pf-status ok">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Selfie enviada. Podes substituí-la abaixo se necessário.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('verify.photo') }}" enctype="multipart/form-data">
                @csrf
                <div class="pf-field">
                    <div class="pf-file">
                        <input type="file" name="photo" accept=".jpg,.jpeg,.png" capture="user">
                    </div>
                    @error('photo') <div class="pf-err">{{ $message }}</div> @enderror
                </div>
                <div class="pf-actions">
                    <button type="submit" class="pf-btn">{{ $selfieOk ? 'Substituir selfie' : 'Enviar e concluir' }}</button>
                </div>
            </form>
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
