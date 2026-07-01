<x-app-layout>

@php
    $user = auth()->user();
    $allowedTabs = ['home','history','iban','support','profile'];
    $initialTab = in_array(request()->query('tab'), $allowedTabs)
                  ? request()->query('tab')
                  : 'home';

    // Saudação dinâmica
    $hour = now()->setTimezone('Africa/Luanda')->hour;
    $greeting = $hour < 12 ? 'Bom dia' : ($hour < 19 ? 'Boa tarde' : 'Boa noite');
    $firstName = $user->full_name ? explode(' ', trim($user->full_name))[0] : explode('@', $user->email)[0];

    // Status KYC
    $kycStatus = [
        'data_complete'   => $user->full_name && $user->bi_number && $user->birth_date,
        'phone_verified'  => (bool) $user->phone_verified_at,
        'document_sent'   => (bool) $user->identity_document_path,
        'photo_sent'      => (bool) $user->profile_photo_path,
        'approved'        => (bool) $user->identity_verified_at,
    ];

    // Stats por moeda (totais separados)
    $totalsByCurrency = $transactions
        ->where('status', 'completed')
        ->groupBy('currency_from')
        ->map(fn($g) => $g->sum('amount_received'));

    $hasAnyTransaction = $transactions->count() > 0;
    $pendingCount = $transactions->whereNotIn('status', ['completed','cancelled','expired'])->count();
    $completedCount = $transactions->where('status', 'completed')->count();
    $totalKzReceived = $transactions->where('status', 'completed')->sum('amount_received');

    // Taxas reais para a calculadora
    $currencyMeta = [
        'EUR'  => ['symbol' => '€',  'flag' => '🇪🇺', 'color' => '#003399'],
        'BRL'  => ['symbol' => 'R$', 'flag' => '🇧🇷', 'color' => '#009c3b'],
        'USDT' => ['symbol' => '$',  'flag' => '₮',  'color' => '#26a17b'],
    ];
    $ratesForJs = $rates->map(fn($r) => array_merge(
        ['id' => $r->id, 'code' => $r->currency_from, 'rate' => (float) $r->rate],
        $currencyMeta[$r->currency_from] ?? ['symbol' => '$', 'flag' => '💱', 'color' => '#009d44']
    ))->values();

    // Destinos de recepção (beneficiários + carteiras) para a calculadora
    $walletLabels = ['bybit' => 'Bybit', 'binance' => 'Binance', 'redotpay' => 'RedotPay'];
    $destinationsForJs = collect();
    foreach (($beneficiaries ?? []) as $b) {
        $destinationsForJs->push(['type' => 'bank', 'id' => $b->id, 'title' => $b->bank_name, 'subtitle' => $b->iban, 'icon' => 'bank']);
    }
    foreach (($wallets ?? []) as $w) {
        $destinationsForJs->push(['type' => $w->provider, 'id' => $w->id, 'title' => $walletLabels[$w->provider] ?? ucfirst($w->provider), 'subtitle' => $w->identifier, 'icon' => 'wallet']);
    }
    $destinationsForJs = $destinationsForJs->values();

    // TX para JS (dashboard.blade só)
    $txForJs = $transactions->map(fn($tx) => [
        'reference_id'    => $tx->reference_id,
        'currency_from'   => $tx->currency_from,
        'amount_sent'     => (float) $tx->amount_sent,
        'amount_received' => (float) $tx->amount_received,
        'rate_applied'    => (float) $tx->rate_applied,
        'status'          => $tx->status,
        'created_at'      => $tx->created_at->toDateTimeString(),
        'created_diff'    => $tx->created_at->diffForHumans(),
        'expires_at'      => $tx->expires_at?->toDateTimeString(),
        'url'         => route('transaction.show', $tx->reference_id),
        'receipt_url' => $tx->status === 'completed'
                         ? route('transaction.receipt', $tx->reference_id)
                         : null,
    ])->values();
@endphp

@push('head')
<title>Painel — KwanzaSafe</title>
<style>
    body { background:#fafafa; }
    .dc * { box-sizing:border-box; }
    .dc { font-family:'DM Sans',sans-serif; min-height:100dvh; padding-bottom:80px; }
    @media(min-width:1024px) { .dc { padding-bottom:0; padding-left:260px; } }

    .dc-font-display { font-family:'Syne',sans-serif; }
    .dc-font-mono { font-family:'JetBrains Mono',monospace; }

    /* ============ TOPBAR (mobile) ============ */
    .dc-topbar {
        position:sticky; top:0; z-index:40;
        background:#fff;
        border-bottom:1px solid #e5e5e5;
        padding:0.75rem 1rem;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
    }
    @media(min-width:1024px) { .dc-topbar { display:none; } }

    .dc-topbar__menu {
        background:none; border:none; cursor:pointer; padding:0.5rem;
        border-radius:10px; transition:background 0.15s;
        display:flex; align-items:center; justify-content:center;
    }
    .dc-topbar__menu:hover { background:#f5f5f5; }

    .dc-topbar__brand { display:flex; align-items:center; gap:0.5rem; flex:1; min-width:0; text-decoration:none; }
    .dc-topbar__logo { height:30px; width:auto; }

    .dc-topbar__bell {
        position:relative; flex-shrink:0;
        width:38px; height:38px; border-radius:50%;
        display:flex; align-items:center; justify-content:center;
        color:#404040; background:transparent; border:none; cursor:pointer;
        text-decoration:none; transition:background 0.15s;
    }
    .dc-topbar__bell:hover { background:#f5f5f5; }
    .dc-topbar__bell-badge {
        position:absolute; top:4px; right:4px; min-width:16px; height:16px; padding:0 4px;
        background:#dc2626; color:#fff; border-radius:999px;
        font-size:0.6rem; font-weight:700; line-height:16px; text-align:center;
        border:2px solid #fff;
    }
    .dc-topbar__avatar {
        width:36px; height:36px; border-radius:50%; flex-shrink:0;
        background:#d1f2e0; color:#007a34;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem;
        overflow:hidden;
        cursor:pointer;
        border:2px solid transparent;
        transition:border-color 0.15s;
    }
    .dc-topbar__avatar:hover { border-color:#009d44; }
    .dc-topbar__avatar img { width:100%; height:100%; object-fit:cover; }

    /* ============ SIDEBAR (DESKTOP) ============ */
    .dc-sidebar {
        position:fixed; top:0; left:0; bottom:0;
        width:260px;
        background:#0a0a0a;
        color:white;
        z-index:50;
        display:flex; flex-direction:column;
        transform:translateX(-100%);
        transition:transform 0.3s ease;
    }
    @media(min-width:1024px) {
        .dc-sidebar { transform:none; }
    }
    .dc-sidebar.is-open { transform:translateX(0); }

    .dc-sidebar__head {
        padding:1.35rem 1.25rem 1.1rem;
        border-bottom:1px solid rgba(255,255,255,0.08);
    }
    .dc-sidebar__logo { display:inline-flex; align-items:center; text-decoration:none; }
    .dc-logo { width:auto; display:none; }
    .dc-logo--full { height:42px; display:block; }
    .dc-logo--icon { height:36px; }
    @media (max-width:640px) {
        .dc-logo--full { display:none; }
        .dc-logo--icon { display:block; }
    }
    .dc-sidebar__sub {
        font-size:0.55rem; font-weight:700; letter-spacing:0.16em;
        text-transform:uppercase; color:rgba(255,255,255,0.42); margin-top:0.6rem;
    }

    .dc-sidebar__user {
        padding:1rem 1.25rem;
        border-bottom:1px solid rgba(255,255,255,0.08);
        display:flex; align-items:center; gap:0.625rem;
    }
    .dc-sidebar__avatar {
        width:38px; height:38px; border-radius:50%; flex-shrink:0;
        background:#009d44; color:#fff;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem;
        overflow:hidden;
    }
    .dc-sidebar__avatar img { width:100%; height:100%; object-fit:cover; }

    .dc-sidebar__nav {
        flex:1;
        padding:0.875rem 0.625rem;
        display:flex; flex-direction:column; gap:2px;
        overflow-y:auto;
    }
    .dc-sidebar__label {
        font-size:0.55rem; font-weight:700; letter-spacing:0.15em;
        text-transform:uppercase; color:rgba(255,255,255,0.3);
        padding:0.75rem 0.75rem 0.25rem;
    }

    .dc-nav {
        display:flex; align-items:center; gap:0.75rem;
        padding:0.625rem 0.75rem; border-radius:10px;
        cursor:pointer; color:rgba(255,255,255,0.6);
        font-size:0.825rem; font-weight:500; text-decoration:none;
        border:1px solid transparent;
        transition:all 0.15s;
        position:relative;
        background:none;
        text-align:left;
        width:100%;
    }
    .dc-nav:hover { background:rgba(255,255,255,0.06); color:white; }
    .dc-nav.is-active {
        background:rgba(0,157,68,0.15);
        color:#009d44;
        border-color:rgba(0,157,68,0.25);
    }
    .dc-nav.is-active::before {
        content:'';
        position:absolute; left:-0.625rem; top:50%; transform:translateY(-50%);
        width:3px; height:16px; background:#009d44; border-radius:0 3px 3px 0;
    }
    .dc-nav__icon { width:18px; height:18px; flex-shrink:0; opacity:0.7; }
    .dc-nav.is-active .dc-nav__icon { opacity:1; }
    .dc-nav__badge {
        margin-left:auto;
        background:#dc2626; color:white;
        font-size:0.6rem; font-weight:800;
        padding:2px 7px; border-radius:10px;
        min-width:18px; text-align:center;
    }

    .dc-sidebar__foot {
        padding:1rem;
        border-top:1px solid rgba(255,255,255,0.08);
        font-size:0.55rem; color:rgba(255,255,255,0.4);
        letter-spacing:0.08em; text-transform:uppercase;
        font-weight:700; text-align:center;
    }

    /* ============ OVERLAY (mobile sidebar) ============ */
    .dc-overlay {
        position:fixed; inset:0;
        background:rgba(0,0,0,0.5);
        backdrop-filter:blur(2px);
        z-index:45;
        opacity:0; pointer-events:none;
        transition:opacity 0.3s;
    }
    .dc-overlay.is-open { opacity:1; pointer-events:auto; }
    @media(min-width:1024px) { .dc-overlay { display:none !important; } }

    /* ============ CONTENT ============ */
    .dc-main { padding:1rem; }
    @media(min-width:768px) { .dc-main { padding:1.5rem; max-width:1200px; margin:0 auto; } }

    /* ============ FLASH ============ */
    .dc-flash {
        padding:0.875rem 1.125rem; border-radius:12px;
        font-size:0.85rem; font-weight:500;
        margin-bottom:1rem;
        display:flex; align-items:flex-start; gap:0.5rem;
    }
    .dc-flash.success { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; }
    .dc-flash.error { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; }

    /* ============ HERO (HOME) ============ */
    .dc-hero {
        background:#009d44;
        border-radius:16px;
        padding:1.5rem;
        color:white;
        margin-bottom:1.25rem;
        position:relative;
    }
    .dc-hero__greeting { font-size:0.85rem; opacity:0.9; font-weight:500; }
    .dc-hero__name {
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:1.5rem; line-height:1.2; margin-top:2px;
        position:relative;
    }
    .dc-hero__sub {
        font-size:0.8rem; opacity:0.85; margin-top:6px;
        position:relative; line-height:1.5;
    }

    /* ============ CARTÃO DE SALDO ============ */
    .dc-balance {
        background:#fff; border:1px solid #e5e5e5; border-radius:16px;
        padding:1.25rem 1.5rem; margin-bottom:1rem;
    }
    .dc-balance__top { display:flex; align-items:center; justify-content:space-between; }
    .dc-balance__label { font-size:0.8rem; color:#737373; font-weight:600; }
    .dc-balance__eye { background:none; border:none; cursor:pointer; color:#a3a3a3; padding:4px; display:flex; align-items:center; }
    .dc-balance__eye:hover { color:#404040; }
    .dc-balance__amount { font-family:'Syne',sans-serif; font-weight:800; font-size:2rem; color:#0a0d0b; line-height:1.1; margin-top:0.5rem; letter-spacing:-0.02em; }
    .dc-balance__cur { font-size:1rem; color:#737373; font-weight:600; margin-left:0.35rem; }
    .dc-balance__chip { display:inline-flex; align-items:center; gap:0.375rem; margin-top:0.75rem; font-size:0.72rem; font-weight:600; padding:0.25rem 0.6rem; border-radius:999px; }
    .dc-balance__chip.ok { background:#f0faf4; color:#007a34; }
    .dc-balance__chip.pending { background:#fffbeb; color:#b45309; }

    /* ============ PESQUISA (histórico) ============ */
    .dc-search { display:flex; align-items:center; gap:0.5rem; background:#fff; border:1px solid #e5e5e5; border-radius:12px; padding:0 0.875rem; height:44px; margin-bottom:0.75rem; }
    .dc-search:focus-within { border-color:#009d44; box-shadow:0 0 0 3px rgba(0,157,68,0.1); }
    .dc-search input { flex:1; border:none; outline:none; background:none; font-family:'DM Sans',sans-serif; font-size:0.9rem; color:#0a0d0b; height:100%; }
    .dc-search input::placeholder { color:#a3a3a3; }
    .dc-search__clear { background:none; border:none; cursor:pointer; color:#a3a3a3; padding:2px; display:flex; align-items:center; }
    .dc-search__clear:hover { color:#404040; }

    /* ============ KYC STATUS BANNER ============ */
    .dc-kyc-banner {
        border-radius:12px;
        padding:1rem 1.125rem;
        margin-bottom:1rem;
        display:flex; align-items:center; gap:0.75rem;
        text-decoration:none; color:inherit;
        background:#fff; border:1px solid #e5e5e5;
    }
    .dc-kyc-banner.warn { background:#fffbeb; border-color:#fde68a; }
    .dc-kyc-banner.success { background:#f0faf4; border-color:#a7f3d0; }
    .dc-kyc-banner.danger { background:#fef2f2; border-color:#fca5a5; }
    .dc-kyc-banner__icon {
        width:40px; height:40px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; color:white;
    }
    .dc-kyc-banner.warn .dc-kyc-banner__icon { background:#f59e0b; }
    .dc-kyc-banner.success .dc-kyc-banner__icon { background:#009d44; }
    .dc-kyc-banner.danger .dc-kyc-banner__icon { background:#dc2626; }
    .dc-kyc-banner__title { font-family:'Syne',sans-serif; font-weight:700; font-size:0.875rem; }
    .dc-kyc-banner__sub { font-size:0.75rem; margin-top:1px; opacity:0.85; }
    .dc-kyc-banner__cta {
        background:#000; color:white;
        padding:0.45rem 0.8rem;
        border-radius:8px;
        font-family:'DM Sans',sans-serif;
        font-size:0.75rem; font-weight:600;
        flex-shrink:0;
    }

    /* ============ TOTALS GRID (separado por moeda) ============ */
    .dc-totals { display:grid; gap:0.75rem; margin-bottom:1.25rem; }
    @media(min-width:480px) { .dc-totals { grid-template-columns:repeat(3, 1fr); } }

    .dc-total-card {
        background:white;
        border-radius:14px;
        padding:1rem 1.125rem;
        border:1px solid #e5e5e5;
        box-shadow:0 1px 3px rgba(0,0,0,0.03);
        position:relative;
        overflow:hidden;
    }
    .dc-total-card::before {
        content:''; position:absolute;
        top:0; left:0; right:0; height:3px;
    }
    .dc-total-card.eur::before { background:#003399; }
    .dc-total-card.brl::before { background:#009c3b; }
    .dc-total-card.usdt::before { background:#26a17b; }

    .dc-total-card__head { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem; }
    .dc-total-card__flag { font-size:1.25rem; line-height:1; }
    .dc-total-card__label {
        font-size:0.7rem; font-weight:700; color:#737373;
        text-transform:uppercase; letter-spacing:0.08em;
    }
    .dc-total-card__value {
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:1.25rem; letter-spacing:-0.02em; color:#000;
    }
    .dc-total-card__currency {
        font-size:0.65rem; color:#a3a3a3; margin-top:1px;
        font-family:'JetBrains Mono',monospace;
    }
    .dc-total-card.empty .dc-total-card__value { color:#a3a3a3; font-weight:600; }

    /* ============ ACTION BUTTONS ============ */
    .dc-actions { display:grid; gap:0.625rem; grid-template-columns:1fr 1fr; margin-bottom:1.25rem; }
    .dc-action-btn {
        background:white; border:1px solid #e5e5e5; border-radius:14px;
        padding:1rem 0.875rem;
        cursor:pointer; text-decoration:none; color:inherit;
        display:flex; align-items:center; gap:0.625rem;
        transition:all 0.15s;
        text-align:left;
    }
    .dc-action-btn:hover { border-color:#009d44; transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,0.06); }
    .dc-action-btn.primary { background:#000; color:white; border-color:#000; }
    .dc-action-btn.primary:hover { background:#009d44; border-color:#009d44; }
    .dc-action-btn__icon {
        width:36px; height:36px; border-radius:10px;
        background:#f0faf4; color:#009d44;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .dc-action-btn.primary .dc-action-btn__icon { background:rgba(255,255,255,0.15); color:white; }
    .dc-action-btn__text { flex:1; min-width:0; }
    .dc-action-btn__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.825rem; }
    .dc-action-btn__sub { font-size:0.65rem; opacity:0.7; margin-top:1px; }

    /* ============ TX LIST (recent on home) ============ */
    .dc-section { margin-bottom:1.25rem; }
    .dc-section__head {
        display:flex; align-items:center; justify-content:space-between;
        margin-bottom:0.75rem;
    }
    .dc-section__title {
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem;
    }
    .dc-section__link {
        font-size:0.7rem; font-weight:700;
        color:#009d44; text-decoration:none;
        background:none; border:none; cursor:pointer;
    }
    .dc-section__link:hover { color:#007a34; }

    .dc-tx-card {
        background:white; border:1px solid #e5e5e5;
        border-radius:14px;
        padding:0.875rem 1.125rem;
        margin-bottom:0.5rem;
        display:flex; align-items:center; gap:0.875rem;
        text-decoration:none; color:inherit;
        transition:all 0.15s;
    }
    .dc-tx-card:hover { border-color:#009d44; }

    .dc-tx-card__icon {
        width:38px; height:38px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; font-size:1.1rem;
    }
    .dc-tx-card__icon.completed { background:#d1f2e0; color:#007a34; }
    .dc-tx-card__icon.pending,
    .dc-tx-card__icon.negotiating,
    .dc-tx-card__icon.awaiting_payment,
    .dc-tx-card__icon.processing { background:#fef3c7; color:#92400e; }
    .dc-tx-card__icon.payment_received,
    .dc-tx-card__icon.aoa_sent { background:#d1fae5; color:#065f46; }
    .dc-tx-card__icon.cancelled,
    .dc-tx-card__icon.expired { background:#fee2e2; color:#991b1b; }

    .dc-tx-card__info { flex:1; min-width:0; }
    .dc-tx-card__ref {
        font-family:'JetBrains Mono',monospace;
        font-size:0.7rem; font-weight:700; color:#525252;
    }
    .dc-tx-card__amount {
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:0.9rem; margin-top:1px;
    }
    .dc-tx-card__when { font-size:0.65rem; color:#a3a3a3; margin-top:1px; }

    .dc-tx-card__status {
        font-size:0.6rem; font-weight:800;
        padding:3px 8px; border-radius:20px;
        text-transform:uppercase; letter-spacing:0.05em;
        flex-shrink:0;
    }
    .dc-tx-card__status.completed { background:#d1f2e0; color:#007a34; }
    .dc-tx-card__status.pending,
    .dc-tx-card__status.negotiating,
    .dc-tx-card__status.awaiting_payment,
    .dc-tx-card__status.processing { background:#fef3c7; color:#92400e; }
    .dc-tx-card__status.payment_received,
    .dc-tx-card__status.aoa_sent { background:#d1fae5; color:#065f46; }
    .dc-tx-card__status.cancelled,
    .dc-tx-card__status.expired { background:#fee2e2; color:#991b1b; }

    /* ============ EMPTY STATES ============ */
    .dc-empty {
        background:white;
        border:2px dashed #d4d4d4;
        border-radius:16px;
        padding:2.5rem 1.5rem;
        text-align:center;
    }
    .dc-empty__icon { font-size:2.5rem; margin-bottom:0.5rem; }
    .dc-empty__text {
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:0.95rem; color:#525252;
    }
    .dc-empty__sub { font-size:0.8rem; color:#a3a3a3; margin-top:3px; }
    .dc-empty__cta {
        margin-top:1rem; display:inline-flex; align-items:center; gap:0.5rem;
        background:#009d44; color:white;
        padding:0.625rem 1rem; border-radius:10px;
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;
        text-decoration:none; border:none; cursor:pointer;
    }

    /* ============ HISTORICO ============ */
    .dc-filter-pills { display:flex; gap:5px; flex-wrap:wrap; margin-bottom:0.875rem; }
    .dc-pill {
        padding:0.375rem 0.75rem;
        background:white; border:1px solid #e5e5e5;
        border-radius:8px;
        font-family:'Syne',sans-serif; font-size:0.7rem; font-weight:700;
        color:#737373;
        cursor:pointer;
    }
    .dc-pill.is-active { background:#000; color:white; border-color:#000; }

    /* ============ IBAN CARDS ============ */
    .dc-iban-card {
        background:white; border:1px solid #e5e5e5; border-radius:14px;
        padding:1rem 1.125rem;
        margin-bottom:0.625rem;
    }
    .dc-iban-card__head { display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem; }
    .dc-iban-card__bank { font-family:'Syne',sans-serif; font-weight:800; font-size:0.875rem; }
    .dc-iban-card__holder { font-size:0.7rem; color:#737373; margin-top:1px; }
    .dc-iban-card__num {
        font-family:'JetBrains Mono',monospace; font-size:0.8rem; font-weight:700;
        background:#f5f5f5; padding:8px 12px; border-radius:8px;
        margin-top:8px;
        word-break:break-all;
    }
    .dc-iban-card__del {
        background:#fee2e2; color:#991b1b;
        border:none; padding:5px 8px; border-radius:6px;
        font-family:'Syne',sans-serif; font-size:0.65rem; font-weight:800;
        text-transform:uppercase; cursor:pointer;
    }

    /* ============ MODAL IBAN ============ */
    .dc-modal-overlay {
        position:fixed; inset:0;
        background:rgba(0,0,0,0.6);
        backdrop-filter:blur(4px);
        z-index:80;
        display:flex; align-items:center; justify-content:center;
        padding:1rem;
    }
    .dc-modal {
        background:white; border-radius:18px;
        padding:1.5rem;
        max-width:440px; width:100%;
        max-height:90dvh; overflow-y:auto;
        box-shadow:0 30px 80px rgba(0,0,0,0.3);
    }
    .dc-modal__title {
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.1rem;
        margin-bottom:0.25rem;
    }
    .dc-modal__sub { font-size:0.85rem; color:#737373; margin-bottom:1.25rem; }
    .dc-modal__field { margin-bottom:0.875rem; }
    .dc-modal__label {
        display:block; font-size:0.7rem; font-weight:700;
        text-transform:uppercase; letter-spacing:0.08em;
        color:#525252; margin-bottom:0.375rem;
    }
    .dc-modal__input {
        width:100%; padding:11px 14px;
        border:2px solid #e5e5e5; border-radius:10px;
        font-size:0.9rem; outline:none;
        background:#fafafa;
    }
    .dc-modal__input:focus { border-color:#009d44; background:white; }
    .dc-modal__actions { display:flex; gap:0.5rem; margin-top:1rem; }
    .dc-modal__btn {
        flex:1; padding:0.75rem; border-radius:10px; border:none;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.8rem;
        text-transform:uppercase; letter-spacing:0.05em; cursor:pointer;
    }
    .dc-modal__btn-cancel { background:#f5f5f5; color:#525252; }
    .dc-modal__btn-save { background:#009d44; color:white; }

    /* ============ SUPPORT ============ */
    .dc-support-card {
        background:white; border:1px solid #e5e5e5;
        border-radius:14px;
        padding:1rem 1.125rem;
        margin-bottom:0.625rem;
        display:flex; align-items:center; gap:0.875rem;
        text-decoration:none; color:inherit;
    }
    .dc-support-card:hover { border-color:#009d44; }
    .dc-support-card__icon {
        width:42px; height:42px; border-radius:12px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .dc-support-card__icon.wa { background:#25d366; color:white; }
    .dc-support-card__icon.email { background:#dbeafe; color:#1e40af; }
    .dc-support-card__icon.faq { background:#fef3c7; color:#92400e; }
    .dc-support-card__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.875rem; }
    .dc-support-card__sub { font-size:0.7rem; color:#737373; margin-top:1px; }

    /* ============ PROFILE ============ */
    .dc-profile-hero {
        background:#111111;
        border-radius:16px; padding:1.5rem;
        color:white; text-align:center;
        margin-bottom:1rem;
        position:relative;
    }
    .dc-profile-hero__avatar {
        width:80px; height:80px; border-radius:50%;
        background:#009d44; color:white;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.6rem;
        margin:0 auto 0.625rem;
        overflow:hidden;
        border:3px solid rgba(255,255,255,0.2);
        position:relative;
    }
    .dc-profile-hero__avatar img { width:100%; height:100%; object-fit:cover; }
    .dc-profile-hero__name {
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.1rem;
        position:relative;
    }
    .dc-profile-hero__email {
        font-size:0.8rem; opacity:0.7; margin-top:1px;
        position:relative;
    }

    .dc-profile-row {
        display:flex; justify-content:space-between; align-items:center;
        padding:0.875rem 1.125rem;
        background:white; border-radius:12px;
        margin-bottom:0.375rem;
        border:1px solid #e5e5e5;
        text-decoration:none; color:inherit;
        cursor:pointer;
    }
    .dc-profile-row:hover { border-color:#009d44; }
    .dc-profile-row__label { font-size:0.85rem; font-weight:600; }
    .dc-profile-row__icon { color:#a3a3a3; }
    .dc-profile-row.danger .dc-profile-row__label { color:#dc2626; }

    /* ============ BOTTOM NAV (mobile only) ============ */
    .dc-bottom {
        position:fixed; bottom:0; left:0; right:0;
        background:white;
        border-top:1px solid #e5e5e5;
        padding:0.5rem 0.25rem;
        display:flex; justify-content:space-around;
        z-index:30;
        padding-bottom:env(safe-area-inset-bottom, 0.5rem);
    }
    @media(min-width:1024px) { .dc-bottom { display:none; } }

    .dc-bottom-btn {
        flex:1;
        background:none; border:none; cursor:pointer;
        padding:0.5rem 0.25rem;
        display:flex; flex-direction:column; align-items:center; gap:3px;
        color:#737373;
        font-size:0.65rem; font-weight:600;
        position:relative;
        transition:color 0.15s;
        font-family:'DM Sans',sans-serif;
    }
    .dc-bottom-btn.is-active { color:#009d44; }
    .dc-bottom-btn.is-active::after {
        content:'';
        position:absolute;
        top:0; left:50%; transform:translateX(-50%);
        width:24px; height:3px;
        background:#009d44;
        border-radius:0 0 3px 3px;
    }
    .dc-bottom-btn__icon { width:22px; height:22px; }
    .dc-bottom-btn__badge {
        position:absolute; top:2px; right:25%;
        background:#dc2626; color:white;
        font-size:0.55rem; font-weight:800;
        padding:1px 5px; border-radius:10px;
        min-width:14px; text-align:center;
        border:2px solid white;
    }

    [x-cloak] { display:none !important; }
</style>
@endpush

<div class="dc"
     x-data="clientDashboard()"
     x-init="init()"
     @keydown.escape.window="sidebarOpen = false">

    {{-- ============ TOPBAR (mobile) ============ --}}
    <div class="dc-topbar">
        <button class="dc-topbar__menu" @click="sidebarOpen = true" aria-label="Abrir menu">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
            </svg>
        </button>
        <a href="{{ url('/') }}" class="dc-topbar__brand" aria-label="KwanzaSafe — início">
            <img src="{{ asset('assets/images/logos/logo-isotipo.png') }}" alt="KwanzaSafe" class="dc-topbar__logo">
        </a>
        <a href="{{ route('notifications.index') }}" class="dc-topbar__bell" aria-label="Notificações">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @if(($unreadNotifications ?? 0) > 0)
                <span class="dc-topbar__bell-badge">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
            @endif
        </a>
        <button class="dc-topbar__avatar" @click="setTab('profile')" aria-label="Perfil">
            @if(ks_file($user->display_photo_path))
                <img loading="lazy" decoding="async" src="{{ ks_file($user->display_photo_path) }}" alt="">
            @else
                {{ strtoupper(substr($firstName, 0, 1)) }}
            @endif
        </button>
    </div>

    {{-- ============ OVERLAY (mobile sidebar) ============ --}}
    <div class="dc-overlay" :class="{'is-open': sidebarOpen}" @click="sidebarOpen = false"></div>

    {{-- ============ SIDEBAR (desktop fixa, mobile hamburger) ============ --}}
    <aside class="dc-sidebar" :class="{'is-open': sidebarOpen}">
        <div class="dc-sidebar__head">
            <a href="{{ url('/') }}" class="dc-sidebar__logo" aria-label="KwanzaSafe — início">
                <img src="{{ asset('assets/images/logos/logo-icone.png') }}" alt="KwanzaSafe" class="dc-logo dc-logo--icon">
                <img src="{{ asset('assets/images/logos/logo-isotipo-white.png') }}" alt="KwanzaSafe" class="dc-logo dc-logo--full">
            </a>
            <div class="dc-sidebar__sub">Painel do Cliente</div>
        </div>

        <div class="dc-sidebar__user">
            <div class="dc-sidebar__avatar">
                @if(ks_file($user->display_photo_path))
                    <img loading="lazy" decoding="async" src="{{ ks_file($user->display_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($firstName, 0, 1)) }}
                @endif
            </div>
            <div style="min-width:0; flex:1;">
                <div style="font-weight:600; font-size:0.85rem; line-height:1.2; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $user->full_name ?? $firstName }}</div>
                <div style="font-size:0.65rem; color:#a3a3a3;">{{ Str::limit($user->email, 24) }}</div>
            </div>
        </div>

        <nav class="dc-sidebar__nav">
            <div class="dc-sidebar__label">Navegação</div>
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'home'}" @click="setTab('home')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Início
            </button>
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'history'}" @click="setTab('history')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Histórico
                <template x-if="transactions.length > 0">
                    <span class="dc-nav__badge" style="background:#737373;" x-text="transactions.length"></span>
                </template>
            </button>
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'iban'}" @click="setTab('iban')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
                IBAN
            </button>
            <a href="{{ route('notifications.index') }}" class="dc-nav">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notificações
                @if(($unreadNotifications ?? 0) > 0)
                    <span class="dc-nav__badge">{{ $unreadNotifications }}</span>
                @endif
            </a>
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'support'}" @click="setTab('support')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Suporte
                @if(($unreadMessages ?? 0) > 0)
                    <span class="dc-nav__badge">{{ $unreadMessages }}</span>
                @endif
            </button>
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'profile'}" @click="setTab('profile')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Perfil
            </button>

            <div class="dc-sidebar__label">Conta</div>
            @if($user->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="dc-nav">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Painel Admin
            </a>
            @endif
            <a href="{{ route('settings') }}" class="dc-nav">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Definições
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dc-nav" style="color:#fca5a5;">
                    <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </button>
            </form>
        </nav>

        <div class="dc-sidebar__foot">KwanzaSafe © 2026</div>
    </aside>

    {{-- ============ MAIN CONTENT ============ --}}
    <main class="dc-main">

        @if(session('success'))
            <div class="dc-flash success">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="dc-flash error">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12" stroke-linecap="round"/></svg>
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        {{-- ============ HOME ============ --}}
        <div x-show="activeTab === 'home'" x-transition.opacity>

            {{-- HERO com saudação --}}
            <div class="dc-hero">
                <div class="dc-hero__greeting">{{ $greeting }},</div>
                <div class="dc-hero__name">{{ $firstName }}</div>
                <div class="dc-hero__sub">
                    @if($kycStatus['approved'])
                        Conta verificada. Pronta para câmbio internacional.
                    @else
                        Completa a tua verificação para começar a transacionar.
                    @endif
                </div>
            </div>

            {{-- CARTÃO DE SALDO --}}
            <div class="dc-balance" x-data="{ hide: false }">
                <div class="dc-balance__top">
                    <span class="dc-balance__label">Saldo da conta</span>
                    <button type="button" class="dc-balance__eye" @click="hide = !hide" :aria-label="hide ? 'Mostrar saldo' : 'Esconder saldo'">
                        <svg x-show="!hide" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="hide" x-cloak width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
                <div class="dc-balance__amount">
                    <span x-show="!hide">{{ number_format($user->balance ?? 0, 2, ',', '.') }}</span>
                    <span x-show="hide" x-cloak>••••••</span><span class="dc-balance__cur">AOA</span>
                </div>
                @if($user->is_fully_verified)
                    <span class="dc-balance__chip ok">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Conta verificada
                    </span>
                @else
                    <span class="dc-balance__chip pending">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
                        Verificação pendente
                    </span>
                @endif
            </div>

            {{-- ============ CONTINUAR NA APP (Android) ============ --}}
            @php $hasApk = file_exists(base_path('../public_html/downloads/kwanzasafe.apk')); @endphp
            <div style="background:#111111;border-radius:16px;padding:1.25rem 1.5rem;margin-bottom:1rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;position:relative;">
                <div style="width:52px;height:52px;border-radius:14px;background:rgba(0,180,84,.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;">
                    <svg width="26" height="26" fill="none" stroke="#1be37a" stroke-width="2" viewBox="0 0 24 24"><rect x="7" y="2" width="10" height="20" rx="2.5"/><line x1="11" y1="18" x2="13" y2="18" stroke-linecap="round"/></svg>
                </div>
                <div style="flex:1;min-width:200px;color:#fff;position:relative;">
                    <div style="font-family:'Syne',sans-serif;font-weight:800;font-size:1.05rem;">Continua no telemóvel</div>
                    <div style="font-size:.85rem;color:#a7c4b5;margin-top:.2rem;">Leva o KwanzaSafe contigo — câmbio, chat, KYC e notificações na app Android.</div>
                </div>
                <div style="display:flex;gap:.6rem;flex-wrap:wrap;position:relative;">
                    <a href="https://play.google.com/store/apps/details?id=com.kwanzasafe.app" target="_blank" rel="noopener"
                       style="display:inline-flex;align-items:center;gap:.5rem;background:#fff;color:#0a0d0b;font-family:'Syne',sans-serif;font-weight:700;font-size:.85rem;padding:.7rem 1.1rem;border-radius:999px;text-decoration:none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3.6 2.3 13 11.7l-9.4 9.4A1.7 1.7 0 0 1 3 19.9V4.1c0-.7.3-1.3.6-1.8Zm11 11 2.6 2.6-9.7 5.5 7.1-8.1Zm0-3.6L7.5 2.6l9.7 5.5-2.6 2.6ZM18.9 9.6l2.7 1.5c1 .6 1 1.9 0 2.5l-2.7 1.5L16 12l2.9-2.4Z"/></svg>
                        Google Play
                    </a>
                    @if($hasApk)
                        <a href="{{ asset('downloads/kwanzasafe.apk') }}" download
                           style="display:inline-flex;align-items:center;gap:.45rem;background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.22);font-weight:600;font-size:.82rem;padding:.7rem 1rem;border-radius:999px;text-decoration:none;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l4-4m-4 4l-4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Baixar APK
                        </a>
                    @endif
                </div>
            </div>

            {{-- KYC BANNER --}}
            @if(!auth()->user()->email_verified_at)
                <a href="{{ route('otp.email.verify') }}" class="dc-kyc-banner warn">
                    <div class="dc-kyc-banner__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="dc-kyc-banner__title">Verifica o teu email</div>
                        <div class="dc-kyc-banner__sub">Confirma {{ auth()->user()->email }} para continuar</div>
                    </div>
                    <span class="dc-kyc-banner__cta">Verificar →</span>
                </a>
            @elseif(!$kycStatus['approved'])
                <a href="{{ route('verify.data') ? '#' : '#' }}" class="dc-kyc-banner warn" @click.prevent="setTab('profile')">
                    <div class="dc-kyc-banner__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="dc-kyc-banner__title">Verificação KYC pendente</div>
                        <div class="dc-kyc-banner__sub">Completa para começar a transacionar</div>
                    </div>
                    <span class="dc-kyc-banner__cta">Completar →</span>
                </a>
            @else
                <div class="dc-kyc-banner success">
                    <div class="dc-kyc-banner__icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="dc-kyc-banner__title">Conta Verificada ✓</div>
                        <div class="dc-kyc-banner__sub">Identidade aprovada. Pronto para enviar.</div>
                    </div>
                </div>
            @endif
            
                            {{-- ============ CALCULADORA DE CÂMBIO ============ --}}
                @push('head')
                <style>
                    .dc-calc input[type=number]::-webkit-outer-spin-button,
                    .dc-calc input[type=number]::-webkit-inner-spin-button { -webkit-appearance:none; margin:0; }
                    .dc-calc input[type=number] { -moz-appearance:textfield; appearance:textfield; }
                    .dc-fxcard { background:#fff; border:1px solid #e9ebee; border-radius:18px; padding:1.15rem; box-shadow:0 2px 12px rgba(15,23,42,0.05); }
                    .dc-fx { display:flex; flex-direction:column; gap:0.7rem; }
                    .dc-fxblock { background:#f6f7f9; border:1.5px solid #ebedf0; border-radius:14px; padding:0.85rem 1rem; transition:border-color .15s, background .15s; }
                    .dc-fxblock:focus-within { border-color:#009d44; background:#fff; }
                    .dc-fxblock--recv { background:#f0faf4; border-color:#bce6cd; }
                    .dc-fxlabel { font-family:'DM Sans',sans-serif; font-size:0.62rem; font-weight:800; letter-spacing:0.1em; text-transform:uppercase; color:#8a9099; margin-bottom:0.55rem; }
                    .dc-fxblock--recv .dc-fxlabel { color:#018a3b; }
                    .dc-fxrow { display:flex; align-items:center; gap:0.75rem; }
                    .dc-amount { flex:1; min-width:0; border:none; background:transparent; outline:none; text-align:right; font-family:'Syne',sans-serif; font-weight:800; font-size:1.7rem; color:#0f172a; letter-spacing:-0.02em; padding:0; }
                    .dc-amount::placeholder { color:#cfd3d8; }
                    .dc-recv { flex:1; min-width:0; text-align:right; font-family:'Syne',sans-serif; font-weight:800; font-size:1.7rem; letter-spacing:-0.02em; line-height:1.1; overflow:hidden; }
                    .dc-recv.zero { color:#9aa4ae; }
                    .dc-recv.has { color:#0f172a; }
                    .dc-cc { display:inline-flex; align-items:center; gap:7px; background:#fff; border:1.5px solid #e5e7eb; border-radius:999px; padding:5px 11px 5px 6px; cursor:pointer; flex-shrink:0; transition:border-color .15s, box-shadow .15s; }
                    .dc-cc:hover { border-color:#cfd3d8; box-shadow:0 2px 8px rgba(15,23,42,0.06); }
                    .dc-cc--static { cursor:default; border-color:#bce6cd; background:#fff; }
                    .dc-ccico { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-weight:800; font-size:0.7rem; flex-shrink:0; }
                    .dc-cccode { font-family:'Syne',sans-serif; font-weight:800; font-size:0.82rem; color:#0f172a; }
                    .dc-rate { display:flex; align-items:center; gap:0.65rem; padding:0.1rem; }
                    .dc-rateline { flex:1; height:1px; background:#ebedf0; }
                    .dc-ratepill { display:inline-flex; align-items:center; gap:6px; font-family:'DM Sans',sans-serif; font-size:0.72rem; color:#5b6470; white-space:nowrap; }
                    .dc-ratearrow { width:28px; height:28px; border-radius:50%; background:#009d44; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 10px rgba(0,157,68,0.28); }
                    .dc-dd { position:absolute; right:0; top:calc(100% + 6px); z-index:50; background:#fff; border:1px solid #e9ebee; border-radius:14px; box-shadow:0 16px 40px rgba(15,23,42,0.16); padding:6px; min-width:185px; }
                    .dc-dditem { width:100%; display:flex; align-items:center; gap:10px; background:none; border:none; border-radius:10px; padding:9px 10px; cursor:pointer; text-align:left; }
                    .dc-dditem:hover { background:#f5f6f8; }
                    .dc-dditem.sel { background:#f1faf4; }
                    .dc-destbtn { width:100%; display:flex; align-items:center; gap:0.7rem; background:#f6f7f9; border:1.5px solid #e5e7eb; border-radius:14px; padding:0.7rem 0.85rem; cursor:pointer; text-align:left; transition:border-color .15s; }
                    .dc-destbtn:hover { border-color:#cfd3d8; }
                    .dc-destico { width:38px; height:38px; border-radius:11px; background:#d9f3e3; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
                    .dc-desttt { display:block; font-family:'DM Sans',sans-serif; font-weight:700; font-size:0.84rem; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
                    .dc-destsub { display:block; font-size:0.7rem; color:#737b86; font-family:'JetBrains Mono',monospace; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
                    .dc-cta { display:flex; align-items:center; gap:0.85rem; background:#f1faf4; border:1.5px dashed #6cc28d; border-radius:14px; padding:0.9rem 1rem; text-decoration:none; transition:background .15s, border-color .15s; }
                    .dc-cta:hover { background:#e6f7ec; border-color:#009d44; }
                    .dc-ctaplus { width:40px; height:40px; border-radius:12px; background:#009d44; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
                    .dc-ctabody { flex:1; min-width:0; }
                    .dc-ctatt { display:block; font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem; color:#0f172a; }
                    .dc-ctasub { display:block; font-family:'DM Sans',sans-serif; font-size:0.7rem; color:#5b6470; margin-top:2px; line-height:1.35; }
                    .dc-go { width:100%; background:#009d44; color:#fff; border:none; padding:0.9rem; border-radius:12px; font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.925rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:background .15s, opacity .15s; }
                    .dc-go:hover:not(:disabled) { background:#007a34; }
                    .dc-go:disabled { opacity:0.45; cursor:not-allowed; }
                    .dc-err { font-family:'DM Sans',sans-serif; font-size:0.72rem; color:#dc2626; font-weight:600; text-align:center; }
                    .dc-foot { font-family:'DM Sans',sans-serif; font-size:0.66rem; color:#a0a6ae; text-align:center; line-height:1.5; }
                    @media (max-width:360px){ .dc-amount, .dc-recv { font-size:1.4rem; } }
                </style>
                @endpush
                <div class="dc-calc" x-data="ksCalculator()" style="margin-bottom: 1.25rem;">

                    {{-- Header da calculadora --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; gap:0.5rem;">
                        <div>
                            <div class="dc-section__title">Iniciar Câmbio</div>
                            <div style="font-size:0.7rem; color:#737373; margin-top:1px;">
                                Calcula e inicia a tua transação diretamente
                            </div>
                        </div>
                        <div style="display:inline-flex; align-items:center; gap:6px; background:#d1f2e0; border-radius:999px; padding:5px 11px; flex-shrink:0;">
                            <span class="ks-pulse" style="width:7px; height:7px; border-radius:50%; background:#009d44;"></span>
                            <span style="font-family:'DM Sans',sans-serif; font-weight:600; font-size:0.68rem; color:#007a34;">Taxa ao vivo</span>
                        </div>
                    </div>

                    <div class="dc-fxcard">

                        {{-- ===== CONVERSOR (Envias → Recebes) ===== --}}
                        {{-- ENVIAS --}}
                        <div class="dc-fxblock">
                            <div class="dc-fxlabel">Envias</div>
                            <div class="dc-fxrow">
                                <div style="position:relative; flex-shrink:0;">
                                    <button type="button" class="dc-cc" @click="currOpen = !currOpen" aria-label="Escolher moeda de envio">
                                        <span class="dc-ccico" :style="`background:${activeCurrency.color}1a; color:${activeCurrency.color}`" x-text="activeCurrency.symbol"></span>
                                        <span class="dc-cccode" x-text="activeCurrency.code"></span>
                                        <svg width="13" height="13" fill="none" stroke="#9aa4ae" stroke-width="2.5" viewBox="0 0 24 24" :style="currOpen?'transform:rotate(180deg);transition:.2s':'transition:.2s'"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                    <div x-show="currOpen" x-cloak @click.outside="currOpen = false" class="dc-dd">
                                        <template x-for="curr in currencies" :key="curr.code">
                                            <button type="button" class="dc-dditem" :class="{ sel: activeCurrency.code === curr.code }" @click="setCurrency(curr)">
                                                <span class="dc-ccico" :style="`background:${curr.color}1a; color:${curr.color}`" x-text="curr.symbol"></span>
                                                <span style="flex:1; font-family:'Syne',sans-serif; font-weight:700; font-size:0.85rem; color:#0f172a;" x-text="curr.code"></span>
                                                <svg x-show="activeCurrency.code === curr.code" width="15" height="15" fill="none" stroke="#009d44" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <input type="number" inputmode="decimal" step="0.01" min="0" class="dc-amount"
                                       x-model="amount" placeholder="0,00" aria-label="Valor a enviar">
                            </div>
                        </div>

                        {{-- DIVISOR COM A TAXA --}}
                        <div class="dc-rate">
                            <span class="dc-ratearrow"><svg width="15" height="15" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14m0 0l-6-6m6 6l6-6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <span class="dc-ratepill">1&nbsp;<strong x-text="activeCurrency.code" style="color:#0f172a;"></strong>&nbsp;=&nbsp;<strong style="color:#009d44; font-family:'JetBrains Mono',monospace;" x-text="formatNumber(activeCurrency.rate)"></strong>&nbsp;Kz</span>
                            <span class="dc-rateline"></span>
                        </div>

                        {{-- RECEBES --}}
                        <div class="dc-fxblock dc-fxblock--recv">
                            <div class="dc-fxrow" style="margin-bottom:0.55rem;">
                                <div class="dc-fxlabel" style="margin:0; flex:1;">Recebes</div>
                                <span class="dc-cc dc-cc--static">
                                    <span class="dc-ccico" style="background:#d9f3e3; color:#007a34;">Kz</span>
                                    <span class="dc-cccode" style="color:#007a34;">AOA</span>
                                </span>
                            </div>
                            <div class="dc-recv" :class="amountNum > 0 ? 'has' : 'zero'">
                                <span x-text="amountNum > 0 ? formatNumber(received, 0) : 'Introduz um valor'"></span><span x-show="amountNum > 0" style="font-family:'Syne',sans-serif; font-weight:700; color:#009d44; font-size:0.95rem;">&nbsp;Kz</span>
                            </div>
                        </div>

                        {{-- SELETOR DE DESTINO + BOTÃO INICIAR --}}
                        @if($kycStatus['approved'])
                            {{-- Onde receber (com destinos) --}}
                            <div x-show="destinations.length > 0" style="margin-top:0.9rem; position:relative;">
                                <div class="dc-fxlabel">Receber em</div>
                                <button type="button" class="dc-destbtn" @click="destOpen = !destOpen">
                                    <span class="dc-destico" x-text="selectedDest?.icon === 'bank' ? '🏦' : '👛'"></span>
                                    <span style="flex:1; min-width:0;">
                                        <span class="dc-desttt" x-text="selectedDest?.title"></span>
                                        <span class="dc-destsub" x-text="selectedDest?.subtitle"></span>
                                    </span>
                                    <svg width="16" height="16" fill="none" stroke="#9aa4ae" stroke-width="2.5" viewBox="0 0 24 24" :style="destOpen ? 'transform:rotate(180deg);transition:.2s' : 'transition:.2s'"><path d="M19 9l-7 7-7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div x-show="destOpen" x-cloak @click.outside="destOpen = false" class="dc-dd" style="left:0; right:0; max-height:260px; overflow-y:auto;">
                                    <template x-for="d in destinations" :key="`${d.type}:${d.id}`">
                                        <button type="button" class="dc-dditem" :class="{ sel: destKey === `${d.type}:${d.id}` }" @click="setDest(d)">
                                            <span class="dc-destico" style="width:30px; height:30px; border-radius:8px; font-size:0.9rem;" x-text="d.icon === 'bank' ? '🏦' : '👛'"></span>
                                            <span style="flex:1; min-width:0;">
                                                <span class="dc-desttt" style="font-size:0.8rem;" x-text="d.title"></span>
                                                <span class="dc-destsub" style="font-size:0.66rem;" x-text="d.subtitle"></span>
                                            </span>
                                            <svg x-show="destKey === `${d.type}:${d.id}`" width="16" height="16" fill="none" stroke="#009d44" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </template>
                                    <a href="{{ route('dashboard', ['tab' => 'iban']) }}" style="display:flex; align-items:center; gap:0.4rem; justify-content:center; padding:0.625rem; color:#009d44; font-weight:700; font-size:0.75rem; text-decoration:none; border-top:1px solid #f0f0f0; margin-top:4px;">+ Adicionar destino</a>
                                </div>
                            </div>

                            {{-- Sem destinos: CTA (bloco próprio, robusto) --}}
                            <a x-show="destinations.length === 0" href="{{ route('dashboard', ['tab' => 'iban']) }}" class="dc-cta" style="margin-top:0.9rem;">
                                <span class="dc-ctaplus"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg></span>
                                <span class="dc-ctabody">
                                    <span class="dc-ctatt">Adiciona onde queres receber</span>
                                    <span class="dc-ctasub">Precisas de um IBAN ou carteira para receberes os Kwanzas.</span>
                                </span>
                                <svg width="18" height="18" fill="none" stroke="#009d44" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>

                            {{-- Mensagem de validação (mín/máx) --}}
                            <div x-show="errorText" x-cloak class="dc-err" style="margin-top:0.6rem;" x-text="errorText"></div>

                            {{-- Botão iniciar (desativado até ser válido) --}}
                            <form method="POST" action="{{ route('transaction.store') }}" x-show="destinations.length > 0" style="margin-top:0.9rem;">
                                @csrf
                                <input type="hidden" name="moeda" :value="activeCurrency.id">
                                <input type="hidden" name="valor_enviar" :value="amountNum">
                                <input type="hidden" name="destino_tipo" :value="selectedDest?.type">
                                <input type="hidden" name="destino_id" :value="selectedDest?.id">
                                <button type="submit" class="dc-go" :disabled="!canSubmit">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    Iniciar Transação
                                </button>
                            </form>
                        @elseif(!auth()->user()->email_verified_at)
                            <div style="margin-top:0.875rem; background:#fef3c7; border:1px solid #f59e0b; border-radius:12px; padding:0.875rem; text-align:center; font-size:0.8rem; color:#78350f;">
                                <strong>Verifica o teu email</strong> para iniciar transações.
                                <a href="{{ route('otp.email.verify') }}" style="display:block; margin-top:6px; color:#92400e; font-weight:700; font-size:0.75rem;">Verificar email →</a>
                            </div>
                        @else
                            <div style="margin-top:0.875rem; background:#fef3c7; border:1px solid #f59e0b; border-radius:12px; padding:0.875rem; text-align:center; font-size:0.8rem; color:#78350f;">
                                <strong>Verificação KYC pendente.</strong> Completa para iniciar transações.
                                <button type="button" @click="setTab('profile')" style="display:block; width:100%; margin-top:6px; background:none; border:none; color:#92400e; font-weight:700; font-size:0.75rem; cursor:pointer; text-align:center;">
                                    Completar verificação →
                                </button>
                            </div>
                        @endif

                        {{-- INFO TAXAS --}}
                        <div style="font-size:0.65rem; color:#a3a3a3; text-align:center; margin-top:0.75rem; line-height:1.5;">
                            ℹ️ Taxas actualizadas em tempo real · Mínimo 10 · Máximo 50.000
                        </div>

                    </div>
                </div>

            {{-- TOTAIS POR MOEDA --}}
            <div class="dc-totals">
                @php
                    $eurTotal = $totalsByCurrency['EUR'] ?? 0;
                    $brlTotal = $totalsByCurrency['BRL'] ?? 0;
                    $usdtTotal = ($totalsByCurrency['USDT'] ?? 0) + ($totalsByCurrency['USDC'] ?? 0);
                @endphp
                <div class="dc-total-card eur {{ $eurTotal == 0 ? 'empty' : '' }}">
                    <div class="dc-total-card__head">
                        <span class="dc-total-card__flag">🇪🇺</span>
                        <span class="dc-total-card__label">EUR Recebido</span>
                    </div>
                    <div class="dc-total-card__value">{{ number_format($eurTotal, 0, ',', '.') }}</div>
                    <div class="dc-total-card__currency">AOA · de Euros</div>
                </div>
                <div class="dc-total-card brl {{ $brlTotal == 0 ? 'empty' : '' }}">
                    <div class="dc-total-card__head">
                        <span class="dc-total-card__flag">🇧🇷</span>
                        <span class="dc-total-card__label">BRL Recebido</span>
                    </div>
                    <div class="dc-total-card__value">{{ number_format($brlTotal, 0, ',', '.') }}</div>
                    <div class="dc-total-card__currency">AOA · de Reais</div>
                </div>
                <div class="dc-total-card usdt {{ $usdtTotal == 0 ? 'empty' : '' }}">
                    <div class="dc-total-card__head">
                        <span class="dc-total-card__flag">₮</span>
                        <span class="dc-total-card__label">USDT Recebido</span>
                    </div>
                    <div class="dc-total-card__value">{{ number_format($usdtTotal, 0, ',', '.') }}</div>
                    <div class="dc-total-card__currency">AOA · de Stablecoins</div>
                </div>
            </div>

            {{-- AÇÕES RÁPIDAS --}}
            <div class="dc-actions">
                <button type="button" class="dc-action-btn primary" @click="setTab('iban')">
                    <div class="dc-action-btn__icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
                    </div>
                    <div class="dc-action-btn__text">
                        <div class="dc-action-btn__title">Cadastrar IBAN</div>
                        <div class="dc-action-btn__sub">Adicionar conta de destino</div>
                    </div>
                </button>
                <button type="button" class="dc-action-btn" @click="setTab('history')">
                    <div class="dc-action-btn__icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 8v4l3 3" stroke-linecap="round"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div class="dc-action-btn__text">
                        <div class="dc-action-btn__title">Ver Histórico</div>
                        <div class="dc-action-btn__sub">{{ count($transactions) }} transações</div>
                    </div>
                </button>
            </div>

            {{-- TX RECENTES (3 últimas) --}}
            @if($hasAnyTransaction)
                <div class="dc-section">
                    <div class="dc-section__head">
                        <div class="dc-section__title">Transações Recentes</div>
                        <button type="button" class="dc-section__link" @click="setTab('history')">Ver todas →</button>
                    </div>
                    @foreach($transactions->take(3) as $tx)
                        <a href="{{ route('transaction.show', $tx->reference_id) }}" class="dc-tx-card">
                            <div class="dc-tx-card__icon {{ $tx->status }}">
                                @if($tx->status === 'completed') ✓
                                @elseif(in_array($tx->status, ['cancelled','expired'])) ✕
                                @else ⏳ @endif
                            </div>
                            <div class="dc-tx-card__info">
                                <div class="dc-tx-card__ref">#{{ $tx->reference_id }}</div>
                                <div class="dc-tx-card__amount">{{ number_format($tx->amount_sent, 2, ',', '.') }} {{ $tx->currency_from }}</div>
                                <div class="dc-tx-card__when">{{ $tx->created_at->diffForHumans() }}</div>
                            </div>
                            <div class="dc-tx-card__status {{ $tx->status }}">{{ str_replace('_', ' ', $tx->status) }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="dc-empty">
                    <div class="dc-empty__icon">💸</div>
                    <div class="dc-empty__text">Sem transações ainda</div>
                    <div class="dc-empty__sub">Completa o teu KYC e cadastra um IBAN para começar.</div>
                </div>
            @endif

        </div>

        {{-- ============ HISTÓRICO ============ --}}
        <div x-show="activeTab === 'history'" x-transition.opacity x-cloak>
            <h2 class="dc-section__title" style="margin-bottom:0.875rem;">Histórico de Transações</h2>

            {{-- Stats strip --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.5rem;margin-bottom:1rem;">
                <div style="background:white;border:1px solid #e5e5e5;border-radius:12px;padding:0.875rem;text-align:center;">
                    <div style="font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:#0f172a;">{{ $transactions->count() }}</div>
                    <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-top:2px;">Total</div>
                </div>
                <div style="background:white;border:1px solid #e5e5e5;border-radius:12px;padding:0.875rem;text-align:center;">
                    <div style="font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;color:#064e3b;">{{ $completedCount }}</div>
                    <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-top:2px;">Concluídas</div>
                </div>
                <div style="background:#f0faf4;border:1px solid #a7f3d0;border-radius:12px;padding:0.875rem;text-align:center;">
                    <div style="font-family:'Syne',sans-serif;font-size:1.05rem;font-weight:800;color:#064e3b;line-height:1.1;">{{ number_format($totalKzReceived, 0, ',', '.') }}</div>
                    <div style="font-size:0.6rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#059669;margin-top:2px;">Kz Recebidos</div>
                </div>
            </div>

            {{-- Pesquisa --}}
            <div class="dc-search">
                <svg width="18" height="18" fill="none" stroke="#a3a3a3" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3" stroke-linecap="round"/></svg>
                <input type="text" x-model="search" placeholder="Procurar por referência, moeda, estado ou valor…">
                <button type="button" class="dc-search__clear" x-show="search" x-cloak @click="search = ''" aria-label="Limpar pesquisa">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round"/></svg>
                </button>
            </div>

            <div class="dc-filter-pills">
                <template x-for="f in filters" :key="f.key">
                    <button type="button" class="dc-pill" :class="{'is-active': filter === f.key}" @click="filter = f.key" x-text="f.label"></button>
                </template>
            </div>

            <template x-if="filteredTransactions.length === 0">
                <div class="dc-empty">
                    <div class="dc-empty__icon">📋</div>
                    <div class="dc-empty__text">Sem resultados</div>
                    <div class="dc-empty__sub">Tenta outro filtro.</div>
                </div>
            </template>

            <template x-for="tx in filteredTransactions" :key="tx.reference_id">
                <div style="position:relative;">
                    <a :href="tx.url" class="dc-tx-card">
                        <div class="dc-tx-card__icon" :class="tx.status">
                            <span x-text="tx.status === 'completed' ? '✓' : (['cancelled','expired'].includes(tx.status) ? '✕' : '⏳')"></span>
                        </div>
                        <div class="dc-tx-card__info">
                            <div class="dc-tx-card__ref" x-text="'#' + tx.reference_id"></div>
                            <div class="dc-tx-card__amount" x-text="formatCurrency(tx.amount_sent) + ' ' + tx.currency_from"></div>
                            <div class="dc-tx-card__when" x-text="tx.created_diff"></div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                            <div class="dc-tx-card__status" :class="tx.status" x-text="statusLabel(tx.status)"></div>
                            <button x-show="tx.receipt_url"
                                    @click.stop.prevent="window.open(tx.receipt_url, '_blank')"
                                    style="font-size:0.6rem;font-weight:800;font-family:'Syne',sans-serif;background:#064e3b;color:white;border:none;padding:3px 8px;border-radius:20px;cursor:pointer;text-transform:uppercase;letter-spacing:0.05em;white-space:nowrap;">
                                📄 Comp.
                            </button>
                        </div>
                    </a>
                </div>
            </template>
        </div>

        {{-- ============ IBAN ============ --}}
        <div x-show="activeTab === 'iban'" x-transition.opacity x-cloak>
            <div class="dc-section__head">
                <h2 class="dc-section__title">Beneficiários (IBAN)</h2>
                <button type="button" class="dc-action-btn primary" style="padding:0.5rem 0.875rem;" @click="openIbanModal = true">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
                    <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem;">Adicionar</span>
                </button>
            </div>

            @if($beneficiaries->isEmpty())
                <div class="dc-empty">
                    <div class="dc-empty__icon">🏦</div>
                    <div class="dc-empty__text">Sem IBANs cadastrados</div>
                    <div class="dc-empty__sub">Cadastra a conta angolana onde queres receber Kwanzas.</div>
                    <button type="button" class="dc-empty__cta" @click="openIbanModal = true">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
                        Adicionar IBAN
                    </button>
                </div>
            @else
                @foreach($beneficiaries as $b)
                    <div class="dc-iban-card">
                        <div class="dc-iban-card__head">
                            <div style="flex:1; min-width:0;">
                                <div class="dc-iban-card__bank">{{ $b->bank_name }}</div>
                                <div class="dc-iban-card__holder">{{ $b->holder_name }}</div>
                            </div>
                            <form method="POST" action="{{ route('beneficiary.destroy', $b->id) }}"
                                  onsubmit="return confirm('Apagar este IBAN?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dc-iban-card__del">Apagar</button>
                            </form>
                        </div>
                        <div class="dc-iban-card__num">{{ $b->iban }}</div>
                    </div>
                @endforeach
            @endif

            {{-- ===== CARTEIRAS (Bybit / Binance / RedotPay) ===== --}}
            @php
                $providerLabels = ['bybit' => 'Bybit', 'binance' => 'Binance', 'redotpay' => 'RedotPay'];
            @endphp
            <div class="dc-section__head" style="margin-top:1.75rem;">
                <h2 class="dc-section__title">Carteiras (cripto)</h2>
                <button type="button" class="dc-action-btn primary" style="padding:0.5rem 0.875rem;" @click="openWalletModal = true">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
                    <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:0.75rem;">Adicionar</span>
                </button>
            </div>

            @if(($wallets ?? collect())->isEmpty())
                <div class="dc-empty">
                    <div class="dc-empty__icon">
                        <svg width="26" height="26" fill="none" stroke="#a3a3a3" stroke-width="1.75" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14h2" stroke-linecap="round"/></svg>
                    </div>
                    <div class="dc-empty__text">Sem carteiras registadas</div>
                    <div class="dc-empty__sub">Adiciona a tua carteira Bybit, Binance ou RedotPay para receberes.</div>
                    <button type="button" class="dc-empty__cta" @click="openWalletModal = true">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
                        Adicionar carteira
                    </button>
                </div>
            @else
                @foreach($wallets as $w)
                    <div class="dc-iban-card">
                        <div class="dc-iban-card__head">
                            <div style="flex:1; min-width:0;">
                                <div class="dc-iban-card__bank">
                                    {{ $providerLabels[$w->provider] ?? ucfirst($w->provider) }}
                                    @if($w->network) <span style="font-weight:500; color:#737373; font-size:0.75rem;">· {{ $w->network }}</span> @endif
                                    @if($w->is_default) <span style="font-weight:700; color:#007a34; font-size:0.7rem;">· Predefinida</span> @endif
                                </div>
                                <div class="dc-iban-card__holder">{{ $w->holder_name }}</div>
                            </div>
                            <form method="POST" action="{{ route('wallet.destroy', $w->id) }}"
                                  onsubmit="return confirm('Apagar esta carteira?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dc-iban-card__del">Apagar</button>
                            </form>
                        </div>
                        <div class="dc-iban-card__num">{{ $w->identifier }}</div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ============ SUPPORT ============ --}}
        <div x-show="activeTab === 'support'" x-transition.opacity x-cloak>
            <h2 class="dc-section__title" style="margin-bottom:1rem;">Suporte</h2>

            <a href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe."
               target="_blank" rel="noopener" class="dc-support-card">
                <div class="dc-support-card__icon wa">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                </div>
                <div style="flex:1;">
                    <div class="dc-support-card__title">WhatsApp</div>
                    <div class="dc-support-card__sub">+55 11 93357-9009 · Resposta rápida</div>
                </div>
                <svg width="14" height="14" fill="none" stroke="#a3a3a3" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <a href="mailto:suporte@kwanzasafe.com" class="dc-support-card">
                <div class="dc-support-card__icon email">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div style="flex:1;">
                    <div class="dc-support-card__title">Email</div>
                    <div class="dc-support-card__sub">suporte@kwanzasafe.com</div>
                </div>
                <svg width="14" height="14" fill="none" stroke="#a3a3a3" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <a href="{{ url('/#faq') }}" class="dc-support-card">
                <div class="dc-support-card__icon faq">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div style="flex:1;">
                    <div class="dc-support-card__title">Perguntas Frequentes</div>
                    <div class="dc-support-card__sub">Respostas às dúvidas comuns</div>
                </div>
                <svg width="14" height="14" fill="none" stroke="#a3a3a3" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <div style="background:#f0faf4; border:1px solid #a7f3d0; border-radius:14px; padding:1rem 1.125rem; margin-top:1rem;">
                <div style="font-family:'Syne',sans-serif; font-weight:700; font-size:0.85rem; color:#007a34;">Horário de Atendimento</div>
                <div style="font-size:0.8rem; color:#525252; margin-top:6px; line-height:1.5;">
                    <strong>Dias úteis:</strong> 9h-18h (Luanda)<br>
                    <strong>Sábados:</strong> 9h-13h (Luanda)<br>
                    <strong>Resposta WhatsApp:</strong> < 2 horas
                </div>
            </div>
        </div>

        {{-- ============ PROFILE ============ --}}
        <div x-show="activeTab === 'profile'" x-transition.opacity x-cloak>
            <div class="dc-profile-hero">
                <div class="dc-profile-hero__avatar">
                    @if(ks_file($user->display_photo_path))
                        <img loading="lazy" decoding="async" src="{{ ks_file($user->display_photo_path) }}" alt="">
                    @else
                        {{ strtoupper(substr($firstName, 0, 1)) }}
                    @endif
                </div>
                <div class="dc-profile-hero__name">{{ $user->full_name ?? $firstName }}</div>
                <div class="dc-profile-hero__email">{{ $user->email }}</div>
                @if($kycStatus['approved'])
                    <div style="display:inline-block; margin-top:8px; background:#009d44; color:white; font-size:0.6rem; font-weight:800; padding:3px 8px; border-radius:20px; position:relative;">✓ VERIFICADO</div>
                @endif
            </div>

            <a href="{{ route('profile.edit') }}" class="dc-profile-row">
                <span class="dc-profile-row__label">Editar Perfil</span>
                <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            @if(!$kycStatus['approved'])
                <a href="{{ route('profile.edit') }}#kyc" class="dc-profile-row">
                    <span class="dc-profile-row__label">Completar Verificação KYC</span>
                    <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif

            @if($user->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="dc-profile-row">
                    <span class="dc-profile-row__label">Painel Admin</span>
                    <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif

            <a href="{{ route('terms') }}" target="_blank" class="dc-profile-row">
                <span class="dc-profile-row__label">Termos de Uso</span>
                <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <a href="{{ route('privacy') }}" target="_blank" class="dc-profile-row">
                <span class="dc-profile-row__label">Política de Privacidade</span>
                <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>

            <form method="POST" action="{{ route('logout') }}" style="margin-top:1rem;">
                @csrf
                <button type="submit" class="dc-profile-row danger" style="width:100%; border:1px solid #fee2e2; background:#fee2e2;">
                    <span class="dc-profile-row__label">Sair da Conta</span>
                    <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#dc2626;"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>

    </main>

    {{-- ============ MODAL ADICIONAR IBAN ============ --}}
    <div x-show="openIbanModal"
         x-cloak
         x-transition.opacity
         class="dc-modal-overlay"
         @click.self="openIbanModal = false"
         @keydown.escape.window="openIbanModal = false">
        <div class="dc-modal">
            <div class="dc-modal__title">Adicionar IBAN</div>
            <div class="dc-modal__sub">Conta bancária angolana onde queres receber os Kwanzas.</div>
            <form method="POST" action="{{ route('beneficiary.store') }}">
                @csrf
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Banco</label>
                    <input type="text" name="bank_name" class="dc-modal__input" placeholder="Ex: BFA, BAI, BIC..." required>
                </div>
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Titular da Conta</label>
                    <input type="text" name="holder_name" class="dc-modal__input" value="{{ $user->full_name }}" required>
                </div>
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Número IBAN</label>
                    <input type="text" name="iban" class="dc-modal__input" placeholder="AO06 0000 0000 0000 0000 0000 0" required>
                </div>
                <div class="dc-modal__actions">
                    <button type="button" class="dc-modal__btn dc-modal__btn-cancel" @click="openIbanModal = false">Cancelar</button>
                    <button type="submit" class="dc-modal__btn dc-modal__btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ MODAL ADICIONAR CARTEIRA ============ --}}
    <div x-show="openWalletModal"
         x-cloak
         x-transition.opacity
         class="dc-modal-overlay"
         @click.self="openWalletModal = false"
         @keydown.escape.window="openWalletModal = false">
        <div class="dc-modal">
            <div class="dc-modal__title">Adicionar carteira</div>
            <div class="dc-modal__sub">Carteira Bybit, Binance ou RedotPay onde queres receber. O titular tem de coincidir com o teu nome do KYC.</div>
            <form method="POST" action="{{ route('wallet.store') }}">
                @csrf
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Fornecedor</label>
                    <select name="provider" class="dc-modal__input" required>
                        <option value="bybit">Bybit</option>
                        <option value="binance">Binance</option>
                        <option value="redotpay">RedotPay</option>
                    </select>
                </div>
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Titular da carteira</label>
                    <input type="text" name="holder_name" class="dc-modal__input" value="{{ $user->full_name }}" required>
                </div>
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Identificador (email / UID)</label>
                    <input type="text" name="identifier" class="dc-modal__input" placeholder="Ex.: email da conta ou UID" required>
                </div>
                <div class="dc-modal__field">
                    <label class="dc-modal__label">Rede (opcional)</label>
                    <input type="text" name="network" class="dc-modal__input" placeholder="Ex.: TRC20, ERC20">
                </div>
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.82rem; color:#404040; margin-bottom:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="is_default" value="1"> Usar como predefinida
                </label>
                <div class="dc-modal__actions">
                    <button type="button" class="dc-modal__btn dc-modal__btn-cancel" @click="openWalletModal = false">Cancelar</button>
                    <button type="submit" class="dc-modal__btn dc-modal__btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ BOTTOM NAV (mobile only) ============ --}}
    <nav class="dc-bottom" aria-label="Navegação principal">
        <button type="button" class="dc-bottom-btn" :class="{'is-active': activeTab === 'home'}" @click="setTab('home')">
            <svg class="dc-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Início</span>
        </button>
        <button type="button" class="dc-bottom-btn" :class="{'is-active': activeTab === 'history'}" @click="setTab('history')">
            <svg class="dc-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Histórico</span>
            @if($pendingCount > 0)
                <span class="dc-bottom-btn__badge">{{ $pendingCount }}</span>
            @endif
        </button>
        <button type="button" class="dc-bottom-btn" :class="{'is-active': activeTab === 'iban'}" @click="setTab('iban')">
            <svg class="dc-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/></svg>
            <span>IBAN</span>
        </button>
        <button type="button" class="dc-bottom-btn" :class="{'is-active': activeTab === 'support'}" @click="setTab('support')">
            <svg class="dc-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Suporte</span>
            @if(($unreadMessages ?? 0) > 0)
                <span class="dc-bottom-btn__badge">{{ $unreadMessages }}</span>
            @endif
        </button>
        <button type="button" class="dc-bottom-btn" :class="{'is-active': activeTab === 'profile'}" @click="setTab('profile')">
            <svg class="dc-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Perfil</span>
        </button>
    </nav>

</div>

@push('scripts')
<script>
function ksCalculator() {
    return {
        currencies: @json($ratesForJs),
        destinations: @json($destinationsForJs),
        amount: '',
        activeCurrency: null,
        selectedDest: null,
        destOpen: false,
        currOpen: false,
        MIN: 10,
        MAX: 50000,

        init() {
            const lastCode = localStorage.getItem('ks_calc_currency') || 'EUR';
            this.activeCurrency = this.currencies.find(c => c.code === lastCode) || this.currencies[0];
            this.selectedDest = this.destinations[0] || null;
        },

        setCurrency(curr) {
            this.activeCurrency = curr;
            this.currOpen = false;
            localStorage.setItem('ks_calc_currency', curr.code);
        },

        setDest(d) {
            this.selectedDest = d;
            this.destOpen = false;
        },

        get destKey() {
            return this.selectedDest ? `${this.selectedDest.type}:${this.selectedDest.id}` : '';
        },

        /** Valor numérico do input (nunca float na lógica de negócio — só apresentação). */
        get amountNum() {
            const n = parseFloat(String(this.amount).replace(',', '.'));
            return isFinite(n) && n > 0 ? n : 0;
        },

        get received() {
            return this.amountNum > 0 ? this.amountNum * this.activeCurrency.rate : 0;
        },

        get belowMin() { return this.amountNum > 0 && this.amountNum < this.MIN; },
        get aboveMax() { return this.amountNum > this.MAX; },

        get errorText() {
            if (this.belowMin) return `O valor mínimo é ${this.MIN} ${this.activeCurrency.code}.`;
            if (this.aboveMax) return `O valor máximo por transação é 50.000 ${this.activeCurrency.code}.`;
            return '';
        },

        /** Pode submeter: valor dentro dos limites E destino escolhido. */
        get canSubmit() {
            return this.amountNum >= this.MIN && this.amountNum <= this.MAX && !!this.selectedDest;
        },

        /** Formatação pt-AO: '.' para milhares, ',' para decimais. */
        formatNumber(val, dec = 2) {
            let n = parseFloat(val);
            if (!isFinite(n)) n = 0;
            const fixed = Math.abs(n).toFixed(dec);
            let parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            let out = (parts[1] && parseInt(parts[1], 10) !== 0) ? parts[0] + ',' + parts[1].replace(/0+$/, '') : parts[0];
            return (n < 0 ? '-' : '') + out;
        }
    };
}
    
function clientDashboard() {
    return {
        // Estado de navegação
        activeTab: '{{ $initialTab }}',
        sidebarOpen: false,
        openIbanModal: false,
        openWalletModal: false,

        // Filtros
        filter: window.ksPersist ? window.ksPersist.loadState('client_filter', 'all') : 'all',
        search: '',
        filters: [
            {key: 'all',       label: 'Todas'},
            {key: 'completed', label: 'Concluídas'},
            {key: 'pending',   label: 'Em Curso'},
            {key: 'cancelled', label: 'Canceladas'},
        ],

        // Dados
        transactions: @json($txForJs),

        init() {
            // Persistência da tab via URL
            this.$watch('activeTab', t => {
                if (window.ksPersist) window.ksPersist.saveTab(t);
                // Fechar sidebar ao mudar de tab
                this.sidebarOpen = false;
            });

            // Persistência do filtro
            this.$watch('filter', f => {
                if (window.ksPersist) window.ksPersist.saveState('client_filter', f);
            });

            // Botão "voltar" do browser
            window.addEventListener('popstate', () => {
                const urlTab = new URLSearchParams(window.location.search).get('tab') || 'home';
                if (['home','history','iban','support','profile'].includes(urlTab)) {
                    this.activeTab = urlTab;
                }
            });

            // Bloqueio scroll body quando sidebar aberta
            this.$watch('sidebarOpen', open => {
                document.body.style.overflow = open ? 'hidden' : '';
            });

            this.$watch('openIbanModal', open => {
                document.body.style.overflow = open ? 'hidden' : '';
            });

            this.$watch('openWalletModal', open => {
                document.body.style.overflow = open ? 'hidden' : '';
            });
        },

        setTab(t) {
            this.activeTab = t;
            this.sidebarOpen = false;
            // Scroll para o topo do conteúdo
            const main = document.querySelector('.dc-main');
            if (main) main.scrollIntoView({behavior:'smooth', block:'start'});
        },

        get filteredTransactions() {
            let list = this.transactions;
            if (this.filter === 'cancelled') {
                list = list.filter(tx => ['cancelled','expired'].includes(tx.status));
            } else if (this.filter === 'pending') {
                list = list.filter(tx => !['completed','cancelled','expired'].includes(tx.status));
            } else if (this.filter !== 'all') {
                list = list.filter(tx => tx.status === this.filter);
            }

            const q = (this.search || '').trim().toLowerCase();
            if (q) {
                list = list.filter(tx =>
                    (tx.reference_id || '').toLowerCase().includes(q)
                    || (tx.currency_from || '').toLowerCase().includes(q)
                    || this.statusLabel(tx.status).toLowerCase().includes(q)
                    || String(tx.amount_sent).includes(q)
                    || String(tx.amount_received).includes(q)
                );
            }
            return list;
        },

        formatCurrency(val) {
            return parseFloat(val).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        statusLabel(status) {
            const labels = {
                pending: 'Pendente', negotiating: 'Negociação',
                awaiting_payment: 'Aguarda Pag.', payment_received: 'Pag. Recebido',
                aoa_sent: 'AOA Enviados', completed: 'Concluída',
                cancelled: 'Cancelada', expired: 'Expirada',
            };
            return labels[status] || status.replace(/_/g, ' ');
        },
    };
}
</script>
@endpush

</x-app-layout>
