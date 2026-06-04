<x-app-layout>
{{--
|==========================================================================
| KwanzaSafe — Sala de Transação com Chat Bidirecional
| Cliente acompanha o estado, envia comprovativo, e comunica com o Admin.
|==========================================================================
--}}

@php
    // O cliente só vê os canais 'client' e 'recourse' — nunca notas internas do staff.
    $clientChannels = ['client', 'recourse'];

    // Carregar mensagens da conversa
    $messages = \App\Models\ChatMessage::with('sender')
        ->where('transaction_id', $transaction->id)
        ->whereIn('channel', $clientChannels)
        ->orderBy('created_at', 'asc')
        ->get();

    // Marcar mensagens do admin como lidas (cliente está a ver)
    \App\Models\ChatMessage::where('transaction_id', $transaction->id)
        ->whereIn('channel', $clientChannels)
        ->where('sender_id', '!=', auth()->id())
        ->where('is_read', false)
        ->update(['is_read' => true]);

    $currentUserId = auth()->id();

    // Recurso activo (se houver) sobre esta transação
    $activeRecourse = \App\Models\Recourse::where('transaction_id', $transaction->id)
        ->whereIn('status', ['open', 'in_review'])
        ->latest()
        ->first();
    $lastRecourse = $activeRecourse ?? \App\Models\Recourse::where('transaction_id', $transaction->id)
        ->latest()
        ->first();

    // Conta de recepção activa para a moeda desta transação (gerida pelo admin)
    $paymentAccount = \App\Models\PaymentAccount::activeFor($transaction->currency_from);
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap');
.tr-root { font-family:'DM Sans',sans-serif; background:#f0fdf4; min-height:100dvh; }
.tr-root * { box-sizing:border-box; }
.tr-font-display { font-family:'Syne',sans-serif; }
.tr-topbar { background:white; border-bottom:1px solid #e2e8f0; padding:0 1.5rem; height:64px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; }
.tr-back-btn { display:flex; align-items:center; gap:0.5rem; font-size:0.875rem; font-weight:600; color:#64748b; text-decoration:none; transition:color 0.18s; }
.tr-back-btn:hover { color:#064e3b; }
.tr-content { max-width:800px; margin:0 auto; padding:1.5rem; }
.tr-status-bar { background:white; border-radius:1.25rem; border:1px solid #e2e8f0; box-shadow:0 4px 24px rgba(6,78,59,0.06); padding:1.25rem 1.5rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; }
.tr-status-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.tr-status-icon.pending          { background:#fffbeb; color:#d97706; }
.tr-status-icon.negotiating      { background:#f0fdf4; color:#009d44; }
.tr-status-icon.awaiting_payment { background:#eff6ff; color:#2563eb; }
.tr-status-icon.payment_received { background:#f0fdf4; color:#065f46; }
.tr-status-icon.processing       { background:#eff6ff; color:#2563eb; }
.tr-status-icon.aoa_sent         { background:#ecfdf5; color:#059669; }
.tr-status-icon.completed        { background:#ecfdf5; color:#064e3b; }
.tr-status-icon.cancelled        { background:#fee2e2; color:#dc2626; }
.tr-status-icon.expired          { background:#f5f5f5; color:#737373; }
.tr-confirm-btn { width:100%; background:linear-gradient(135deg,#009d44,#007a34); color:white; border:none; padding:1.125rem; border-radius:14px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.625rem; transition:all 0.2s; box-shadow:0 6px 20px rgba(0,157,68,0.3); }
.tr-confirm-btn:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(0,157,68,0.4); }
.tr-confirm-btn:active { transform:translateY(0); }
.tr-sys-msg { text-align:center; padding:0.5rem 1rem; margin:0.5rem auto; max-width:80%; }
.tr-sys-msg__text { display:inline-block; background:#f0fdf4; border:1px solid rgba(0,157,68,0.15); color:#065f46; font-size:0.75rem; font-weight:600; padding:0.375rem 0.875rem; border-radius:999px; line-height:1.4; }
.tr-status-label { font-size:0.625rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#94a3b8; }
.tr-status-value { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; color:#0f172a; }
.tr-ref-badge { margin-left:auto; background:#064e3b; color:white; padding:0.375rem 0.875rem; border-radius:20px; font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; }
.tr-card { background:white; border-radius:1.5rem; border:1px solid #e2e8f0; box-shadow:0 4px 24px rgba(6,78,59,0.06); padding:1.5rem; margin-bottom:1.25rem; }
.tr-card__title { font-family:'Syne',sans-serif; font-size:0.875rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem; }
.tr-amounts { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.tr-amount-box { border-radius:14px; padding:1.25rem; }
.tr-amount-box.sent { background:#f8fafc; border:2px solid #e2e8f0; }
.tr-amount-box.receive { background:linear-gradient(135deg,#ecfdf5,#d1fae5); border:2px solid #a7f3d0; }
.tr-amount-label { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#94a3b8; margin-bottom:0.375rem; }
.tr-amount-box.receive .tr-amount-label { color:#065f46; }
.tr-amount-value { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:#0f172a; }
.tr-amount-box.receive .tr-amount-value { color:#064e3b; }
.tr-amount-rate { font-size:0.7rem; color:#64748b; margin-top:4px; }

.tr-upload-zone { border:2px dashed #e2e8f0; border-radius:14px; padding:2rem; text-align:center; cursor:pointer; transition:all 0.2s; position:relative; }
.tr-upload-zone:hover { border-color:#064e3b; background:#f0fdf4; }
.tr-upload-zone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.tr-upload-btn { margin-top:1rem; width:100%; padding:1rem; background:#064e3b; color:white; border:none; border-radius:14px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.875rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:0.5rem; }
.tr-upload-btn:hover { background:#065f46; }
.tr-bank-info { background:linear-gradient(135deg,#064e3b,#047857); border-radius:14px; padding:1.5rem; color:white; }
.tr-bank-info__label { font-size:0.625rem; font-weight:700; text-transform:uppercase; letter-spacing:0.15em; opacity:0.6; margin-bottom:0.25rem; }
.tr-bank-info__value { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; margin-bottom:0.875rem; }
.tr-bank-info__value:last-child { margin-bottom:0; }
.tr-copy-btn { background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.2); color:white; padding:0.25rem 0.625rem; border-radius:6px; font-size:0.65rem; font-weight:700; cursor:pointer; transition:all 0.18s; display:inline-flex; align-items:center; gap:0.25rem; }
.tr-copy-btn:hover { background:rgba(255,255,255,0.25); }
.tr-alert { border-radius:12px; padding:1rem 1.25rem; display:flex; gap:0.75rem; font-size:0.8rem; line-height:1.6; margin-bottom:1rem; }
.tr-alert.warn    { background:#fffbeb; border:1px solid #fde68a; color:#78350f; }
.tr-alert.success { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
.tr-alert.info    { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }

/* ===== CHAT ===== */
.tr-chat-wrap { background:white; border-radius:1.5rem; border:1px solid #e2e8f0; box-shadow:0 4px 24px rgba(6,78,59,0.06); overflow:hidden; margin-bottom:1.25rem; }
.tr-chat-header { padding:1rem 1.5rem; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; gap:0.75rem; background:#f8fafc; }
.tr-chat-header__avatar { width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg,#064e3b,#059669); color:white; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.875rem; }
.tr-chat-header__name  { font-family:'Syne',sans-serif; font-size:0.9rem; font-weight:800; color:#0f172a; }
.tr-chat-header__sub   { font-size:0.7rem; color:#10b981; font-weight:600; display:flex; align-items:center; gap:0.375rem; }
.tr-chat-header__sub::before { content:''; width:6px; height:6px; background:#10b981; border-radius:50%; display:inline-block; }

.tr-chat-body { padding:1.25rem 1.5rem; max-height:500px; overflow-y:auto; background:#fcfcfd; }
.tr-chat-body::-webkit-scrollbar { width:6px; }
.tr-chat-body::-webkit-scrollbar-track { background:transparent; }
.tr-chat-body::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }

.tr-msg { display:flex; gap:0.75rem; margin-bottom:1rem; max-width:85%; }
.tr-msg.mine { margin-left:auto; flex-direction:row-reverse; }
.tr-msg__avatar { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem; color:white; overflow:hidden; }
.tr-msg.mine .tr-msg__avatar { background:#064e3b; }
.tr-msg.admin .tr-msg__avatar { background:#1e293b; }
.tr-msg__content { display:flex; flex-direction:column; }
.tr-msg.mine .tr-msg__content { align-items:flex-end; }
.tr-msg__bubble { padding:0.75rem 1rem; border-radius:16px; font-size:0.875rem; line-height:1.5; word-wrap:break-word; max-width:100%; }
.tr-msg.mine .tr-msg__bubble  { background:#064e3b; color:white; border-bottom-right-radius:4px; }
.tr-msg.admin .tr-msg__bubble { background:#f1f5f9; color:#0f172a; border-bottom-left-radius:4px; border:1px solid #e2e8f0; }
.tr-msg__meta  { font-size:0.65rem; color:#94a3b8; margin-top:3px; display:flex; align-items:center; gap:0.375rem; }
.tr-msg.mine .tr-msg__meta { flex-direction:row-reverse; }
.tr-msg__image { max-width:220px; max-height:220px; border-radius:12px; margin-top:4px; cursor:zoom-in; border:1px solid #e2e8f0; display:block; }
.tr-msg__file  { display:inline-flex; align-items:center; gap:0.5rem; background:rgba(255,255,255,0.15); padding:0.5rem 0.75rem; border-radius:8px; text-decoration:none; color:inherit; font-weight:600; font-size:0.8rem; margin-top:4px; }
.tr-msg.admin .tr-msg__file { background:white; border:1px solid #e2e8f0; color:#0f172a; }
.tr-msg.mine .tr-msg__file:hover { background:rgba(255,255,255,0.25); }

.tr-chat-empty { text-align:center; padding:3rem 1rem; color:#94a3b8; font-size:0.875rem; }
.tr-chat-empty__icon { font-size:2.5rem; margin-bottom:0.75rem; opacity:0.5; }

.tr-chat-form { padding:1rem 1.25rem; background:white; border-top:1px solid #e2e8f0; }
.tr-chat-form__inner { display:flex; gap:0.5rem; align-items:flex-end; }
.tr-chat-form__textarea { flex:1; border:2px solid #e2e8f0; border-radius:14px; padding:0.75rem 1rem; font-family:'DM Sans',sans-serif; font-size:0.875rem; resize:none; outline:none; transition:border-color 0.18s; max-height:120px; min-height:44px; }
.tr-chat-form__textarea:focus { border-color:#064e3b; }
.tr-chat-form__btn { width:44px; height:44px; border-radius:50%; background:#064e3b; color:white; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.18s; flex-shrink:0; }
.tr-chat-form__btn:hover { background:#065f46; }
.tr-chat-form__btn:disabled { opacity:0.5; cursor:not-allowed; }
.tr-chat-form__attach { width:44px; height:44px; border-radius:50%; background:#f1f5f9; color:#64748b; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.18s; flex-shrink:0; position:relative; }
.tr-chat-form__attach:hover { background:#e2e8f0; color:#0f172a; }
.tr-chat-form__attach input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
.tr-chat-form__preview { background:#ecfdf5; border:1px solid #a7f3d0; padding:0.5rem 0.75rem; border-radius:10px; font-size:0.75rem; color:#065f46; display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem; }
.tr-chat-form__preview button { background:none; border:none; color:#dc2626; cursor:pointer; margin-left:auto; font-weight:700; }

@media(max-width:600px) { .tr-amounts { grid-template-columns:1fr; } .tr-content { padding:1rem; } .tr-chat-body { max-height:400px; } .tr-msg__image { max-width:180px; max-height:180px; } }
</style>

<div class="tr-root">

    <div class="tr-topbar">
        <a href="{{ route('dashboard') }}" class="tr-back-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Dashboard
        </a>
        <span style="font-family:'Syne',sans-serif;font-size:0.875rem;font-weight:800;color:#0f172a;">Sala de Transação</span>
        <div style="width:80px;"></div>
    </div>

    <div class="tr-content">

        @if(session('success'))
        <div class="tr-alert success" x-data="{s:true}" x-show="s">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div class="tr-alert warn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Status Bar --}}
        <div class="tr-status-bar">
            <div class="tr-status-icon {{ $transaction->status }}">
                @php $st = $transaction->status; @endphp
                @if($st === 'completed')
                    {{-- check circle --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($st === 'cancelled' || $st === 'expired')
                    {{-- X circle --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                @elseif($st === 'awaiting_payment' || $st === 'processing')
                    {{-- credit card / upload --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                @elseif($st === 'payment_received')
                    {{-- shield check --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                @elseif($st === 'aoa_sent')
                    {{-- send arrow --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                @elseif($st === 'negotiating')
                    {{-- chat bubble --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                @else
                    {{-- clock (pending / default) --}}
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </div>
            <div>
                <div class="tr-status-label">Estado da Operação</div>
                <div class="tr-status-value">
                    @php
                        $statusLabels = [
                            'pending'          => 'Aguarda Confirmação do Agente',
                            'negotiating'      => 'Em Negociação com o Agente',
                            'awaiting_payment' => 'A Aguardar Pagamento',
                            'payment_received' => 'Pagamento Confirmado',
                            'processing'       => 'Comprovativo em Análise',
                            'aoa_sent'         => 'Kwanzas Enviados — Confirma Recepção',
                            'completed'        => 'Concluída — Kwanzas Enviados!',
                            'cancelled'        => 'Operação Cancelada',
                            'expired'          => 'Operação Expirada',
                        ];
                    @endphp
                    {{ $statusLabels[$transaction->status] ?? ucfirst($transaction->status) }}
                </div>
            </div>
            <div class="tr-ref-badge">#{{ $transaction->reference_id }}</div>
        </div>

        {{-- Resumo Financeiro --}}
        <div class="tr-card">
            <div class="tr-card__title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Resumo da Operação
            </div>
            <div class="tr-amounts">
                <div class="tr-amount-box sent">
                    <div class="tr-amount-label">Tu Envias</div>
                    <div class="tr-amount-value tr-font-display">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</div>
                    <div class="tr-amount-rate">Taxa aplicada: 1 {{ $transaction->currency_from }} = {{ number_format($transaction->rate_applied, 2, ',', '.') }} Kz</div>
                </div>
                <div class="tr-amount-box receive">
                    <div class="tr-amount-label">Recebes em AOA</div>
                    <div class="tr-amount-value tr-font-display">{{ number_format($transaction->amount_received, 2, ',', '.') }} Kz</div>
                    <div class="tr-amount-rate">Criada {{ $transaction->created_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>

        {{-- Dados para Pagamento (configurados pelo admin por moeda) --}}
        @if(in_array($transaction->status, ['pending', 'negotiating', 'awaiting_payment']))
        <div class="tr-card">
            <div class="tr-card__title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                Dados para Pagamento (KwanzaSafe)
            </div>

            @if($paymentAccount)
                <div class="tr-alert warn" style="margin-bottom:1rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg>
                    <span>Paga em <strong>{{ $transaction->currency_from }}</strong> o valor exato de <strong>{{ number_format($transaction->amount_sent, 2, ',', '.') }}</strong>. Coloca <strong>#{{ $transaction->reference_id }}</strong> na referência.</span>
                </div>

                <div class="tr-bank-info">
                    <div>
                        <div class="tr-bank-info__label">Titular</div>
                        <div class="tr-bank-info__value">{{ $paymentAccount->holder }}</div>
                    </div>
                    <div>
                        <div class="tr-bank-info__label">IBAN / Conta / Carteira ({{ $transaction->currency_from }})</div>
                        <div class="tr-bank-info__value" style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;word-break:break-all;">
                            <span id="ks-pay-id">{{ $paymentAccount->identifier }}</span>
                            <button class="tr-copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('ks-pay-id').textContent.trim());this.textContent='✓ Copiado'">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                Copiar
                            </button>
                        </div>
                    </div>
                    @if($paymentAccount->network)
                    <div>
                        <div class="tr-bank-info__label">Rede / BIC</div>
                        <div class="tr-bank-info__value">{{ $paymentAccount->network }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="tr-bank-info__label">Referência Obrigatória</div>
                        <div class="tr-bank-info__value" style="display:flex;align-items:center;gap:0.75rem;">
                            <span>#{{ $transaction->reference_id }}</span>
                            <button class="tr-copy-btn" onclick="navigator.clipboard.writeText('#{{ $transaction->reference_id }}');this.textContent='✓ Copiado'">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                Copiar
                            </button>
                        </div>
                    </div>
                </div>

                @if($paymentAccount->instructions)
                    <div style="margin-top:0.875rem;font-size:0.78rem;color:#64748b;line-height:1.5;">{{ $paymentAccount->instructions }}</div>
                @endif
            @else
                <div class="tr-alert info">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/></svg>
                    <span>O nosso agente vai indicar-te os dados de pagamento em <strong>{{ $transaction->currency_from }}</strong> aqui no chat. Mantém a referência <strong>#{{ $transaction->reference_id }}</strong>.</span>
                </div>
            @endif
        </div>
        @endif

        {{-- Upload Comprovativo --}}
        @if(in_array($transaction->status, ['pending', 'negotiating', 'awaiting_payment']))
        <div class="tr-card">
            <div class="tr-card__title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Enviar Comprovativo de Pagamento
            </div>

            <form method="POST" action="{{ route('transaction.upload', $transaction->reference_id) }}" enctype="multipart/form-data" x-data="{fileName:''}">
                @csrf
                <div class="tr-upload-zone">
                    <input type="file" name="comprovativo" accept=".pdf,.jpg,.jpeg,.png" required @change="fileName=$event.target.files[0]?.name||''">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.75rem;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <div style="font-size:0.9rem;font-weight:600;color:#64748b;" x-text="fileName || 'Toca aqui para selecionar o comprovativo'"></div>
                    <div style="font-size:0.75rem;color:#94a3b8;margin-top:4px;">PDF, JPG ou PNG • Máx. 5MB</div>
                </div>
                <button type="submit" class="tr-upload-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Enviar Comprovativo
                </button>
            </form>
        </div>
        @endif

        {{-- Confirmar Recepção dos Kwanzas (apenas quando aoa_sent) --}}
        @if($transaction->status === 'aoa_sent')
        <div class="tr-card">
            <div class="tr-card__title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Confirma a Recepção
            </div>
            <div class="tr-alert info" style="margin-bottom:1.25rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/></svg>
                <span>O nosso agente indica que os Kwanzas foram enviados. Confirma quando o valor estiver disponível na tua conta.</span>
            </div>
            <form method="POST" action="{{ route('transaction.confirm', $transaction->reference_id) }}">
                @csrf
                <button type="submit" class="tr-confirm-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Confirmar Recepção dos Kwanzas
                </button>
            </form>
        </div>
        @endif

        {{-- Comprovativo (apenas concluídas) --}}
        @if($transaction->status === 'completed')
        <div style="text-align:center;padding:0.5rem 0 0.75rem;">
            <a href="{{ route('transaction.receipt', $transaction->reference_id) }}" target="_blank"
               style="display:inline-flex;align-items:center;gap:0.5rem;background:#064e3b;color:white;text-decoration:none;padding:0.75rem 1.5rem;border-radius:12px;font-family:'Syne',sans-serif;font-weight:800;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Descarregar Comprovativo
            </a>
        </div>
        @endif

        {{-- Cancelar Transação (apenas pending / negotiating) --}}
        @if(in_array($transaction->status, ['pending', 'negotiating']))
        <div style="text-align:center;padding:0.25rem 0 0.75rem;">
            <form method="POST" action="{{ route('transaction.cancel', $transaction->reference_id) }}"
                  onsubmit="return confirm('Tens a certeza que queres cancelar esta transação? Esta acção não pode ser revertida.');">
                @csrf
                <button type="submit" style="background:none;border:none;color:#dc2626;font-size:0.8rem;font-weight:700;cursor:pointer;font-family:\'DM Sans\',sans-serif;text-decoration:underline;padding:0;">
                    Cancelar esta transação
                </button>
            </form>
        </div>
        @endif

        {{-- ===== CHAT BIDIRECIONAL ===== --}}
        <div class="tr-chat-wrap" x-data="chatRoom()" x-init="scrollToBottom()">
            <div class="tr-chat-header">
                <div class="tr-chat-header__avatar">KS</div>
                <div>
                    <div class="tr-chat-header__name">Suporte KwanzaSafe</div>
                    <div class="tr-chat-header__sub">Online • Resposta em até 4h</div>
                </div>
            </div>

            <div class="tr-chat-body" id="chat-body" x-ref="body">
                @if($messages->isEmpty())
                    <div class="tr-chat-empty">
                        <div class="tr-chat-empty__icon">💬</div>
                        <div>Ainda não há mensagens. Escreve abaixo para contactar o suporte sobre esta transação.</div>
                    </div>
                @else
                    @foreach($messages as $msg)
                        @php
                            $isSystem = is_null($msg->sender_id);
                            $isMine   = !$isSystem && $msg->sender_id === $currentUserId;
                        @endphp
                        @if($isSystem)
                            <div class="tr-sys-msg">
                                <span class="tr-sys-msg__text">{{ $msg->message_text }}</span>
                            </div>
                        @else
                            <div class="tr-msg {{ $isMine ? 'mine' : 'admin' }}">
                                <div class="tr-msg__avatar">
                                    {{ $isMine ? 'EU' : 'KS' }}
                                </div>
                                <div class="tr-msg__content">
                                    <div class="tr-msg__bubble">
                                        @if($msg->message_text)
                                            {{ $msg->message_text }}
                                        @endif

                                        @if($msg->file_path)
                                            @if($msg->is_image)
                                                <img src="{{ $msg->file_url }}" alt="anexo" class="tr-msg__image" onclick="window.open(this.src,'_blank')">
                                            @else
                                                <a href="{{ $msg->file_url }}" target="_blank" class="tr-msg__file">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9V5a3 3 0 013-3v0a3 3 0 013 3v10a5 5 0 01-10 0V9a1 1 0 012 0v6a3 3 0 006 0V5a1 1 0 00-2 0v4"/></svg>
                                                    Ver Documento
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="tr-msg__meta">
                                        <span>{{ $msg->created_at->format('d/m H:i') }}</span>
                                        @if($isMine && $msg->is_read)
                                            <span style="color:#10b981;" title="Lida pelo admin">✓✓</span>
                                        @elseif($isMine)
                                            <span style="color:#94a3b8;" title="Enviada">✓</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <form method="POST" action="{{ route('chat.send', $transaction->reference_id) }}" enctype="multipart/form-data" class="tr-chat-form" x-data="{attachmentName:''}" @submit="setTimeout(()=>{attachmentName=''},100)">
                @csrf
                <template x-if="attachmentName">
                    <div class="tr-chat-form__preview">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span x-text="attachmentName"></span>
                        <button type="button" @click="attachmentName='';$refs.fileInput.value=''">✕</button>
                    </div>
                </template>

                <div class="tr-chat-form__inner">
                    <label class="tr-chat-form__attach" title="Anexar ficheiro">
                        <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" x-ref="fileInput" @change="attachmentName=$event.target.files[0]?.name||''">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </label>

                    <textarea
    name="message_text"
    class="tr-chat-form__textarea"
    placeholder="Escreve a tua mensagem..."
    rows="1"
    data-ks-persist="chat_client_{{ $transaction->reference_id }}"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>

                    <button type="submit" class="tr-chat-form__btn" title="Enviar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- ===== RECURSO ===== --}}
        <div class="tr-card" x-data="{ openForm: false }">
            <div class="tr-card__title">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l9-4 9 4M4 10v8m16-8v8M4 18h16M9 10v8m6-8v8"/></svg>
                Recurso
            </div>

            @if($activeRecourse)
                <div class="tr-alert info" style="margin-bottom:0.875rem;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
                    <span>Tens um recurso <strong>{{ $activeRecourse->status === 'open' ? 'aberto' : 'em análise' }}</strong>. Um super-administrador irá responder-te aqui no chat. Podes acrescentar informação abaixo.</span>
                </div>
                <form method="POST" action="{{ route('recourse.reply', $transaction->reference_id) }}" style="display:flex;gap:0.5rem;align-items:flex-end;">
                    @csrf
                    <textarea name="message_text" rows="2" required placeholder="Acrescenta informação ao teu recurso..." class="tr-chat-form__textarea" style="flex:1;"></textarea>
                    <button type="submit" class="tr-chat-form__btn" title="Enviar ao super-admin">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
            @elseif($lastRecourse && in_array($lastRecourse->status, ['resolved','rejected']))
                <div class="tr-alert {{ $lastRecourse->status === 'resolved' ? 'success' : 'warn' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                    <span>
                        <strong>Recurso {{ $lastRecourse->status === 'resolved' ? 'resolvido' : 'indeferido' }}.</strong>
                        @if($lastRecourse->resolution) {{ $lastRecourse->resolution }} @endif
                    </span>
                </div>
            @elseif(!in_array($transaction->status, ['completed','cancelled','expired']))
                <p style="font-size:0.8rem;color:#64748b;line-height:1.6;margin-bottom:0.75rem;">
                    Sentes que houve uma falha ou injustiça nesta transação? Abre um recurso e um super-administrador irá analisar o teu caso pessoalmente.
                </p>
                <button type="button" class="tr-back-btn" @click="openForm = !openForm" style="border:1px solid #e2e8f0;padding:0.5rem 0.875rem;border-radius:10px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
                    Abrir recurso
                </button>
                <form method="POST" action="{{ route('recourse.open', $transaction->reference_id) }}" x-show="openForm" x-cloak style="margin-top:0.875rem;">
                    @csrf
                    <textarea name="reason" rows="3" required maxlength="2000" placeholder="Descreve o que correu mal..." class="tr-chat-form__textarea" style="width:100%;"></textarea>
                    <button type="submit" class="tr-upload-btn" style="margin-top:0.625rem;">Submeter Recurso</button>
                </form>
            @else
                <p style="font-size:0.8rem;color:#94a3b8;">Não há recursos para esta transação.</p>
            @endif
        </div>

        {{-- Suporte WhatsApp (alternativo) --}}
        <div style="text-align:center;padding:0.5rem 0 1.5rem;">
            <a href="https://wa.me/244931719207?text=Suporte+transação+%23{{ $transaction->reference_id }}" target="_blank" style="display:inline-flex;align-items:center;gap:0.625rem;color:#25d366;padding:0.5rem 1rem;font-family:'Syne',sans-serif;font-weight:700;font-size:0.75rem;text-decoration:none;">
                Ou contacta via WhatsApp →
            </a>
        </div>

    </div>
</div>

<script>
function chatRoom() {
    return {
        lastId: {{ $messages->max('id') ?? 0 }},
        _timer: null,

        init() {
            this.$nextTick(() => this.scrollToBottom());
            this._timer = setInterval(() => this.poll(), 5000);
        },

        destroy() { clearInterval(this._timer); },

        scrollToBottom() {
            const b = document.getElementById('chat-body');
            if (b) b.scrollTop = b.scrollHeight;
        },

        async poll() {
            try {
                const r = await fetch('{{ route('chat.poll', $transaction->reference_id) }}?after=' + this.lastId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!r.ok) return;
                const { messages } = await r.json();
                if (!messages || !messages.length) return;
                const hasNewFromOther = messages.some(m => !m.is_mine);
                this.appendMessages(messages);
                this.lastId = messages[messages.length - 1].id;
                this.scrollToBottom();
                if (hasNewFromOther) this.playSound();
            } catch (e) {}
        },

        appendMessages(messages) {
            const body = document.getElementById('chat-body');
            const empty = body.querySelector('.tr-chat-empty');
            if (empty) empty.remove();
            messages.forEach(msg => {
                const w = document.createElement('div');
                w.innerHTML = this.buildMessage(msg).trim();
                body.appendChild(w.firstElementChild);
            });
        },

        buildMessage(msg) {
            if (msg.is_system) {
                return `<div class="tr-sys-msg"><span class="tr-sys-msg__text">${this.esc(msg.text)}</span></div>`;
            }
            const side     = msg.is_mine ? 'mine' : 'admin';
            const initials = msg.is_mine ? 'EU' : 'KS';
            let body = '';
            if (msg.text) body += this.esc(msg.text);
            if (msg.file_url) {
                if (msg.is_image) {
                    body += `<img src="${msg.file_url}" class="tr-msg__image" onclick="window.open(this.src,'_blank')">`;
                } else {
                    body += `<a href="${msg.file_url}" target="_blank" class="tr-msg__file">📎 Ver Documento</a>`;
                }
            }
            const tick = msg.is_mine
                ? `<span style="color:${msg.is_read ? '#10b981' : '#94a3b8'};">${msg.is_read ? '✓✓' : '✓'}</span>`
                : '';
            return `<div class="tr-msg ${side}">
                <div class="tr-msg__avatar">${initials}</div>
                <div class="tr-msg__content">
                    <div class="tr-msg__bubble">${body}</div>
                    <div class="tr-msg__meta"><span>${msg.time}</span>${tick}</div>
                </div>
            </div>`;
        },

        esc(str) {
            if (!str) return '';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML.replace(/\n/g, '<br>');
        },

        playSound() {
            try {
                const a = new Audio('{{ asset('assets/sounds/notify.wav') }}');
                a.volume = 0.5;
                a.play().catch(() => {});
            } catch (e) {}
        },
    };
}
</script>

</x-app-layout>
