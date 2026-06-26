<x-app-layout>

@push('head')
<title>Revisão KYC — Admin KwanzaSafe</title>
<style>
body { background:#f5f5f5; }
.kycs-topbar { background:#000; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.kycs-topbar__back { color:#a3a3a3; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.kycs-topbar__back:hover { color:white; }
.kycs-topbar__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.kycs-topbar__logo { height:28px; filter:brightness(0) invert(1); }

.kycs-container { max-width:1400px; margin:0 auto; padding:1.5rem; }
.kycs-flash { padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; font-weight:500; margin-bottom:1rem; }
.kycs-flash.success { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; }
.kycs-flash.error { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

/* ============ BOT BANNER (NOVO) ============ */
.kycs-bot-banner { border-radius:14px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1.25rem; box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.kycs-bot-banner.approved { background:linear-gradient(135deg,#009d44,#007a34); color:white; }
.kycs-bot-banner.pending  { background:linear-gradient(135deg,#f59e0b,#d97706); color:white; }
.kycs-bot-banner.rejected { background:linear-gradient(135deg,#dc2626,#991b1b); color:white; }
.kycs-bot-banner.never    { background:#f5f5f5; color:#737373; border:1px solid #e5e5e5; }

.kycs-bot-score { width:80px; height:80px; border-radius:50%; background:rgba(255,255,255,0.2); display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; border:3px solid rgba(255,255,255,0.3); }
.kycs-bot-banner.never .kycs-bot-score { background:#e5e5e5; border-color:#d4d4d4; }
.kycs-bot-score__num { font-family:'Syne',sans-serif; font-size:1.75rem; font-weight:800; line-height:1; }
.kycs-bot-score__max { font-size:0.6rem; opacity:0.8; font-weight:700; letter-spacing:0.05em; }
.kycs-bot-info { flex:1; min-width:0; }
.kycs-bot-status { font-family:'Syne',sans-serif; font-weight:800; font-size:1rem; }
.kycs-bot-msg { font-size:0.85rem; margin-top:3px; opacity:0.9; line-height:1.5; }
.kycs-bot-time { font-size:0.7rem; margin-top:6px; opacity:0.7; font-family:'JetBrains Mono',monospace; }

.kycs-grid { display:grid; grid-template-columns:1fr 2fr; gap:1.25rem; }
@media(max-width:1024px) { .kycs-grid { grid-template-columns:1fr; } }

.kycs-card { background:white; border-radius:16px; border:1px solid #e5e5e5; overflow:hidden; margin-bottom:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.kycs-card__head { padding:0.875rem 1.125rem; border-bottom:1px solid #f5f5f5; display:flex; align-items:center; justify-content:space-between; background:#fafafa; }
.kycs-card__title { font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#737373; }
.kycs-card__body { padding:1.25rem; }

.kycs-hero { padding:1.5rem 1.125rem; text-align:center; }
.kycs-hero__avatar { width:88px; height:88px; border-radius:50%; background:#d1f2e0; border:3px solid #a7f3d0; overflow:hidden; display:flex; align-items:center; justify-content:center; margin:0 auto 0.875rem; font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; color:#007a34; }
.kycs-hero__name { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:#000; }
.kycs-hero__email { font-size:0.75rem; color:#a3a3a3; margin-top:2px; }

.kycs-datarow { padding:0.75rem 1.125rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f5f5f5; gap:0.75rem; font-size:0.8rem; }
.kycs-datarow:last-child { border-bottom:none; }
.kycs-datarow__label { color:#a3a3a3; font-weight:500; }
.kycs-datarow__value { color:#000; font-weight:700; text-align:right; }
.kycs-datarow__bi { font-family:'JetBrains Mono',monospace; font-size:0.75rem; background:#f5f5f5; padding:2px 7px; border-radius:6px; color:#525252; }

/* ============ BOT CHECKLIST DETALHADO (NOVO) ============ */
.kycs-checks { padding:0; }
.kycs-check-item { padding:0.75rem 1.125rem; border-bottom:1px solid #f5f5f5; display:flex; align-items:flex-start; gap:0.75rem; }
.kycs-check-item:last-child { border-bottom:none; }
.kycs-check-circle { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
.kycs-check-circle.passed { background:#009d44; color:white; }
.kycs-check-circle.failed { background:#dc2626; color:white; }
.kycs-check-circle.partial { background:#f59e0b; color:white; }
.kycs-check-info { flex:1; min-width:0; }
.kycs-check-label { font-size:0.85rem; font-weight:700; color:#000; }
.kycs-check-detail { font-size:0.75rem; color:#737373; margin-top:2px; line-height:1.4; }
.kycs-check-pts { font-family:'Syne',sans-serif; font-size:0.75rem; font-weight:800; padding:2px 8px; border-radius:6px; flex-shrink:0; }
.kycs-check-pts.full { background:#d1f2e0; color:#007a34; }
.kycs-check-pts.partial { background:#fef3c7; color:#92400e; }
.kycs-check-pts.zero { background:#fee2e2; color:#991b1b; }

.kycs-doc-wrap { background:#fafafa; border:2px solid #e5e5e5; border-radius:12px; padding:1rem; min-height:280px; display:flex; align-items:center; justify-content:center; position:relative; }
.kycs-doc-wrap img { max-width:100%; max-height:480px; border-radius:8px; cursor:zoom-in; box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.kycs-doc-pdf { text-align:center; padding:2rem; }
.kycs-doc-pdf a { display:inline-flex; align-items:center; gap:0.5rem; background:#000; color:white; padding:0.75rem 1.25rem; border-radius:10px; text-decoration:none; font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem; text-transform:uppercase; }
.kycs-doc-empty { text-align:center; color:#a3a3a3; padding:2rem; }

.kycs-selfie-row { display:grid; grid-template-columns:120px 1fr; gap:1.25rem; align-items:center; padding:1rem; }
@media(max-width:600px) { .kycs-selfie-row { grid-template-columns:1fr; text-align:center; } }
.kycs-selfie-img { width:120px; height:120px; border-radius:16px; object-fit:cover; border:3px solid #d1f2e0; box-shadow:0 4px 16px rgba(0,0,0,0.08); cursor:zoom-in; }

.kycs-decision { background:linear-gradient(135deg,#000 0%,#0a0a0a 100%); border-radius:20px; padding:1.75rem; color:white; box-shadow:0 20px 60px rgba(0,0,0,0.15); margin-top:1.25rem; }
.kycs-decision__title { font-family:'Syne',sans-serif; font-size:1.125rem; font-weight:800; margin-bottom:0.375rem; }
.kycs-decision__sub { font-size:0.8rem; color:rgba(255,255,255,0.6); margin-bottom:1.25rem; line-height:1.5; }
.kycs-decision__actions { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media(max-width:600px) { .kycs-decision__actions { grid-template-columns:1fr; } }

.kycs-btn-approve { background:#009d44; color:white; padding:0.875rem 1.125rem; border-radius:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem; text-transform:uppercase; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; width:100%; }
.kycs-btn-approve:hover { background:#007a34; }

.kycs-reject-box { display:flex; flex-direction:column; gap:0.5rem; }
.kycs-reject-select, .kycs-reject-custom { width:100%; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); color:white; padding:0.625rem 0.875rem; border-radius:10px; font-size:0.8rem; outline:none; }
.kycs-reject-select option { background:#000; color:white; }
.kycs-btn-reject { background:#dc2626; color:white; padding:0.75rem 1rem; border-radius:10px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem; text-transform:uppercase; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; }

.kycs-approved-card { background:linear-gradient(135deg,#d1f2e0,#a7f3d0); border:2px solid #a7f3d0; border-radius:16px; padding:1.25rem; margin-top:1.25rem; display:flex; align-items:center; gap:1rem; }
.kycs-approved-card__icon { width:48px; height:48px; background:#009d44; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.kycs-approved-card__title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; color:#007a34; }
.kycs-approved-card__sub { font-size:0.75rem; color:#007a34; margin-top:2px; }

.kycs-tag { display:inline-block; font-size:0.58rem; font-weight:800; padding:3px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:0.08em; }
.kycs-tag.ok { background:#d1f2e0; color:#007a34; }
.kycs-tag.blue { background:#dbeafe; color:#1e40af; }
.kycs-tag.purple { background:#ede9fe; color:#6d28d9; }
.kycs-tag.red { background:#fee2e2; color:#991b1b; }
</style>
@endpush

<div class="kycs-topbar">
    <a href="{{ route('admin.kyc.index') }}" class="kycs-topbar__back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Lista KYC
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo-icone.png') }}" class="kycs-topbar__logo" alt="KwanzaSafe">
        <span class="kycs-topbar__title">Revisão KYC</span>
    </div>
    <div style="font-size:0.65rem;color:#a3a3a3;font-family:'JetBrains Mono',monospace;">ID #{{ $kycUser->id }}</div>
</div>

<div class="kycs-container">

    @if(session('success'))
        <div class="kycs-flash success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="kycs-flash error">⚠️ {{ session('error') }}</div>
    @endif

    {{-- ============ 🤖 BOT BANNER (NOVO) ============ --}}
    @php
        $botStatus = $kycUser->kyc_bot_status;
        $botScore = $kycUser->kyc_score ?? 0;
        $botNotes = $kycUser->kyc_bot_notes ?? [];
        $bannerClass = match($botStatus) {
            'auto_approved' => 'approved',
            'auto_rejected' => 'rejected',
            'pending_review' => 'pending',
            default => 'never'
        };
        $botMessages = [
            'auto_approved' => '✅ APROVADO automaticamente pelo bot — score elevado.',
            'auto_rejected' => '❌ REJEITADO automaticamente — score insuficiente. Cliente deve resubmeter.',
            'pending_review' => '⏳ Pendente de revisão manual — score intermédio. Verifica os pontos abaixo.',
        ];
        $botMsg = $botMessages[$botStatus] ?? 'Bot ainda não analisou este utilizador.';
    @endphp

    <div class="kycs-bot-banner {{ $bannerClass }}">
        <div class="kycs-bot-score">
            <div class="kycs-bot-score__num">{{ $botScore }}</div>
            <div class="kycs-bot-score__max">/100 PTS</div>
        </div>
        <div class="kycs-bot-info">
            <div class="kycs-bot-status">🤖 KYC Bot — Verificação Automática</div>
            <div class="kycs-bot-msg">{{ $botMsg }}</div>
            @if($kycUser->kyc_bot_analyzed_at)
                <div class="kycs-bot-time">Analisado: {{ $kycUser->kyc_bot_analyzed_at->format('d/m/Y H:i:s') }}</div>
            @endif
        </div>
    </div>

    <div class="kycs-grid">

        {{-- ============ COLUNA ESQUERDA ============ --}}
        <div>
            <div class="kycs-card">
                <div class="kycs-hero">
                    <div class="kycs-hero__avatar">
                        @if(ks_file($kycUser->profile_photo_path))
                            <img loading="lazy" decoding="async" src="{{ ks_file($kycUser->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($kycUser->full_name ?? $kycUser->email, 0, 1)) }}
                        @endif
                    </div>
                    <div class="kycs-hero__name">{{ $kycUser->full_name ?? 'Sem nome' }}</div>
                    <div class="kycs-hero__email">{{ $kycUser->email }}</div>
                </div>
            </div>

            {{-- ============ 🤖 ANÁLISE DETALHADA (NOVO) ============ --}}
            @if(!empty($botNotes))
            <div class="kycs-card">
                <div class="kycs-card__head"><div class="kycs-card__title">🤖 Análise do Bot</div></div>
                <div class="kycs-checks">
                    @foreach($botNotes as $check)
                        @php
                            $passed = $check['passed'] ?? false;
                            $points = $check['points'] ?? 0;
                            $max = $check['max'] ?? 1;
                            $isPartial = !$passed && $points > 0;
                            $circleClass = $passed ? 'passed' : ($isPartial ? 'partial' : 'failed');
                            $ptsClass = $points === $max ? 'full' : ($points > 0 ? 'partial' : 'zero');
                        @endphp
                        <div class="kycs-check-item">
                            <div class="kycs-check-circle {{ $circleClass }}">
                                @if($passed)
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @elseif($isPartial)
                                    <span style="font-size:0.7rem;font-weight:800;">!</span>
                                @else
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round"/></svg>
                                @endif
                            </div>
                            <div class="kycs-check-info">
                                <div class="kycs-check-label">{{ $check['label'] ?? '—' }}</div>
                                <div class="kycs-check-detail">{{ $check['detail'] ?? '' }}</div>
                            </div>
                            <span class="kycs-check-pts {{ $ptsClass }}">{{ $points }}/{{ $max }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="kycs-card">
                <div class="kycs-card__head"><div class="kycs-card__title">Dados Pessoais</div></div>
                <div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Nome</span><span class="kycs-datarow__value">{{ $kycUser->full_name ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Nascimento</span><span class="kycs-datarow__value">{{ optional($kycUser->birth_date)->format('d/m/Y') ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Género</span><span class="kycs-datarow__value">{{ $kycUser->gender === 'M' ? 'Masculino' : ($kycUser->gender === 'F' ? 'Feminino' : '—') }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Número BI</span><span class="kycs-datarow__bi">{{ $kycUser->bi_number ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Validade BI</span><span class="kycs-datarow__value">{{ optional($kycUser->bi_expiry)->format('d/m/Y') ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Província</span><span class="kycs-datarow__value">{{ $kycUser->province ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Município</span><span class="kycs-datarow__value">{{ $kycUser->municipality ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Telefone</span><span class="kycs-datarow__value">{{ $kycUser->phone_number ?? '—' }}</span></div>
                    <div class="kycs-datarow"><span class="kycs-datarow__label">Membro desde</span><span class="kycs-datarow__value">{{ $kycUser->created_at->format('d/m/Y') }}</span></div>
                </div>
            </div>

            <a href="{{ ks_route('admin.audit.user', $kycUser->id, '#') }}" style="display:flex;align-items:center;justify-content:center;gap:0.5rem;background:#1e293b;color:white;padding:0.75rem;border-radius:12px;text-decoration:none;font-family:'Syne',sans-serif;font-weight:800;font-size:0.75rem;text-transform:uppercase;">
                Ver Audit Trail
            </a>
        </div>

        {{-- ============ COLUNA DIREITA ============ --}}
        <div>
            <div class="kycs-card">
                <div class="kycs-card__head">
                    <div class="kycs-card__title">🪪 Documento de Identidade</div>
                    @if($kycUser->identity_document_path)<span class="kycs-tag blue">Presente</span>@else<span class="kycs-tag red">Não Submetido</span>@endif
                </div>
                <div class="kycs-card__body">
                    @php
                        $docUrl = ks_file($kycUser->identity_document_path);
                        $isPdf = ks_is_pdf($kycUser->identity_document_path);
                    @endphp

                    @if($docUrl)
                        @if($isPdf)
                            <div class="kycs-doc-wrap">
                                <div class="kycs-doc-pdf">
                                    <svg width="48" height="48" fill="none" stroke="#a3a3a3" stroke-width="1.5" style="margin:0 auto 0.75rem;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <a href="{{ $docUrl }}" target="_blank">Abrir PDF</a>
                                </div>
                            </div>
                        @else
                            <div class="kycs-doc-wrap">
                                <img src="{{ $docUrl }}" alt="Documento" onclick="window.open(this.src,'_blank')">
                            </div>
                        @endif
                    @else
                        <div class="kycs-doc-wrap kycs-doc-empty">
                            <div style="font-size:0.8rem;">Utilizador ainda não submeteu documento.</div>
                        </div>
                    @endif
                </div>
            </div>

            @php $selfieUrl = ks_file($kycUser->profile_photo_path); @endphp
            @if($selfieUrl)
            <div class="kycs-card">
                <div class="kycs-card__head">
                    <div class="kycs-card__title">📷 Selfie</div>
                    <span class="kycs-tag purple">Presente</span>
                </div>
                <div class="kycs-selfie-row">
                    <img src="{{ $selfieUrl }}" alt="Selfie" class="kycs-selfie-img" onclick="window.open(this.src,'_blank')">
                    <div style="font-size:0.825rem;color:#525252;line-height:1.6;">
                        <strong>Verificação Visual</strong>
                        <div style="margin-top:3px;">Compara o rosto com a fotografia do documento.</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ============ DECISÃO ============ --}}
            @if(!$kycUser->identity_verified_at && $kycUser->identity_document_path)
                <div class="kycs-decision">
                    <h3 class="kycs-decision__title">Decisão de Verificação</h3>
                    <p class="kycs-decision__sub">
                        @if($botStatus === 'pending_review')
                            🤖 Bot atribuiu <strong>{{ $botScore }}/100</strong> — necessita revisão manual.
                        @else
                            Aprova ou rejeita manualmente. A decisão sobrepõe-se ao bot.
                        @endif
                    </p>

                    <div class="kycs-decision__actions">
                        <form method="POST" action="{{ route('admin.kyc.approve', $kycUser->id) }}"
                              onsubmit="return confirm('Confirmas APROVAÇÃO de {{ addslashes($kycUser->full_name ?? $kycUser->email) }}?');">
                            @csrf
                            <button type="submit" class="kycs-btn-approve">✓ Aprovar Identidade</button>
                        </form>

                        <form method="POST" action="{{ route('admin.kyc.reject', $kycUser->id) }}"
                              x-data="{reason:'', custom:''}"
                              @submit.prevent="
                                const r = reason === '__custom__' ? custom.trim() : reason;
                                if(!r){ alert('Indica o motivo.'); return; }
                                if(confirm('Rejeitar com motivo: '+r+'?')){
                                    $el.querySelector('input[name=reason]').value = r;
                                    $el.submit();
                                }">
                            @csrf
                            <input type="hidden" name="reason" value="">
                            <div class="kycs-reject-box">
                                <select x-model="reason" class="kycs-reject-select">
                                    <option value="">Selecionar motivo…</option>
                                    <option value="Documento ilegível">📷 Documento ilegível</option>
                                    <option value="Selfie não corresponde ao documento">👤 Selfie não corresponde</option>
                                    <option value="Documento expirado">📅 Documento expirado</option>
                                    <option value="Dados pessoais incompletos">📝 Dados incompletos</option>
                                    <option value="Documento suspeito">🚨 Documento suspeito</option>
                                    <option value="__custom__">✏️ Outro motivo…</option>
                                </select>
                                <template x-if="reason === '__custom__'">
                                    <input type="text" x-model="custom" placeholder="Motivo…" class="kycs-reject-custom">
                                </template>
                                <button type="submit" class="kycs-btn-reject">✕ Rejeitar & Notificar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif($kycUser->identity_verified_at)
                <div class="kycs-approved-card">
                    <div class="kycs-approved-card__icon">
                        <svg width="22" height="22" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div>
                        <div class="kycs-approved-card__title">Identidade Aprovada</div>
                        <div class="kycs-approved-card__sub">Verificado em {{ $kycUser->identity_verified_at->format('d/m/Y \à\s H:i') }}.</div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

</x-app-layout>
 