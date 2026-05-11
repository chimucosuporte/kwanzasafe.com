<x-app-layout>

@php
    $user = auth()->user();
    $hour = now()->setTimezone('Africa/Luanda')->hour;
    $greeting = $hour < 12 ? 'Bom dia' : ($hour < 19 ? 'Boa tarde' : 'Boa noite');
    $firstName = $user->full_name ? explode(' ', trim($user->full_name))[0] : 'Admin';
@endphp

@push('head')
<title>Centro de Comando — KwanzaSafe Admin</title>
<style>
    body { background:#f5f5f5; }
    .ad * { box-sizing:border-box; }
    .ad {
        font-family:'DM Sans',sans-serif;
        min-height:100dvh;
        padding-bottom:80px;
    }
    @media(min-width:1024px) {
        .ad { padding-bottom:0; padding-left:240px; }
    }

    .ad-font-display { font-family:'Syne',sans-serif; }
    .ad-font-mono { font-family:'JetBrains Mono',monospace; }

    /* ============ TOPBAR (mobile) ============ */
    .ad-topbar {
        position:sticky; top:0; z-index:40;
        background:#000; color:white;
        padding:0.75rem 1rem;
        display:flex; align-items:center; justify-content:space-between; gap:0.75rem;
        box-shadow:0 2px 8px rgba(0,0,0,0.1);
    }
    @media(min-width:1024px) { .ad-topbar { display:none; } }

    .ad-topbar__menu {
        background:rgba(255,255,255,0.1); border:none; cursor:pointer;
        padding:0.5rem; border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        color:white; transition:background 0.15s;
    }
    .ad-topbar__menu:hover { background:rgba(255,255,255,0.2); }

    .ad-topbar__brand { display:flex; align-items:center; gap:0.5rem; flex:1; min-width:0; }
    .ad-topbar__logo { height:30px; filter:brightness(0) invert(1); }
    .ad-topbar__title-wrap { min-width:0; }
    .ad-topbar__title {
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .ad-topbar__sub {
        font-size:0.6rem; color:#a3a3a3;
        margin-top:-1px;
    }

    .ad-topbar__live {
        display:inline-flex; align-items:center; gap:5px;
        background:rgba(0,157,68,0.2);
        padding:4px 9px; border-radius:20px;
        font-size:0.6rem; font-weight:700; color:#22c55e;
        text-transform:uppercase; letter-spacing:0.05em;
        flex-shrink:0;
    }
    .ad-topbar__live::before {
        content:''; width:5px; height:5px;
        background:#22c55e; border-radius:50%;
        box-shadow:0 0 6px #22c55e;
        animation:adLivePulse 1.5s infinite;
    }
    @keyframes adLivePulse { 0%,100% {opacity:1} 50% {opacity:0.4} }

    /* ============ DESKTOP TOPBAR (white, no mobile elements) ============ */
    .ad-desktop-topbar {
        background:white;
        border-bottom:1px solid #e5e5e5;
        padding:0.875rem 1.5rem;
        display:none;
        align-items:center; justify-content:space-between;
        position:sticky; top:0; z-index:30;
        box-shadow:0 1px 3px rgba(0,0,0,0.03);
    }
    @media(min-width:1024px) { .ad-desktop-topbar { display:flex; } }

    .ad-desktop-topbar__left h1 {
        font-family:'Syne',sans-serif; font-weight:800; font-size:1.125rem;
        margin:0;
    }
    .ad-desktop-topbar__sub {
        font-size:0.7rem; color:#737373; margin-top:1px;
    }

    .ad-desktop-topbar__right { display:flex; gap:0.5rem; align-items:center; }

    /* ============ SIDEBAR (DESKTOP fixa, MOBILE hamburger) ============ */
    .ad-sidebar {
        position:fixed; top:0; left:0; bottom:0;
        width:240px;
        background:linear-gradient(180deg,#000 0%,#0a0a0a 100%);
        color:white;
        z-index:50;
        display:flex; flex-direction:column;
        transform:translateX(-100%);
        transition:transform 0.3s ease;
    }
    @media(min-width:1024px) {
        .ad-sidebar { transform:none; }
    }
    .ad-sidebar.is-open { transform:translateX(0); }

    .ad-sidebar__logo {
        padding:1.25rem 1.25rem 1rem;
        border-bottom:1px solid rgba(255,255,255,0.08);
        display:flex; align-items:center; gap:0.625rem;
    }
    .ad-sidebar__logo img { height:32px; filter:brightness(0) invert(1); }
    .ad-sidebar__brand { font-family:'Syne',sans-serif; font-weight:800; font-size:1rem; }
    .ad-sidebar__sub {
        font-size:0.55rem; font-weight:700; letter-spacing:0.15em;
        text-transform:uppercase; color:#009d44; margin-top:-2px;
    }

    .ad-sidebar__user {
        padding:1rem 1.25rem;
        border-bottom:1px solid rgba(255,255,255,0.08);
        display:flex; align-items:center; gap:0.625rem;
    }
    .ad-sidebar__avatar {
        width:36px; height:36px; border-radius:50%;
        background:#009d44; color:white; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.85rem;
        overflow:hidden;
    }
    .ad-sidebar__avatar img { width:100%; height:100%; object-fit:cover; }
    .ad-sidebar__user-info { min-width:0; flex:1; }
    .ad-sidebar__user-name {
        font-weight:600; font-size:0.85rem; line-height:1.2;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .ad-sidebar__user-role {
        font-size:0.6rem; color:#009d44;
        font-weight:700; text-transform:uppercase; letter-spacing:0.08em;
    }

    .ad-sidebar__nav {
        flex:1;
        padding:0.875rem 0.625rem;
        display:flex; flex-direction:column; gap:2px;
        overflow-y:auto;
    }
    .ad-sidebar__label {
        font-size:0.55rem; font-weight:700; letter-spacing:0.15em;
        text-transform:uppercase; color:rgba(255,255,255,0.3);
        padding:0.75rem 0.75rem 0.25rem;
    }

    .ad-nav {
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
        font-family:'DM Sans',sans-serif;
    }
    .ad-nav:hover { background:rgba(255,255,255,0.06); color:white; }
    .ad-nav.is-active {
        background:rgba(0,157,68,0.15);
        color:#009d44;
        border-color:rgba(0,157,68,0.25);
    }
    .ad-nav.is-active::before {
        content:'';
        position:absolute; left:-0.625rem; top:50%; transform:translateY(-50%);
        width:3px; height:16px; background:#009d44; border-radius:0 3px 3px 0;
    }
    .ad-nav__icon { width:18px; height:18px; flex-shrink:0; opacity:0.7; }
    .ad-nav.is-active .ad-nav__icon { opacity:1; }
    .ad-nav__badge {
        margin-left:auto; min-width:18px; text-align:center;
        background:#dc2626; color:white;
        font-size:0.6rem; font-weight:800;
        padding:2px 7px; border-radius:10px;
    }
    .ad-nav__badge.warn { background:#f59e0b; }
    .ad-nav__badge.info { background:#2563eb; }

    .ad-sidebar__foot {
        padding:1rem;
        border-top:1px solid rgba(255,255,255,0.08);
        font-size:0.55rem; color:rgba(255,255,255,0.4);
        letter-spacing:0.08em; text-transform:uppercase;
        font-weight:700; text-align:center;
    }

    /* ============ OVERLAY ============ */
    .ad-overlay {
        position:fixed; inset:0;
        background:rgba(0,0,0,0.5);
        backdrop-filter:blur(2px);
        z-index:45;
        opacity:0; pointer-events:none;
        transition:opacity 0.3s;
    }
    .ad-overlay.is-open { opacity:1; pointer-events:auto; }
    @media(min-width:1024px) { .ad-overlay { display:none !important; } }

    /* ============ CONTENT ============ */
    .ad-content { padding:1rem; }
    @media(min-width:768px) { .ad-content { padding:1.5rem; } }

    .ad-flash {
        padding:0.875rem 1.125rem; border-radius:10px;
        font-size:0.85rem; margin-bottom:1rem;
        display:flex; align-items:center; gap:0.5rem;
    }
    .ad-flash.success { background:#d1f2e0; border:1px solid #a7f3d0; color:#007a34; }

    /* ============ FILTROS PERIODO ============ */
    .ad-period {
        display:flex; gap:5px; flex-wrap:wrap;
        margin-bottom:1rem;
        background:white;
        padding:6px;
        border-radius:12px;
        border:1px solid #e5e5e5;
    }
    .ad-period__btn {
        flex:1;
        padding:0.4rem 0.625rem;
        background:none; border:none;
        border-radius:8px;
        font-family:'Syne',sans-serif;
        font-size:0.7rem; font-weight:700;
        color:#737373;
        text-decoration:none;
        text-align:center;
        cursor:pointer;
        transition:all 0.15s;
    }
    .ad-period__btn:hover { color:#000; }
    .ad-period__btn.is-active {
        background:#000; color:white;
    }

    /* ============ STATS GRID ============ */
    .ad-stats {
        display:grid; gap:0.75rem;
        grid-template-columns:repeat(2, 1fr);
        margin-bottom:1rem;
    }
    @media(min-width:768px) { .ad-stats { grid-template-columns:repeat(3, 1fr); gap:1rem; } }
    @media(min-width:1280px) { .ad-stats { grid-template-columns:repeat(5, 1fr); } }

    .ad-stat {
        background:white;
        border-radius:14px;
        padding:1rem;
        border:1px solid #e5e5e5;
        box-shadow:0 1px 3px rgba(0,0,0,0.03);
        position:relative; overflow:hidden;
        text-decoration:none; color:inherit;
        cursor:pointer;
        transition:all 0.2s;
        display:block;
    }
    .ad-stat:hover {
        transform:translateY(-2px);
        box-shadow:0 8px 24px rgba(0,0,0,0.08);
        border-color:#009d44;
    }
    .ad-stat::before {
        content:''; position:absolute;
        top:0; left:0; right:0; height:3px;
    }
    .ad-stat.s-users::before { background:#2563eb; }
    .ad-stat.s-kyc::before { background:#f59e0b; }
    .ad-stat.s-tx::before { background:#009d44; }
    .ad-stat.s-vol::before { background:#000; }
    .ad-stat.s-chat::before { background:#dc2626; }

    .ad-stat__head {
        display:flex; justify-content:space-between; align-items:flex-start;
        margin-bottom:0.5rem;
    }
    .ad-stat__icon {
        width:32px; height:32px; border-radius:8px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    }
    .ad-stat.s-users .ad-stat__icon { background:#dbeafe; color:#2563eb; }
    .ad-stat.s-kyc .ad-stat__icon { background:#fef3c7; color:#f59e0b; }
    .ad-stat.s-tx .ad-stat__icon { background:#d1f2e0; color:#009d44; }
    .ad-stat.s-vol .ad-stat__icon { background:#e5e5e5; color:#000; }
    .ad-stat.s-chat .ad-stat__icon { background:#fee2e2; color:#dc2626; }

    .ad-stat__arrow {
        color:#a3a3a3; opacity:0;
        transition:all 0.2s;
    }
    .ad-stat:hover .ad-stat__arrow {
        opacity:1; transform:translateX(3px); color:#009d44;
    }

    .ad-stat__label {
        font-size:0.6rem; font-weight:700;
        letter-spacing:0.08em; text-transform:uppercase;
        color:#a3a3a3;
        margin-bottom:0.25rem;
    }
    .ad-stat__value {
        font-family:'Syne',sans-serif;
        font-size:1.4rem; font-weight:800;
        letter-spacing:-0.02em; line-height:1.1;
        transition:all 0.3s;
    }
    @media(min-width:768px) { .ad-stat__value { font-size:1.6rem; } }
    .ad-stat__value.flash-up {
        color:#009d44;
        transform:scale(1.05);
    }
    .ad-stat__sub {
        font-size:0.65rem; color:#737373;
        margin-top:0.25rem;
        line-height:1.4;
    }
    .ad-stat__sub-good { color:#009d44; font-weight:600; }
    .ad-stat__sub-warn { color:#f59e0b; font-weight:600; }

    /* ============ CHART ============ */
    .ad-chart {
        background:white;
        border-radius:14px;
        border:1px solid #e5e5e5;
        padding:1rem;
        margin-bottom:1rem;
        box-shadow:0 1px 3px rgba(0,0,0,0.03);
    }
    @media(min-width:768px) { .ad-chart { padding:1.5rem; } }

    .ad-chart__head {
        display:flex; justify-content:space-between; align-items:flex-start;
        margin-bottom:1rem; gap:0.5rem;
    }
    .ad-chart__title {
        font-family:'Syne',sans-serif;
        font-size:0.875rem; font-weight:800;
    }
    .ad-chart__sub {
        font-size:0.7rem; color:#737373; margin-top:1px;
    }
    .ad-chart__total {
        text-align:right;
        font-family:'JetBrains Mono',monospace;
        font-size:0.75rem; color:#737373;
    }
    .ad-chart__total-num {
        font-family:'Syne',sans-serif; font-weight:800;
        color:#000; font-size:0.85rem;
    }

    .ad-chart__bars {
        display:flex; align-items:flex-end;
        height:120px; gap:0.375rem;
        padding:0 0.25rem;
        border-bottom:1px dashed #e5e5e5;
    }
    @media(min-width:768px) { .ad-chart__bars { height:160px; gap:0.5rem; } }

    .ad-chart__col {
        flex:1;
        display:flex; flex-direction:column;
        align-items:center; justify-content:flex-end;
        height:100%; cursor:pointer;
    }
    .ad-chart__bar {
        width:100%;
        background:linear-gradient(180deg,#009d44 0%,#007a34 100%);
        border-radius:5px 5px 0 0;
        min-height:4px;
        position:relative;
        transition:all 0.3s;
    }
    .ad-chart__col:hover .ad-chart__bar {
        background:linear-gradient(180deg,#00b350 0%,#009d44 100%);
    }
    .ad-chart__bar.empty { background:#e5e5e5; }
    .ad-chart__bar-value {
        position:absolute;
        top:-22px; left:50%; transform:translateX(-50%);
        font-size:0.6rem; font-weight:700;
        color:#000; white-space:nowrap;
        opacity:0; transition:opacity 0.2s;
    }
    .ad-chart__col:hover .ad-chart__bar-value { opacity:1; }

    .ad-chart__labels {
        display:flex; gap:0.375rem;
        margin-top:0.5rem; padding:0 0.25rem;
    }
    .ad-chart__label {
        flex:1; text-align:center;
        font-size:0.6rem; color:#737373;
        font-weight:600; text-transform:uppercase;
        letter-spacing:0.05em;
    }

    /* ============ PANELS ============ */
    .ad-panels {
        display:grid; gap:1rem;
        grid-template-columns:1fr;
    }
    @media(min-width:1024px) { .ad-panels { grid-template-columns:2fr 1fr; } }

    .ad-panel {
        background:white;
        border-radius:14px;
        border:1px solid #e5e5e5;
        overflow:hidden;
        margin-bottom:1rem;
        box-shadow:0 1px 3px rgba(0,0,0,0.03);
    }
    .ad-panel__head {
        padding:0.875rem 1.125rem;
        border-bottom:1px solid #e5e5e5;
        background:#fafafa;
        display:flex; align-items:center; justify-content:space-between;
        gap:0.5rem;
    }
    .ad-panel__title {
        font-family:'Syne',sans-serif;
        font-size:0.825rem; font-weight:800;
        display:flex; align-items:center; gap:0.5rem;
        min-width:0; flex:1;
    }
    .ad-panel__count {
        background:#f5f5f5; color:#737373;
        padding:2px 7px; border-radius:20px;
        font-size:0.6rem; font-weight:800;
    }
    .ad-panel__link {
        font-size:0.65rem; font-weight:700;
        color:#009d44; text-decoration:none;
        flex-shrink:0;
    }
    .ad-panel__link:hover { color:#007a34; }

    /* ============ ROWS (transações pendentes em mobile = cards) ============ */
    .ad-row {
        padding:0.75rem 1.125rem;
        border-bottom:1px solid #f5f5f5;
        display:flex; align-items:center; gap:0.625rem;
        text-decoration:none; color:inherit;
        transition:background 0.15s;
    }
    .ad-row:last-child { border-bottom:none; }
    .ad-row:hover { background:#fafafa; }

    .ad-row__badge {
        width:32px; height:32px; border-radius:8px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; font-size:0.95rem;
    }
    .ad-row__badge.pending,
    .ad-row__badge.awaiting_payment { background:#fef3c7; color:#f59e0b; }
    .ad-row__badge.processing { background:#dbeafe; color:#2563eb; }

    .ad-row__info { flex:1; min-width:0; }
    .ad-row__title {
        font-family:'Syne',sans-serif;
        font-size:0.8rem; font-weight:700;
    }
    .ad-row__sub {
        font-size:0.65rem; color:#737373;
        margin-top:1px;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .ad-row__amount {
        font-family:'Syne',sans-serif;
        font-size:0.825rem; font-weight:800;
        text-align:right;
        flex-shrink:0;
    }
    .ad-row__currency {
        font-size:0.65rem; color:#007a34;
        font-weight:600; text-align:right; margin-top:1px;
    }

    /* ============ KYC ROW ============ */
    .ad-kyc-row {
        padding:0.75rem 1.125rem;
        border-bottom:1px solid #f5f5f5;
        display:flex; align-items:center; gap:0.625rem;
        text-decoration:none; color:inherit;
    }
    .ad-kyc-row:last-child { border-bottom:none; }
    .ad-kyc-row:hover { background:#fafafa; }
    .ad-kyc-row__avatar {
        width:32px; height:32px; border-radius:50%;
        background:#d1f2e0; color:#007a34;
        display:flex; align-items:center; justify-content:center;
        font-family:'Syne',sans-serif; font-weight:800; font-size:0.7rem;
        overflow:hidden; flex-shrink:0;
    }
    .ad-kyc-row__avatar img { width:100%; height:100%; object-fit:cover; }
    .ad-kyc-row__name {
        font-size:0.8rem; font-weight:700;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .ad-kyc-row__email {
        font-size:0.65rem; color:#a3a3a3;
        overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .ad-kyc-row__time {
        font-size:0.62rem; color:#f59e0b;
        font-weight:700; margin-top:1px;
    }

    /* ============ EMPTY ============ */
    .ad-empty {
        padding:2rem 1rem; text-align:center; color:#a3a3a3;
    }
    .ad-empty__icon { font-size:2rem; margin-bottom:0.375rem; opacity:0.5; }
    .ad-empty__text { font-size:0.8rem; font-weight:600; }

    /* ============ RATES ============ */
    .ad-rate {
        padding:0.75rem 1.125rem;
        border-bottom:1px solid #f5f5f5;
        display:flex; align-items:center; justify-content:space-between;
        gap:0.5rem;
    }
    .ad-rate:last-child { border-bottom:none; }
    .ad-rate__pair {
        font-family:'Syne',sans-serif; font-weight:800;
        font-size:0.85rem;
    }
    .ad-rate__time { font-size:0.6rem; color:#a3a3a3; margin-top:1px; }
    .ad-rate__value {
        font-family:'Syne',sans-serif;
        font-size:0.95rem; font-weight:800;
        color:#007a34;
    }
    .ad-rate__status {
        font-size:0.55rem; font-weight:800;
        text-transform:uppercase; letter-spacing:0.08em;
        padding:2px 6px; border-radius:20px;
        margin-left:0.375rem;
    }
    .ad-rate__status.active { background:#d1f2e0; color:#007a34; }
    .ad-rate__status.inactive { background:#fee2e2; color:#991b1b; }

    /* ============ TOAST ============ */
    .ad-toasts {
        position:fixed; bottom:90px; left:16px; right:16px;
        z-index:200;
        display:flex; flex-direction:column; gap:8px;
        pointer-events:none;
    }
    @media(min-width:768px) {
        .ad-toasts {
            left:auto; right:24px; bottom:24px;
            max-width:380px;
        }
    }

    .ad-toast {
        background:white;
        border-radius:12px;
        padding:0.875rem 1rem;
        box-shadow:0 12px 32px rgba(0,0,0,0.15);
        border-left:4px solid #009d44;
        display:flex; align-items:flex-start; gap:0.625rem;
        animation:adToastIn 0.3s ease-out;
        pointer-events:auto;
    }
    @keyframes adToastIn {
        from { transform:translateY(20px); opacity:0; }
        to { transform:translateY(0); opacity:1; }
    }
    .ad-toast.warn { border-left-color:#f59e0b; }
    .ad-toast.info { border-left-color:#2563eb; }
    .ad-toast__icon {
        width:24px; height:24px; border-radius:6px;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
        background:#d1f2e0; color:#009d44;
    }
    .ad-toast.warn .ad-toast__icon { background:#fef3c7; color:#f59e0b; }
    .ad-toast.info .ad-toast__icon { background:#dbeafe; color:#2563eb; }

    .ad-toast__text { flex:1; min-width:0; }
    .ad-toast__title {
        font-family:'Syne',sans-serif;
        font-size:0.75rem; font-weight:800;
    }
    .ad-toast__sub {
        font-size:0.65rem; color:#737373; margin-top:1px;
    }
    .ad-toast__close {
        background:none; border:none; cursor:pointer;
        color:#a3a3a3; padding:0;
        flex-shrink:0;
    }

    /* ============ REFRESH BUTTON ============ */
    .ad-refresh-btn {
        width:36px; height:36px; border-radius:8px;
        background:#f5f5f5; border:none;
        color:#737373; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        transition:all 0.15s;
        flex-shrink:0;
    }
    .ad-refresh-btn:hover { background:#009d44; color:white; }

    /* ============ BOTTOM NAV ============ */
    .ad-bottom {
        position:fixed; bottom:0; left:0; right:0;
        background:white;
        border-top:1px solid #e5e5e5;
        padding:0.5rem 0.25rem;
        display:flex; justify-content:space-around;
        z-index:30;
        padding-bottom:env(safe-area-inset-bottom, 0.5rem);
    }
    @media(min-width:1024px) { .ad-bottom { display:none; } }

    .ad-bottom-btn {
        flex:1;
        background:none; border:none; cursor:pointer;
        padding:0.5rem 0.25rem;
        display:flex; flex-direction:column; align-items:center; gap:3px;
        color:#737373;
        font-size:0.65rem; font-weight:600;
        position:relative;
        transition:color 0.15s;
        font-family:'DM Sans',sans-serif;
        text-decoration:none;
    }
    .ad-bottom-btn.is-active { color:#009d44; }
    .ad-bottom-btn.is-active::after {
        content:'';
        position:absolute;
        top:0; left:50%; transform:translateX(-50%);
        width:24px; height:3px;
        background:#009d44;
        border-radius:0 0 3px 3px;
    }
    .ad-bottom-btn__icon { width:22px; height:22px; }
    .ad-bottom-btn__badge {
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

<div class="ad"
     x-data="adminDashboard()"
     x-init="init()"
     @keydown.escape.window="sidebarOpen = false">

    {{-- ============ MOBILE TOPBAR (preto) ============ --}}
    <div class="ad-topbar">
        <button class="ad-topbar__menu" @click="sidebarOpen = true" aria-label="Abrir menu">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/>
            </svg>
        </button>
        <div class="ad-topbar__brand">
            <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="ad-topbar__logo">
            <div class="ad-topbar__title-wrap">
                <div class="ad-topbar__title">{{ $greeting }}, {{ $firstName }}</div>
                <div class="ad-topbar__sub" x-text="`Atualizado às ${lastUpdate}`"></div>
            </div>
        </div>
        <div class="ad-topbar__live">Ao Vivo</div>
    </div>

    {{-- ============ DESKTOP TOPBAR (white) ============ --}}
    <header class="ad-desktop-topbar">
        <div class="ad-desktop-topbar__left">
            <h1>Centro de Comando</h1>
            <div class="ad-desktop-topbar__sub">
                <span x-text="`Atualizado às ${lastUpdate}`"></span>
                <span class="ad-topbar__live" style="margin-left:0.5rem;">Ao Vivo</span>
            </div>
        </div>
        <div class="ad-desktop-topbar__right">
            <a href="?period=today" class="ad-period__btn {{ $period === 'today' ? 'is-active' : '' }}" style="background:white;border:1px solid #e5e5e5;">Hoje</a>
            <a href="?period=week" class="ad-period__btn {{ $period === 'week' ? 'is-active' : '' }}" style="background:white;border:1px solid #e5e5e5;">7 dias</a>
            <a href="?period=all" class="ad-period__btn {{ $period === 'all' ? 'is-active' : '' }}" style="background:white;border:1px solid #e5e5e5;">Total</a>
            <button class="ad-refresh-btn" @click="refresh()" title="Atualizar">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" :class="{'ks-spin': loading}">
                    <path d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114-3M20 15a9 9 0 01-14 3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- ============ OVERLAY ============ --}}
    <div class="ad-overlay" :class="{'is-open': sidebarOpen}" @click="sidebarOpen = false"></div>

    {{-- ============ SIDEBAR ============ --}}
    <aside class="ad-sidebar" :class="{'is-open': sidebarOpen}">
        <div class="ad-sidebar__logo">
            <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe">
            <div>
                <div class="ad-sidebar__brand">KwanzaSafe</div>
                <div class="ad-sidebar__sub">Admin</div>
            </div>
        </div>

        <div class="ad-sidebar__user">
            <div class="ad-sidebar__avatar">
                @if(ks_file($user->profile_photo_path))
                    <img src="{{ ks_file($user->profile_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($firstName, 0, 1)) }}
                @endif
            </div>
            <div class="ad-sidebar__user-info">
                <div class="ad-sidebar__user-name">{{ Str::limit($user->full_name ?? 'Admin', 18) }}</div>
                <div class="ad-sidebar__user-role">Administrador</div>
            </div>
        </div>

        <nav class="ad-sidebar__nav">
            <div class="ad-sidebar__label">Operações</div>
            <a href="{{ route('admin.dashboard') }}" class="ad-nav is-active" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Visão Geral
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transações
                <span class="ad-nav__badge info" x-show="stats.tx_pending > 0" x-text="stats.tx_pending" x-cloak></span>
            </a>
            <a href="{{ route('admin.kyc.index') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/></svg>
                KYC
                <span class="ad-nav__badge warn" x-show="stats.kyc_pending > 0" x-text="stats.kyc_pending" x-cloak></span>
            </a>
            <a href="{{ route('admin.messages.unread') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Mensagens
                <span class="ad-nav__badge" x-show="stats.unread_chats > 0" x-text="stats.unread_chats" x-cloak></span>
            </a>

            <div class="ad-sidebar__label">Gestão</div>
            <a href="{{ route('admin.users.index') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857"/></svg>
                Utilizadores
            </a>
            <a href="{{ route('admin.rates.index') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Taxas
            </a>
            <a href="{{ route('admin.audit.index') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Audit Trail
            </a>

            <div class="ad-sidebar__label">Conta</div>
            <a href="{{ route('dashboard') }}" class="ad-nav" @click="sidebarOpen = false">
                <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Vista Cliente
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ad-nav" style="color:#fca5a5;">
                    <svg class="ad-nav__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </button>
            </form>
        </nav>

        <div class="ad-sidebar__foot">KwanzaSafe © 2026</div>
    </aside>

    {{-- ============ CONTENT ============ --}}
    <div class="ad-content">

        @if(session('success'))
            <div class="ad-flash success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTROS PERIODO (mobile) --}}
        <div class="ad-period" style="display:flex;" class:hidden:lg="true">
            <a href="?period=today" class="ad-period__btn {{ $period === 'today' ? 'is-active' : '' }}">Hoje</a>
            <a href="?period=week" class="ad-period__btn {{ $period === 'week' ? 'is-active' : '' }}">7 dias</a>
            <a href="?period=all" class="ad-period__btn {{ $period === 'all' ? 'is-active' : '' }}">Total</a>
        </div>

        {{-- ============ STATS ============ --}}
        <div class="ad-stats">
            <a href="{{ route('admin.users.index') }}" class="ad-stat s-users">
                <div class="ad-stat__head">
                    <div class="ad-stat__icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857"/></svg>
                    </div>
                    <svg class="ad-stat__arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="ad-stat__label">Utilizadores</div>
                <div class="ad-stat__value ad-font-display" x-text="formatNum(stats.total_users)" :class="{'flash-up': flashing.total_users}">{{ number_format($stats['total_users']) }}</div>
                <div class="ad-stat__sub">
                    <span class="ad-stat__sub-good" x-text="`${formatNum(stats.kyc_approved)} aprovados`"></span>
                </div>
            </a>

            <a href="{{ route('admin.kyc.index') }}" class="ad-stat s-kyc">
                <div class="ad-stat__head">
                    <div class="ad-stat__icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/></svg>
                    </div>
                    <svg class="ad-stat__arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="ad-stat__label">KYC Pendentes</div>
                <div class="ad-stat__value ad-font-display" style="color:#f59e0b;" x-text="formatNum(stats.kyc_pending)" :class="{'flash-up': flashing.kyc_pending}">{{ number_format($stats['kyc_pending']) }}</div>
                <div class="ad-stat__sub">
                    <span x-show="stats.kyc_pending > 0" class="ad-stat__sub-warn">Rever urgente →</span>
                    <span x-show="stats.kyc_pending === 0" class="ad-stat__sub-good">✓ Tudo em dia</span>
                </div>
            </a>

            <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}" class="ad-stat s-tx">
                <div class="ad-stat__head">
                    <div class="ad-stat__icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <svg class="ad-stat__arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="ad-stat__label">Tx Pendentes</div>
                <div class="ad-stat__value ad-font-display" x-text="formatNum(stats.tx_pending)" :class="{'flash-up': flashing.tx_pending}">{{ number_format($stats['tx_pending']) }}</div>
                <div class="ad-stat__sub" x-text="`${formatNum(stats.tx_completed)} concluídas`"></div>
            </a>

            <a href="{{ route('admin.transactions.index', ['period' => 'today', 'status' => 'completed']) }}" class="ad-stat s-vol">
                <div class="ad-stat__head">
                    <div class="ad-stat__icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <svg class="ad-stat__arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="ad-stat__label">Volume {{ $period === 'today' ? 'Hoje' : ($period === 'week' ? '7d' : 'Total') }}</div>
                <div class="ad-stat__value ad-font-display" style="font-size:1.1rem;" x-text="`${formatNum(stats.volume_aoa)} Kz`" :class="{'flash-up': flashing.volume_aoa}">{{ number_format($stats['volume_aoa'], 0, ',', '.') }} Kz</div>
                <div class="ad-stat__sub">Em AOA</div>
            </a>

            <a href="{{ route('admin.messages.unread') }}" class="ad-stat s-chat" style="grid-column: 1 / -1;">
                <div class="ad-stat__head">
                    <div class="ad-stat__icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <svg class="ad-stat__arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div class="ad-stat__label">Mensagens Por Ler</div>
                <div class="ad-stat__value ad-font-display" style="color:#dc2626;" x-text="formatNum(stats.unread_chats)" :class="{'flash-up': flashing.unread_chats}">{{ number_format($stats['unread_chats']) }}</div>
                <div class="ad-stat__sub">
                    <span x-show="stats.unread_chats > 0" style="color:#dc2626;font-weight:600;">Responder ASAP</span>
                    <span x-show="stats.unread_chats === 0" class="ad-stat__sub-good">✓ Tudo em dia</span>
                </div>
            </a>
        </div>

        {{-- ============ GRÁFICO ============ --}}
        <div class="ad-chart">
            <div class="ad-chart__head">
                <div>
                    <div class="ad-chart__title ad-font-display">Volume Diário</div>
                    <div class="ad-chart__sub">Últimos 7 dias · AOA</div>
                </div>
                <div class="ad-chart__total">
                    <div class="ad-chart__total-num" x-text="`${chartTotal} Kz`"></div>
                    <div>acumulado</div>
                </div>
            </div>
            @php $maxVolume = max(array_column($chartData, 'volume')) ?: 1; @endphp
            <div class="ad-chart__bars">
                @foreach($chartData as $day)
                    @php $heightPct = ($day['volume'] / $maxVolume) * 100; @endphp
                    <div class="ad-chart__col">
                        <div class="ad-chart__bar {{ $day['volume'] == 0 ? 'empty' : '' }}" style="height: {{ max($heightPct, 2) }}%;">
                            <div class="ad-chart__bar-value">
                                {{ $day['count'] > 0 ? number_format($day['volume'], 0, ',', '.') . ' Kz' : '—' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="ad-chart__labels">
                @foreach($chartData as $day)
                    <div class="ad-chart__label">{{ $day['label'] }}</div>
                @endforeach
            </div>
        </div>

        {{-- ============ PAINÉIS ============ --}}
        <div class="ad-panels">

            <div>
                <div class="ad-panel">
                    <div class="ad-panel__head">
                        <div class="ad-panel__title">💸 Pendentes <span class="ad-panel__count">{{ $pendingTransactions->count() }}</span></div>
                        <a href="{{ route('admin.transactions.index', ['status' => 'pending']) }}" class="ad-panel__link">Todas →</a>
                    </div>
                    @if($pendingTransactions->isEmpty())
                        <div class="ad-empty">
                            <div class="ad-empty__icon">✅</div>
                            <div class="ad-empty__text">Nenhuma transação pendente</div>
                        </div>
                    @else
                        @foreach($pendingTransactions as $tx)
                            <a href="{{ route('admin.transaction.show', $tx->id) }}" class="ad-row">
                                <div class="ad-row__badge {{ $tx->status }}">
                                    @if($tx->status === 'processing') ⏳
                                    @else 💰 @endif
                                </div>
                                <div class="ad-row__info">
                                    <div class="ad-row__title">#{{ $tx->reference_id }}</div>
                                    <div class="ad-row__sub">{{ Str::limit(optional($tx->user)->email ?? '—', 26) }}</div>
                                </div>
                                <div>
                                    <div class="ad-row__amount">{{ number_format($tx->amount_sent, 2, ',', '.') }} {{ $tx->currency_from }}</div>
                                    <div class="ad-row__currency">{{ number_format($tx->amount_received, 0, ',', '.') }} Kz</div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>

            <div>
                <div class="ad-panel">
                    <div class="ad-panel__head">
                        <div class="ad-panel__title">🪪 KYC <span class="ad-panel__count">{{ $pendingKycUsers->count() }}</span></div>
                        <a href="{{ route('admin.kyc.index') }}" class="ad-panel__link">Todos →</a>
                    </div>
                    @if($pendingKycUsers->isEmpty())
                        <div class="ad-empty">
                            <div class="ad-empty__icon">✅</div>
                            <div class="ad-empty__text">Tudo em dia</div>
                        </div>
                    @else
                        @foreach($pendingKycUsers as $u)
                            <a href="{{ route('admin.kyc.show', $u->id) }}" class="ad-kyc-row">
                                <div class="ad-kyc-row__avatar">
                                    @if(ks_file($u->profile_photo_path))
                                        <img src="{{ ks_file($u->profile_photo_path) }}" alt="">
                                    @else
                                        {{ strtoupper(substr($u->full_name ?? $u->email, 0, 1)) }}
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div class="ad-kyc-row__name">{{ Str::limit($u->full_name ?? $u->email, 18) }}</div>
                                    <div class="ad-kyc-row__email">{{ Str::limit($u->email, 22) }}</div>
                                    <div class="ad-kyc-row__time">{{ $u->updated_at->diffForHumans() }}</div>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>

                <div class="ad-panel">
                    <div class="ad-panel__head">
                        <div class="ad-panel__title">💱 Taxas</div>
                        <a href="{{ route('admin.rates.index') }}" class="ad-panel__link">Gerir →</a>
                    </div>
                    @foreach($rates as $r)
                        <div class="ad-rate">
                            <div>
                                <div class="ad-rate__pair">{{ $r->currency_from }} → AOA</div>
                                <div class="ad-rate__time">{{ $r->updated_at->diffForHumans() }}</div>
                            </div>
                            <div>
                                <span class="ad-rate__value">{{ number_format($r->rate, 2, ',', '.') }}</span>
                                <span class="ad-rate__status {{ $r->is_active ? 'active' : 'inactive' }}">{{ $r->is_active ? 'Ativa' : 'Inativa' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ============ TOASTS ============ --}}
    <div class="ad-toasts">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="ad-toast" :class="toast.type">
                <div class="ad-toast__icon">
                    <svg x-show="toast.type === 'success'" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <svg x-show="toast.type === 'warn'" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/></svg>
                    <svg x-show="toast.type === 'info'" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
                </div>
                <div class="ad-toast__text">
                    <div class="ad-toast__title" x-text="toast.title"></div>
                    <div class="ad-toast__sub" x-text="toast.message"></div>
                </div>
                <button class="ad-toast__close" @click="dismissToast(toast.id)">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-linecap="round"/></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- ============ BOTTOM NAV (mobile) ============ --}}
    <nav class="ad-bottom" aria-label="Navegação admin">
        <a href="{{ route('admin.dashboard') }}" class="ad-bottom-btn is-active">
            <svg class="ad-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.transactions.index') }}" class="ad-bottom-btn">
            <svg class="ad-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Tx</span>
            <span class="ad-bottom-btn__badge" x-show="stats.tx_pending > 0" x-text="stats.tx_pending" x-cloak></span>
        </a>
        <a href="{{ route('admin.kyc.index') }}" class="ad-bottom-btn">
            <svg class="ad-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/></svg>
            <span>KYC</span>
            <span class="ad-bottom-btn__badge" x-show="stats.kyc_pending > 0" x-text="stats.kyc_pending" x-cloak></span>
        </a>
        <a href="{{ route('admin.messages.unread') }}" class="ad-bottom-btn">
            <svg class="ad-bottom-btn__icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span>Mensagens</span>
            <span class="ad-bottom-btn__badge" x-show="stats.unread_chats > 0" x-text="stats.unread_chats" x-cloak></span>
        </a>
    </nav>

</div>

@push('scripts')
<script>
function adminDashboard() {
    return {
        sidebarOpen: false,
        loading: false,
        lastUpdate: '{{ now()->format("H:i:s") }}',
        period: '{{ $period }}',
        stats: @json($stats),
        flashing: {},
        toasts: [],
        toastIdCounter: 1,
        refreshInterval: null,

        init() {
            // Auto-refresh 30s
            this.refreshInterval = setInterval(() => {
                this.refresh(true);
            }, 30000);

            // Pausa quando aba não está visível (poupa bateria)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(this.refreshInterval);
                } else {
                    this.refresh(true);
                    this.refreshInterval = setInterval(() => this.refresh(true), 30000);
                }
            });

            // Bloqueio scroll quando sidebar aberta
            this.$watch('sidebarOpen', open => {
                document.body.style.overflow = open ? 'hidden' : '';
            });

            window.addEventListener('beforeunload', () => {
                clearInterval(this.refreshInterval);
            });
        },

        async refresh(silent = false) {
            if (this.loading) return;
            this.loading = true;
            try {
                const response = await fetch(`{{ route('admin.stats.json') }}?period=${this.period}`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                this.detectChanges(this.stats, data.stats);
                this.stats = data.stats;
                this.lastUpdate = data.timestamp;
            } catch (e) {
                if (!silent) this.showToast('warn', 'Erro', 'Não foi possível atualizar.');
            } finally {
                this.loading = false;
            }
        },

        detectChanges(oldStats, newStats) {
            const fields = ['tx_pending', 'kyc_pending', 'unread_chats', 'volume_aoa', 'total_users'];
            fields.forEach(field => {
                const oldVal = oldStats[field] || 0;
                const newVal = newStats[field] || 0;
                if (newVal > oldVal) {
                    this.flashing[field] = true;
                    setTimeout(() => { this.flashing[field] = false; }, 1500);

                    if (field === 'tx_pending') {
                        this.showToast('info', 'Nova transação', `${newVal - oldVal} nova(s) pendente(s)`);
                        if (window.ksNotify) window.ksNotify();
                    } else if (field === 'unread_chats') {
                        this.showToast('warn', 'Nova mensagem', `${newVal - oldVal} mensagem(ns) por ler`);
                        if (window.ksNotify) window.ksNotify();
                    } else if (field === 'kyc_pending') {
                        this.showToast('info', 'Novo KYC', `${newVal - oldVal} para revisar`);
                    } else if (field === 'total_users') {
                        this.showToast('success', 'Novo utilizador', `+${newVal - oldVal} registado(s)`);
                    }
                }
            });
        },

        showToast(type, title, message) {
            const id = this.toastIdCounter++;
            this.toasts.push({ id, type, title, message });
            setTimeout(() => this.dismissToast(id), 6000);
        },

        dismissToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        formatNum(val) {
            if (val === undefined || val === null) return '0';
            if (typeof val !== 'number') val = parseFloat(val) || 0;
            return val.toLocaleString('pt-PT', { maximumFractionDigits: 0 });
        },

        get chartTotal() {
            const total = @json(array_sum(array_column($chartData, 'volume')));
            return total.toLocaleString('pt-PT', { maximumFractionDigits: 0 });
        }
    };
}
</script>

<style>
.ks-spin { animation: ks-spin 1s linear infinite; }
@keyframes ks-spin { to { transform: rotate(360deg); } }
</style>
@endpush

</x-app-layout>
