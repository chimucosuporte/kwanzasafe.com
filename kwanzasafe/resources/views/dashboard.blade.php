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
        'url'             => route('transaction.show', $tx->reference_id),
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

    .dc-topbar__brand { display:flex; align-items:center; gap:0.5rem; flex:1; min-width:0; }
    .dc-topbar__logo { height:30px; width:auto; }

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
        background:linear-gradient(180deg,#000 0%,#0a0a0a 100%);
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

    .dc-sidebar__logo {
        padding:1.25rem 1.25rem 1rem;
        border-bottom:1px solid rgba(255,255,255,0.08);
        display:flex; align-items:center; gap:0.625rem;
    }
    .dc-sidebar__logo img { height:32px; filter:brightness(0) invert(1); }
    .dc-sidebar__brand { font-family:'Syne',sans-serif; font-weight:800; font-size:1rem; }
    .dc-sidebar__sub {
        font-size:0.55rem; font-weight:700; letter-spacing:0.15em;
        text-transform:uppercase; color:#009d44; margin-top:-2px;
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
        background:linear-gradient(135deg,#009d44 0%,#007a34 100%);
        border-radius:20px;
        padding:1.5rem;
        color:white;
        margin-bottom:1.25rem;
        position:relative;
        overflow:hidden;
    }
    .dc-hero::before {
        content:''; position:absolute;
        top:-40px; right:-40px;
        width:160px; height:160px;
        background:rgba(255,255,255,0.08);
        border-radius:50%;
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

    /* ============ KYC STATUS BANNER ============ */
    .dc-kyc-banner {
        border-radius:14px;
        padding:1rem 1.125rem;
        margin-bottom:1rem;
        display:flex; align-items:center; gap:0.75rem;
        text-decoration:none; color:inherit;
        transition:transform 0.15s;
    }
    .dc-kyc-banner:hover { transform:translateX(2px); }
    .dc-kyc-banner.warn { background:linear-gradient(135deg,#fef3c7,#fde68a); border:1px solid #f59e0b; }
    .dc-kyc-banner.success { background:linear-gradient(135deg,#d1f2e0,#a7f3d0); border:1px solid #009d44; }
    .dc-kyc-banner.danger { background:linear-gradient(135deg,#fee2e2,#fca5a5); border:1px solid #dc2626; }
    .dc-kyc-banner__icon {
        width:42px; height:42px; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; color:white;
    }
    .dc-kyc-banner.warn .dc-kyc-banner__icon { background:#f59e0b; }
    .dc-kyc-banner.success .dc-kyc-banner__icon { background:#009d44; }
    .dc-kyc-banner.danger .dc-kyc-banner__icon { background:#dc2626; }
    .dc-kyc-banner__title { font-family:'Syne',sans-serif; font-weight:800; font-size:0.875rem; }
    .dc-kyc-banner__sub { font-size:0.75rem; margin-top:1px; opacity:0.85; }
    .dc-kyc-banner__cta {
        background:#000; color:white;
        padding:0.5rem 0.875rem;
        border-radius:8px;
        font-family:'Syne',sans-serif;
        font-size:0.7rem; font-weight:800;
        text-transform:uppercase; letter-spacing:0.05em;
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
        background:linear-gradient(135deg,#000 0%,#171717 100%);
        border-radius:18px; padding:1.5rem;
        color:white; text-align:center;
        margin-bottom:1rem;
        position:relative; overflow:hidden;
    }
    .dc-profile-hero::before {
        content:''; position:absolute;
        top:-30px; right:-30px;
        width:140px; height:140px;
        background:radial-gradient(circle,rgba(0,157,68,0.2),transparent);
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
        <div class="dc-topbar__brand">
            <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="dc-topbar__logo">
        </div>
        <button class="dc-topbar__avatar" @click="setTab('profile')" aria-label="Perfil">
            @if(ks_file($user->profile_photo_path))
                <img src="{{ ks_file($user->profile_photo_path) }}" alt="">
            @else
                {{ strtoupper(substr($firstName, 0, 1)) }}
            @endif
        </button>
    </div>

    {{-- ============ OVERLAY (mobile sidebar) ============ --}}
    <div class="dc-overlay" :class="{'is-open': sidebarOpen}" @click="sidebarOpen = false"></div>

    {{-- ============ SIDEBAR (desktop fixa, mobile hamburger) ============ --}}
    <aside class="dc-sidebar" :class="{'is-open': sidebarOpen}">
        <div class="dc-sidebar__logo">
            <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe">
            <div>
                <div class="dc-sidebar__brand">KwanzaSafe</div>
                <div class="dc-sidebar__sub">Cliente</div>
            </div>
        </div>

        <div class="dc-sidebar__user">
            <div class="dc-sidebar__avatar">
                @if(ks_file($user->profile_photo_path))
                    <img src="{{ ks_file($user->profile_photo_path) }}" alt="">
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
            <button type="button" class="dc-nav" :class="{'is-active': activeTab === 'support'}" @click="setTab('support')">
                <svg class="dc-nav__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Suporte
                @if($unreadMessages ?? 0 > 0)
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
                <div class="dc-hero__name">{{ $firstName }} 👋</div>
                <div class="dc-hero__sub">
                    @if($kycStatus['approved'])
                        Conta verificada. Pronta para câmbio internacional.
                    @else
                        Completa a tua verificação para começar a transacionar.
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
                <div class="dc-calc" x-data="ksCalculator()" style="margin-bottom: 1.25rem;">
                
                    {{-- Header da calculadora --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                        <div>
                            <div class="dc-section__title">💱 Simular Câmbio</div>
                            <div style="font-size:0.7rem; color:#737373; margin-top:1px;">
                                Calcula quanto recebes em Kwanzas
                            </div>
                        </div>
                    </div>
                
                    <div style="background:white; border:1px solid #e5e5e5; border-radius:16px; padding:1.125rem; box-shadow:0 1px 3px rgba(0,0,0,0.03);">
                
                        {{-- SELETOR DE MOEDA --}}
                        <div style="display:flex; gap:6px; margin-bottom:1rem;">
                            <template x-for="curr in currencies" :key="curr.code">
                                <button type="button"
                                        @click="setCurrency(curr)"
                                        :class="{'is-active': activeCurrency.code === curr.code}"
                                        class="dc-calc-pill"
                                        style="flex:1; padding:0.625rem 0.5rem; background:#fafafa; border:2px solid transparent; border-radius:10px; cursor:pointer; transition:all 0.2s; font-family:'Syne',sans-serif; font-weight:700; font-size:0.75rem; display:flex; flex-direction:column; align-items:center; gap:2px;"
                                        :style="activeCurrency.code === curr.code ? `background:${curr.color}15; border-color:${curr.color}; color:${curr.color};` : ''">
                                    <span style="font-size:1.1rem; line-height:1;" x-text="curr.flag"></span>
                                    <span x-text="curr.code"></span>
                                </button>
                            </template>
                        </div>
                
                        {{-- INPUT DE VALOR --}}
                        <div style="margin-bottom:0.75rem;">
                            <label style="display:block; font-size:0.65rem; font-weight:700; color:#737373; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:0.375rem;">
                                Quanto vais enviar
                            </label>
                            <div style="position:relative;">
                                <span style="position:absolute; left:14px; top:50%; transform:translateY(-50%); font-family:'Syne',sans-serif; font-weight:800; font-size:0.95rem; color:#a3a3a3;" x-text="activeCurrency.symbol"></span>
                                <input type="number"
                                       inputmode="decimal"
                                       step="0.01"
                                       min="0"
                                       x-model.number="amount"
                                       @input="amount = Math.max(0, $event.target.valueAsNumber || 0)"
                                       placeholder="0,00"
                                       style="width:100%; padding:14px 14px 14px 44px; border:2px solid #e5e5e5; border-radius:12px; font-family:'DM Sans',sans-serif; font-size:1.1rem; font-weight:600; color:#000; background:#fafafa; outline:none; transition:all 0.2s;"
                                       :style="amount > 0 ? 'border-color:#009d44; background:white;' : ''"
                                       onfocus="this.style.borderColor='#009d44'; this.style.background='white';"
                                       onblur="if(!this.value) { this.style.borderColor='#e5e5e5'; this.style.background='#fafafa'; }">
                            </div>
                        </div>
                
                        {{-- INDICADOR DE TAXA --}}
                        <div style="display:flex; align-items:center; justify-content:center; padding:0.5rem 0; font-size:0.7rem; color:#737373;">
                            <svg width="16" height="16" fill="none" stroke="#009d44" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:6px;">
                                <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>1 <strong x-text="activeCurrency.code"></strong> = </span>
                            <strong style="margin: 0 4px; color:#000; font-family:'JetBrains Mono',monospace;" x-text="formatNumber(activeCurrency.rate)"></strong>
                            <span>Kz</span>
                        </div>
                
                        {{-- RESULTADO --}}
                        <div style="background:linear-gradient(135deg,#000 0%,#171717 100%); border-radius:14px; padding:1rem 1.125rem; margin-top:0.5rem; position:relative; overflow:hidden;">
                            <div style="position:absolute; top:-30px; right:-30px; width:120px; height:120px; background:radial-gradient(circle, rgba(0,157,68,0.2), transparent); pointer-events:none;"></div>
                
                            <div style="font-size:0.65rem; font-weight:700; color:rgba(255,255,255,0.6); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px; position:relative;">
                                Vais receber
                            </div>
                            <div style="display:flex; align-items:baseline; gap:8px; position:relative;">
                                <span style="font-family:'Syne',sans-serif; font-weight:800; font-size:1.6rem; color:white; letter-spacing:-0.02em; line-height:1;"
                                      x-text="formatNumber(received)"></span>
                                <span style="font-family:'Syne',sans-serif; font-weight:700; color:#009d44; font-size:0.9rem;">Kz</span>
                            </div>
                            <div style="font-size:0.65rem; color:rgba(255,255,255,0.5); margin-top:6px; position:relative;"
                                 x-show="amount > 0">
                                <span x-text="`${formatNumber(amount)} ${activeCurrency.code}`"></span>
                                ·
                                <span x-text="`Taxa ${formatNumber(activeCurrency.rate)} Kz`"></span>
                            </div>
                            <div style="font-size:0.65rem; color:rgba(255,255,255,0.5); margin-top:6px; position:relative;"
                                 x-show="amount === 0 || !amount">
                                Introduz um valor para simular
                            </div>
                        </div>
                
                        {{-- INFO TAXAS --}}
                        <div style="font-size:0.65rem; color:#a3a3a3; text-align:center; margin-top:0.75rem; line-height:1.5;">
                            ℹ️ Taxas indicativas atualizadas hoje · Taxa real confirmada ao criar a transação
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
            <h2 class="dc-section__title" style="margin-bottom:1rem;">Histórico de Transações</h2>

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
                <a :href="tx.url" class="dc-tx-card">
                    <div class="dc-tx-card__icon" :class="tx.status">
                        <span x-text="tx.status === 'completed' ? '✓' : (['cancelled','expired'].includes(tx.status) ? '✕' : '⏳')"></span>
                    </div>
                    <div class="dc-tx-card__info">
                        <div class="dc-tx-card__ref" x-text="'#' + tx.reference_id"></div>
                        <div class="dc-tx-card__amount" x-text="formatCurrency(tx.amount_sent) + ' ' + tx.currency_from"></div>
                        <div class="dc-tx-card__when" x-text="tx.created_diff"></div>
                    </div>
                    <div class="dc-tx-card__status" :class="tx.status" x-text="tx.status.replace(/_/g, ' ')"></div>
                </a>
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
                <div style="font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem; color:#007a34;">⏱️ Horário de Atendimento</div>
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
                    @if(ks_file($user->profile_photo_path))
                        <img src="{{ ks_file($user->profile_photo_path) }}" alt="">
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
                    <span class="dc-profile-row__label">⏳ Completar Verificação KYC</span>
                    <svg class="dc-profile-row__icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif

            @if($user->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="dc-profile-row">
                    <span class="dc-profile-row__label">🛡️ Painel Admin</span>
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
        // ⚠️ TAXAS HARD-CODED (mesmas do welcome.blade.php).
        // Sprint futuro: substituir por taxas reais do admin via API.
        currencies: [
            { code: 'EUR',  symbol: '€', flag: '🇪🇺', rate: 1050, color: '#003399' },
            { code: 'BRL',  symbol: 'R$', flag: '🇧🇷', rate: 175,  color: '#009c3b' },
            { code: 'USDT', symbol: '$', flag: '₮',  rate: 950,  color: '#26a17b' },
        ],
        amount: 0,
        activeCurrency: null,

        init() {
            // Restaurar última escolha do utilizador (se existir)
            const lastCode = localStorage.getItem('ks_calc_currency') || 'EUR';
            this.activeCurrency = this.currencies.find(c => c.code === lastCode) || this.currencies[0];
        },

        setCurrency(curr) {
            this.activeCurrency = curr;
            localStorage.setItem('ks_calc_currency', curr.code);
        },

        get received() {
            if (!this.amount || this.amount <= 0) return 0;
            return this.amount * this.activeCurrency.rate;
        },

        formatNumber(val) {
            if (!val && val !== 0) return '0';
            return parseFloat(val).toLocaleString('pt-PT', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        }
    };
}
    
function clientDashboard() {
    return {
        // Estado de navegação
        activeTab: '{{ $initialTab }}',
        sidebarOpen: false,
        openIbanModal: false,

        // Filtros
        filter: window.ksPersist ? window.ksPersist.loadState('client_filter', 'all') : 'all',
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
        },

        setTab(t) {
            this.activeTab = t;
            this.sidebarOpen = false;
            // Scroll para o topo do conteúdo
            const main = document.querySelector('.dc-main');
            if (main) main.scrollIntoView({behavior:'smooth', block:'start'});
        },

        get filteredTransactions() {
            if (this.filter === 'all') return this.transactions;
            if (this.filter === 'cancelled') {
                return this.transactions.filter(tx => ['cancelled','expired'].includes(tx.status));
            }
            if (this.filter === 'pending') {
                return this.transactions.filter(tx => !['completed','cancelled','expired'].includes(tx.status));
            }
            return this.transactions.filter(tx => tx.status === this.filter);
        },

        formatCurrency(val) {
            return parseFloat(val).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
    };
}
</script>
@endpush

</x-app-layout>
