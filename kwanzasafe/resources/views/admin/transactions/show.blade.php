<x-app-layout>

@php
    $client = $transaction->user ?? \App\Models\User::find($transaction->user_id);

    $messages = \App\Models\ChatMessage::with('sender')
        ->where('transaction_id', $transaction->id)
        ->orderBy('created_at', 'asc')
        ->get();

    \App\Models\ChatMessage::where('transaction_id', $transaction->id)
        ->where('sender_id', '!=', auth()->id())
        ->where('is_read', false)
        ->update(['is_read' => true]);

    $currentAdminId = auth()->id();
    $receiptUrl = $receipt ? ks_file($receipt->file_path) : null;
    $isReceiptImage = $receipt ? ks_is_image($receipt->file_path) : false;
@endphp

<style>
body { background:#f8fafc; }
.atx-topbar { background:#0f172a; color:white; padding:0.875rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.atx-topbar__back { color:#94a3b8; text-decoration:none; font-size:0.8rem; font-weight:600; display:flex; align-items:center; gap:0.5rem; }
.atx-topbar__back:hover { color:white; }
.atx-topbar__logo { height:28px; filter:brightness(0) invert(1); }
.atx-topbar__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; }
.atx-topbar__ref { font-family:'JetBrains Mono',monospace; font-size:0.7rem; background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px; color:#94a3b8; }

.atx-container { max-width:1400px; margin:0 auto; padding:1.5rem; }
.atx-flash { padding:0.875rem 1.125rem; border-radius:12px; font-size:0.85rem; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem; }
.atx-flash.success { background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; }
.atx-flash.error { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

/* STATUS BANNER */
.atx-status-banner { background:white; border-radius:16px; border:1px solid #e2e8f0; padding:1.25rem 1.5rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.atx-status-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.atx-status-icon.pending,
.atx-status-icon.negotiating      { background:#fef3c7; color:#d97706; }
.atx-status-icon.awaiting_payment,
.atx-status-icon.processing       { background:#dbeafe; color:#1e40af; }
.atx-status-icon.payment_received { background:#d1fae5; color:#059669; }
.atx-status-icon.aoa_sent         { background:#ecfdf5; color:#065f46; }
.atx-status-icon.completed        { background:#d1fae5; color:#065f46; }
.atx-status-icon.cancelled,
.atx-status-icon.expired          { background:#fee2e2; color:#991b1b; }
.atx-status-label { font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#94a3b8; margin-bottom:2px; }
.atx-status-value { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:#0f172a; }
.atx-status-amount { margin-left:auto; text-align:right; }
.atx-status-amount__label { font-size:0.65rem; color:#94a3b8; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; }
.atx-status-amount__value { font-family:'Syne',sans-serif; font-size:1.25rem; font-weight:800; color:#0f172a; }
.atx-status-amount__kz { font-size:0.75rem; color:#065f46; font-weight:700; }

/* GRID */
.atx-grid { display:grid; grid-template-columns:1fr 1.2fr; gap:1.25rem; }
@media(max-width:1024px) { .atx-grid { grid-template-columns:1fr; } }

.atx-card { background:white; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; margin-bottom:1rem; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.atx-card__head { padding:0.875rem 1.125rem; border-bottom:1px solid #f1f5f9; background:#fcfcfd; display:flex; align-items:center; justify-content:space-between; }
.atx-card__title { font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#64748b; }
.atx-card__body { padding:1.25rem; }

/* CLIENT CARD */
.atx-client { padding:1.25rem; display:flex; align-items:center; gap:1rem; border-bottom:1px solid #f1f5f9; }
.atx-client__avatar { width:56px; height:56px; border-radius:50%; background:#d1fae5; color:#064e3b; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:1.25rem; overflow:hidden; flex-shrink:0; border:2px solid #a7f3d0; }
.atx-client__info { flex:1; min-width:0; }
.atx-client__name { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; color:#0f172a; }
.atx-client__email { font-size:0.75rem; color:#64748b; margin-top:2px; }
.atx-client__tags { display:flex; gap:4px; margin-top:6px; flex-wrap:wrap; }
.atx-tag { font-size:0.6rem; font-weight:800; padding:2px 7px; border-radius:20px; text-transform:uppercase; letter-spacing:0.05em; }
.atx-tag.ok { background:#d1fae5; color:#065f46; }
.atx-tag.warn { background:#fef3c7; color:#92400e; }
.atx-tag.info { background:#dbeafe; color:#1e40af; }
.atx-client__kyc-link { font-size:0.7rem; font-weight:700; color:#065f46; text-decoration:none; white-space:nowrap; }
.atx-client__kyc-link:hover { color:#064e3b; }

/* DATAROW */
.atx-datarow { padding:0.75rem 1.125rem; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; gap:0.75rem; font-size:0.8rem; }
.atx-datarow:last-child { border-bottom:none; }
.atx-datarow__label { color:#94a3b8; font-weight:500; }
.atx-datarow__value { color:#0f172a; font-weight:700; text-align:right; }
.atx-datarow__value strong { color:#065f46; }

/* RECEIPT */
.atx-receipt-wrap { background:#f8fafc; border:2px solid #e2e8f0; border-radius:12px; padding:1rem; text-align:center; }
.atx-receipt-wrap img { max-width:100%; max-height:360px; border-radius:8px; cursor:zoom-in; box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.atx-receipt-pdf { padding:1.5rem; }
.atx-receipt-pdf a { display:inline-flex; align-items:center; gap:0.5rem; background:#0f172a; color:white; padding:0.75rem 1.25rem; border-radius:10px; text-decoration:none; font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem; text-transform:uppercase; }
.atx-receipt-empty { color:#94a3b8; padding:1.5rem; font-size:0.85rem; }

/* ACTIONS */
.atx-actions { border-radius:16px; padding:1.5rem; color:white; margin-top:1rem; }
.atx-actions.step-request  { background:linear-gradient(135deg,#1e40af,#2563eb); }
.atx-actions.step-payment  { background:linear-gradient(135deg,#047857,#065f46); }
.atx-actions.step-aoa      { background:linear-gradient(135deg,#064e3b,#047857); }
.atx-actions.step-waiting  { background:#f0fdf4; border:2px solid #a7f3d0; color:#065f46; }
.atx-actions__title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:800; margin-bottom:0.375rem; }
.atx-actions__sub { font-size:0.75rem; opacity:0.75; margin-bottom:1rem; line-height:1.5; }
.atx-actions.step-waiting .atx-actions__sub { opacity:0.8; }
.atx-btn-action { width:100%; color:white; border:none; padding:1rem; border-radius:12px; font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:filter 0.18s; }
.atx-btn-action:hover { filter:brightness(1.12); }
.atx-btn-action.blue    { background:#3b82f6; }
.atx-btn-action.green   { background:#10b981; }
.atx-btn-action.emerald { background:#059669; }
.atx-btn-override { width:100%; margin-top:0.875rem; background:rgba(0,0,0,0.15); border:1px solid rgba(255,255,255,0.25); color:rgba(255,255,255,0.9); padding:0.625rem; border-radius:10px; font-family:'Syne',sans-serif; font-weight:700; font-size:0.75rem; cursor:pointer; text-transform:uppercase; letter-spacing:0.04em; }
.atx-btn-override:hover { background:rgba(0,0,0,0.3); }
.atx-completed { background:#d1fae5; color:#065f46; padding:1rem; border-radius:12px; text-align:center; font-weight:700; font-family:'Syne',sans-serif; border:2px solid #a7f3d0; }
.atx-cancelled { background:#fee2e2; color:#991b1b; padding:1rem; border-radius:12px; text-align:center; font-weight:700; font-family:'Syne',sans-serif; border:2px solid #fca5a5; margin-top:1rem; }

/* SYSTEM MESSAGE */
.atx-sys-msg { text-align:center; padding:0.5rem 1rem; margin:0.5rem auto; max-width:80%; }
.atx-sys-msg__text { display:inline-block; background:#f0fdf4; border:1px solid rgba(0,157,68,0.15); color:#065f46; font-size:0.72rem; font-weight:600; padding:0.375rem 0.875rem; border-radius:999px; line-height:1.4; }

/* =============== CHAT =============== */
.atx-chat-wrap { background:white; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.03); display:flex; flex-direction:column; height:calc(100vh - 200px); min-height:600px; }
@media(max-width:1024px) { .atx-chat-wrap { height:600px; min-height:auto; } }

.atx-chat-header { padding:1rem 1.25rem; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; gap:0.75rem; background:linear-gradient(135deg,#0f172a,#1e293b); color:white; }
.atx-chat-header__avatar { width:40px; height:40px; border-radius:50%; background:#10b981; color:white; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; flex-shrink:0; overflow:hidden; }
.atx-chat-header__name { font-family:'Syne',sans-serif; font-size:0.95rem; font-weight:800; }
.atx-chat-header__sub { font-size:0.7rem; color:#10b981; font-weight:600; display:flex; align-items:center; gap:0.375rem; }
.atx-chat-header__sub::before { content:''; width:6px; height:6px; background:#10b981; border-radius:50%; box-shadow:0 0 6px #10b981; animation:pulse 2s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

.atx-chat-body { flex:1; padding:1.25rem; overflow-y:auto; background:#fcfcfd; }
.atx-chat-body::-webkit-scrollbar { width:6px; }
.atx-chat-body::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }

.atx-msg { display:flex; gap:0.625rem; margin-bottom:1rem; max-width:85%; }
.atx-msg.mine { margin-left:auto; flex-direction:row-reverse; }
.atx-msg__avatar { width:32px; height:32px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.7rem; color:white; }
.atx-msg.mine .atx-msg__avatar { background:#0f172a; }
.atx-msg.client .atx-msg__avatar { background:#065f46; }
.atx-msg__content { display:flex; flex-direction:column; }
.atx-msg.mine .atx-msg__content { align-items:flex-end; }
.atx-msg__bubble { padding:0.625rem 0.875rem; border-radius:14px; font-size:0.875rem; line-height:1.45; word-wrap:break-word; }
.atx-msg.mine .atx-msg__bubble { background:#0f172a; color:white; border-bottom-right-radius:4px; }
.atx-msg.client .atx-msg__bubble { background:#ecfdf5; color:#0f172a; border-bottom-left-radius:4px; border:1px solid #a7f3d0; }
.atx-msg__meta { font-size:0.62rem; color:#94a3b8; margin-top:3px; display:flex; align-items:center; gap:0.375rem; }
.atx-msg.mine .atx-msg__meta { flex-direction:row-reverse; }
.atx-msg__image { max-width:200px; max-height:200px; border-radius:10px; margin-top:4px; cursor:zoom-in; display:block; }
.atx-msg__file { display:inline-flex; align-items:center; gap:0.375rem; background:rgba(255,255,255,0.1); padding:0.5rem 0.75rem; border-radius:8px; text-decoration:none; color:inherit; font-weight:600; font-size:0.75rem; margin-top:4px; }
.atx-msg.client .atx-msg__file { background:white; border:1px solid #e2e8f0; color:#0f172a; }

.atx-chat-empty { text-align:center; padding:3rem 1rem; color:#94a3b8; font-size:0.85rem; }

/* QUICK REPLIES */
.atx-quick-replies { padding:0.75rem 1rem; border-top:1px solid #e2e8f0; background:#f8fafc; display:flex; gap:0.375rem; flex-wrap:wrap; }
.atx-qr-btn { background:white; border:1px solid #e2e8f0; padding:0.375rem 0.75rem; border-radius:20px; cursor:pointer; font-size:0.7rem; font-weight:600; color:#64748b; transition:all 0.15s; }
.atx-qr-btn:hover { background:#065f46; color:white; border-color:#065f46; }

/* CHAT FORM */
.atx-chat-form { padding:0.875rem 1rem; background:white; border-top:1px solid #e2e8f0; }
.atx-chat-form__inner { display:flex; gap:0.5rem; align-items:flex-end; }
.atx-chat-form__textarea { flex:1; border:2px solid #e2e8f0; border-radius:12px; padding:0.625rem 0.875rem; font-family:'DM Sans',sans-serif; font-size:0.875rem; resize:none; outline:none; max-height:120px; min-height:40px; }
.atx-chat-form__textarea:focus { border-color:#065f46; }
.atx-chat-form__btn { width:40px; height:40px; border-radius:50%; background:#065f46; color:white; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.atx-chat-form__btn:hover { background:#064e3b; }
.atx-chat-form__attach { width:40px; height:40px; border-radius:50%; background:#f1f5f9; color:#64748b; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; position:relative; }
.atx-chat-form__attach input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; }
.atx-chat-form__preview { background:#ecfdf5; border:1px solid #a7f3d0; padding:0.375rem 0.625rem; border-radius:8px; font-size:0.7rem; color:#065f46; display:flex; align-items:center; gap:0.375rem; margin-bottom:0.5rem; }
.atx-chat-form__preview button { background:none; border:none; color:#dc2626; cursor:pointer; margin-left:auto; font-weight:700; }
</style>

<div class="atx-topbar">
    <a href="{{ route('admin.transactions.index') }}" class="atx-topbar__back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Transações
    </a>
    <div style="display:flex;align-items:center;gap:0.625rem;">
        <img src="{{ asset('assets/images/logos/logo1.png') }}" class="atx-topbar__logo" alt="KwanzaSafe" onerror="this.style.display='none'">
        <span class="atx-topbar__title">Operação</span>
    </div>
    <span class="atx-topbar__ref">#{{ $transaction->reference_id }}</span>
</div>

<div class="atx-container">

    @if(session('success'))
        <div class="atx-flash success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="atx-flash error">⚠ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="atx-flash error">⚠ {{ $errors->first() }}</div>
    @endif

    {{-- STATUS --}}
    <div class="atx-status-banner">
        <div class="atx-status-icon {{ $transaction->status }}">
            @php $st = $transaction->status; @endphp
            @if($st === 'completed' || $st === 'payment_received')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($st === 'cancelled' || $st === 'expired')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            @elseif($st === 'aoa_sent')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            @elseif($st === 'awaiting_payment' || $st === 'processing')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            @else
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @endif
        </div>
        <div>
            <div class="atx-status-label">Estado</div>
            @php
                $labels = [
                    'pending'          => 'A Aguardar Resposta',
                    'negotiating'      => 'Em Negociação',
                    'awaiting_payment' => 'A Aguardar Pagamento',
                    'payment_received' => 'Pagamento Confirmado',
                    'processing'       => 'Comprovativo em Análise',
                    'aoa_sent'         => 'AOA Enviados',
                    'completed'        => 'Concluída',
                    'cancelled'        => 'Cancelada',
                    'expired'          => 'Expirada',
                ];
            @endphp
            <div class="atx-status-value">{{ $labels[$transaction->status] ?? ucfirst($transaction->status) }}</div>
        </div>
        <div class="atx-status-amount">
            <div class="atx-status-amount__label">Operação</div>
            <div class="atx-status-amount__value">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</div>
            <div class="atx-status-amount__kz">→ {{ number_format($transaction->amount_received, 0, ',', '.') }} Kz</div>
        </div>
    </div>

    <div class="atx-grid">

        {{-- ============ COLUNA ESQUERDA ============ --}}
        <div>

            {{-- Cliente --}}
            <div class="atx-card">
                <div class="atx-card__head">
                    <div class="atx-card__title">👤 Cliente</div>
                    @if($client)
                        <a href="{{ route('admin.kyc.show', $client->id) }}" class="atx-client__kyc-link">Ver KYC →</a>
                    @endif
                </div>
                @if($client)
                <div class="atx-client">
                    @php $avUrl = ks_file($client->profile_photo_path); @endphp
                    <div class="atx-client__avatar">
                        @if($avUrl)
                            <img src="{{ $avUrl }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($client->full_name ?? $client->email, 0, 1)) }}
                        @endif
                    </div>
                    <div class="atx-client__info">
                        <div class="atx-client__name">{{ $client->full_name ?? 'Sem nome' }}</div>
                        <div class="atx-client__email">{{ $client->email }}</div>
                        <div class="atx-client__tags">
                            @if($client->identity_verified_at)
                                <span class="atx-tag ok">✓ KYC</span>
                            @else
                                <span class="atx-tag warn">⏳ KYC Pendente</span>
                            @endif
                            @if($client->phone_number)
                                <span class="atx-tag info">{{ $client->phone_number }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @else
                    <div class="atx-client" style="color:#94a3b8;">Cliente não encontrado</div>
                @endif
            </div>

            {{-- Detalhes da operação --}}
            <div class="atx-card">
                <div class="atx-card__head"><div class="atx-card__title">💱 Detalhes da Operação</div></div>
                <div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Referência</span>
                        <span class="atx-datarow__value" style="font-family:'JetBrains Mono',monospace;">#{{ $transaction->reference_id }}</span>
                    </div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Enviado</span>
                        <span class="atx-datarow__value">{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</span>
                    </div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Taxa Aplicada</span>
                        <span class="atx-datarow__value">1 {{ $transaction->currency_from }} = {{ number_format($transaction->rate_applied, 2, ',', '.') }} Kz</span>
                    </div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">A Pagar (AOA)</span>
                        <span class="atx-datarow__value"><strong>{{ number_format($transaction->amount_received, 2, ',', '.') }} Kz</strong></span>
                    </div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Criada</span>
                        <span class="atx-datarow__value">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Última actualização</span>
                        <span class="atx-datarow__value">{{ $transaction->updated_at->diffForHumans() }}</span>
                    </div>
                    @if($transaction->payment_received_at)
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">Pagamento recebido</span>
                        <span class="atx-datarow__value">{{ $transaction->payment_received_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($transaction->aoa_sent_at)
                    <div class="atx-datarow">
                        <span class="atx-datarow__label">AOA enviados</span>
                        <span class="atx-datarow__value">{{ $transaction->aoa_sent_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Comprovativo --}}
            <div class="atx-card">
                <div class="atx-card__head">
                    <div class="atx-card__title">📎 Comprovativo</div>
                    @if($receipt)
                        <span class="atx-tag ok">Recebido</span>
                    @else
                        <span class="atx-tag warn">Em falta</span>
                    @endif
                </div>
                <div class="atx-card__body">
                    @if($receipt && $receiptUrl)
                        <div class="atx-receipt-wrap">
                            @if($isReceiptImage)
                                <img src="{{ $receiptUrl }}" alt="Comprovativo" onclick="window.open(this.src,'_blank')">
                            @else
                                <div class="atx-receipt-pdf">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 0.75rem;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <div style="margin-bottom:0.75rem;font-size:0.85rem;color:#64748b;">Comprovativo em PDF</div>
                                    <a href="{{ $receiptUrl }}" target="_blank">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Abrir PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="atx-receipt-empty" style="text-align:center;">
                            ⏳ O cliente ainda não enviou comprovativo.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ações faseadas por estado --}}
            @php $st = $transaction->status; @endphp

            @if(in_array($st, ['pending','negotiating']))
                {{-- Passo 1: solicitar pagamento --}}
                <div class="atx-actions step-request">
                    <div class="atx-actions__title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:4px;"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Passo 1 — Solicitar Pagamento
                    </div>
                    <div class="atx-actions__sub">Informa o cliente dos dados para efectuar o pagamento e muda o estado para "A Aguardar Pagamento".</div>
                    <form method="POST" action="{{ route('admin.transaction.request_payment', $transaction->id) }}">
                        @csrf
                        <button type="submit" class="atx-btn-action blue">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Solicitar Pagamento ao Cliente
                        </button>
                    </form>
                </div>

            @elseif(in_array($st, ['awaiting_payment','processing']))
                {{-- Passo 2: confirmar pagamento recebido --}}
                <div class="atx-actions step-payment">
                    <div class="atx-actions__title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Passo 2 — Confirmar Pagamento
                    </div>
                    <div class="atx-actions__sub">Após verificar que recebeste <strong>{{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }}</strong> na conta KwanzaSafe, confirma aqui.</div>
                    <form method="POST" action="{{ route('admin.transaction.payment_received', $transaction->id) }}"
                          onsubmit="return confirm('Confirmas que recebeste {{ number_format($transaction->amount_sent, 2, ',', '.') }} {{ $transaction->currency_from }} de pagamento?');">
                        @csrf
                        <button type="submit" class="atx-btn-action green">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Confirmar Pagamento Recebido
                        </button>
                    </form>
                </div>

            @elseif($st === 'payment_received')
                {{-- Passo 3: marcar AOA enviados --}}
                <div class="atx-actions step-aoa">
                    <div class="atx-actions__title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:-2px;margin-right:4px;"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Passo 3 — Enviar Kwanzas
                    </div>
                    <div class="atx-actions__sub">Após enviares <strong>{{ number_format($transaction->amount_received, 0, ',', '.') }} Kz</strong> para o IBAN do cliente, clica aqui para o notificar e pedir confirmação.</div>
                    <form method="POST" action="{{ route('admin.transaction.aoa_sent', $transaction->id) }}"
                          onsubmit="return confirm('Confirmas o envio de {{ number_format($transaction->amount_received, 0, ',', '.') }} Kz ao cliente?');">
                        @csrf
                        <button type="submit" class="atx-btn-action emerald">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Marcar AOA como Enviados
                        </button>
                    </form>
                </div>

            @elseif($st === 'aoa_sent')
                {{-- Aguardar confirmação do cliente --}}
                <div class="atx-actions step-waiting" style="margin-top:1rem;">
                    <div class="atx-actions__title">A aguardar confirmação</div>
                    <div class="atx-actions__sub">O cliente recebeu notificação para confirmar a recepção dos Kwanzas. Esta etapa é da responsabilidade do cliente.</div>
                    <div style="font-size:0.75rem;font-weight:600;display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;color:#059669;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        AOA enviados em {{ $transaction->aoa_sent_at?->format('d/m/Y H:i') ?? 'data desconhecida' }}
                    </div>
                    <div class="atx-actions step-aoa" style="margin-top:0;padding:1rem;">
                        <div class="atx-actions__sub" style="margin-bottom:0.5rem;font-size:0.7rem;">Override de emergência — apenas se necessário</div>
                        <form method="POST" action="{{ route('admin.transaction.approve', $transaction->id) }}"
                              onsubmit="return confirm('Forçar conclusão sem confirmação do cliente. Tens a certeza?');">
                            @csrf
                            <button type="submit" class="atx-btn-override">
                                Forçar Conclusão (admin override)
                            </button>
                        </form>
                    </div>
                </div>

            @elseif($st === 'completed')
                <div class="atx-completed" style="margin-top:1rem;">
                    ✓ Operação Concluída<br>
                    <span style="font-size:0.75rem;font-weight:500;opacity:0.8;">Kwanzas libertados e confirmados pelo cliente.</span>
                    @if($transaction->client_confirmed_at)
                        <div style="font-size:0.7rem;margin-top:4px;opacity:0.7;">Confirmado em {{ $transaction->client_confirmed_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>

            @elseif(in_array($st, ['cancelled','expired']))
                <div class="atx-cancelled">
                    ✕ Operação {{ $st === 'cancelled' ? 'Cancelada' : 'Expirada' }}
                </div>
            @endif

            {{-- Cancelar (disponível enquanto a transação for reversível) --}}
            @if(in_array($st, ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing']))
            <div style="margin-top:1rem;text-align:center;">
                <form method="POST" action="{{ route('admin.transaction.cancel', $transaction->id) }}"
                      onsubmit="return confirm('Cancelar a transação #{{ $transaction->reference_id }}?\nEsta acção enviará email ao cliente e não pode ser revertida.');">
                    @csrf
                    <button type="submit" style="background:none;border:1px solid #fca5a5;color:#dc2626;padding:0.5rem 1.25rem;border-radius:8px;font-family:\'Syne\',sans-serif;font-weight:700;font-size:0.7rem;cursor:pointer;text-transform:uppercase;letter-spacing:0.06em;">
                        ✕ Cancelar Transação
                    </button>
                </form>
            </div>
            @endif

        </div>

        {{-- ============ COLUNA DIREITA: CHAT ============ --}}
        <div>
            <div class="atx-chat-wrap" x-data="adminChat()">
                <div class="atx-chat-header">
                    <div class="atx-chat-header__avatar">
                        @if($client && ks_file($client->profile_photo_path))
                            <img src="{{ ks_file($client->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ $client ? strtoupper(substr($client->full_name ?? $client->email, 0, 1)) : '?' }}
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="atx-chat-header__name">{{ $client?->full_name ?? 'Cliente' }}</div>
                        <div class="atx-chat-header__sub">Chat da transação #{{ $transaction->reference_id }}</div>
                    </div>
                </div>

                <div class="atx-chat-body" id="atx-chat-body">
                    @if($messages->isEmpty())
                        <div class="atx-chat-empty">
                            <div style="font-size:2.5rem;margin-bottom:0.75rem;opacity:0.5;">💬</div>
                            <div>Ainda não há mensagens nesta conversa.</div>
                            <div style="font-size:0.75rem;margin-top:0.375rem;opacity:0.7;">Usa as respostas rápidas abaixo ou escreve diretamente.</div>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php
                                $isSystem = is_null($msg->sender_id);
                                $isMine   = !$isSystem && $msg->sender_id === $currentAdminId;
                                $msgUrl   = ks_file($msg->file_path);
                                $isMsgImg = ks_is_image($msg->file_path);
                            @endphp
                            @if($isSystem)
                                <div class="atx-sys-msg">
                                    <span class="atx-sys-msg__text">{{ $msg->message_text }}</span>
                                </div>
                            @else
                                <div class="atx-msg {{ $isMine ? 'mine' : 'client' }}">
                                    <div class="atx-msg__avatar">{{ $isMine ? 'KS' : 'CL' }}</div>
                                    <div class="atx-msg__content">
                                        <div class="atx-msg__bubble">
                                            @if($msg->message_text){{ $msg->message_text }}@endif
                                            @if($msg->file_path && $msgUrl)
                                                @if($isMsgImg)
                                                    <img src="{{ $msgUrl }}" alt="anexo" class="atx-msg__image" onclick="window.open(this.src,'_blank')">
                                                @else
                                                    <a href="{{ $msgUrl }}" target="_blank" class="atx-msg__file">📎 Ver Documento</a>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="atx-msg__meta">
                                            <span>{{ $msg->created_at->format('d/m H:i') }}</span>
                                            @if($isMine && $msg->is_read)
                                                <span style="color:#10b981;">✓✓</span>
                                            @elseif($isMine)
                                                <span>✓</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Respostas Rápidas --}}
                <div class="atx-quick-replies">
                    <button type="button" class="atx-qr-btn" @click="setQuick('Comprovativo recebido. Estamos a validar, aguarda por favor.')">📋 Validando</button>
                    <button type="button" class="atx-qr-btn" @click="setQuick('O teu comprovativo está ilegível. Podes reenviar com melhor qualidade?')">🔍 Reenviar</button>
                    <button type="button" class="atx-qr-btn" @click="setQuick('Os Kwanzas foram libertados. Deves recebê-los em minutos.')">✅ Aprovado</button>
                    <button type="button" class="atx-qr-btn" @click="setQuick('Precisamos de confirmar a conta bancária de destino. Podes verificar o IBAN registado?')">🏦 IBAN</button>
                </div>

                <form method="POST" action="{{ ks_route('admin.chat.send', $transaction->id, url('/admin/transaction/'.$transaction->id.'/chat')) }}" enctype="multipart/form-data" class="atx-chat-form" x-data="{attachmentName:''}">
                    @csrf
                    <template x-if="attachmentName">
                        <div class="atx-chat-form__preview">
                            <span>📎</span>
                            <span x-text="attachmentName"></span>
                            <button type="button" @click="attachmentName='';$refs.fileInput.value=''">✕</button>
                        </div>
                    </template>

                    <div class="atx-chat-form__inner">
                        <label class="atx-chat-form__attach" title="Anexar">
                            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" x-ref="fileInput" @change="attachmentName=$event.target.files[0]?.name||''">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </label>

                        <textarea
    name="message_text"
    id="atx-textarea"
    class="atx-chat-form__textarea"
    placeholder="Escreve a mensagem para o cliente..."
    rows="1"
    data-ks-persist="chat_admin_{{ $transaction->reference_id }}"
    @input="$event.target.style.height='auto';$event.target.style.height=Math.min($event.target.scrollHeight,120)+'px';"></textarea>

                        <button type="submit" class="atx-chat-form__btn" title="Enviar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function adminChat() {
    return {
        lastId: {{ $messages->max('id') ?? 0 }},
        _timer: null,

        init() {
            this.$nextTick(() => this.scrollBottom());
            this._timer = setInterval(() => this.poll(), 5000);
        },

        destroy() { clearInterval(this._timer); },

        scrollBottom() {
            const b = document.getElementById('atx-chat-body');
            if (b) b.scrollTop = b.scrollHeight;
        },

        setQuick(text) {
            const t = document.querySelector('#atx-textarea');
            if (t) { t.value = text; t.focus(); }
        },

        async poll() {
            try {
                const r = await fetch('{{ route('admin.transaction.poll', $transaction->id) }}?after=' + this.lastId, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                if (!r.ok) return;
                const { messages } = await r.json();
                if (!messages || !messages.length) return;
                const hasNewFromClient = messages.some(m => !m.is_mine && !m.is_system);
                this.appendMessages(messages);
                this.lastId = messages[messages.length - 1].id;
                this.scrollBottom();
                if (hasNewFromClient) this.playSound();
            } catch (e) {}
        },

        appendMessages(messages) {
            const body = document.getElementById('atx-chat-body');
            const empty = body.querySelector('.atx-chat-empty');
            if (empty) empty.remove();
            messages.forEach(msg => {
                const w = document.createElement('div');
                w.innerHTML = this.buildMessage(msg).trim();
                body.appendChild(w.firstElementChild);
            });
        },

        buildMessage(msg) {
            if (msg.is_system) {
                return `<div class="atx-sys-msg"><span class="atx-sys-msg__text">${this.esc(msg.text)}</span></div>`;
            }
            const side     = msg.is_mine ? 'mine' : 'client';
            const initials = msg.is_mine ? 'KS' : 'CL';
            let body = '';
            if (msg.text) body += this.esc(msg.text);
            if (msg.file_url) {
                if (msg.is_image) {
                    body += `<img src="${msg.file_url}" class="atx-msg__image" onclick="window.open(this.src,'_blank')">`;
                } else {
                    body += `<a href="${msg.file_url}" target="_blank" class="atx-msg__file">📎 Ver Documento</a>`;
                }
            }
            const tick = msg.is_mine
                ? `<span style="color:${msg.is_read ? '#10b981' : '#94a3b8'};">${msg.is_read ? '✓✓' : '✓'}</span>`
                : '';
            return `<div class="atx-msg ${side}">
                <div class="atx-msg__avatar">${initials}</div>
                <div class="atx-msg__content">
                    <div class="atx-msg__bubble">${body}</div>
                    <div class="atx-msg__meta"><span>${msg.time}</span>${tick}</div>
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
