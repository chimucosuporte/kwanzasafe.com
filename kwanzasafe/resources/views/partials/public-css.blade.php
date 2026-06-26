{{-- Sistema de design do site público (tokens + componentes). Fonte única. --}}
<style>
:root {
    /* Marca */
    --ks-green: #009d44;
    --ks-green-bright: #00b450;
    --ks-green-dark: #007a34;        /* texto verde com contraste AA */
    --ks-green-darker: #00692e;
    --ks-green-light: #d1f2e0;
    --ks-green-pale: #f0faf4;
    /* Neutros */
    --ks-ink: #0f172a;
    --ks-ink-soft: #334155;
    --ks-gray-50: #f8fafc;
    --ks-gray-100: #f1f5f9;
    --ks-gray-200: #e5e7eb;
    --ks-gray-300: #cbd5e1;
    --ks-gray-400: #94a3b8;
    --ks-gray-500: #64748b;
    --ks-gray-600: #475569;
    --ks-gray-700: #334155;
    --ks-white: #ffffff;
    /* Estados */
    --ks-danger: #dc2626;
    --ks-warn: #d97706;
    --ks-info: #2563eb;
    /* Tipografia */
    --ks-font-display: 'Syne', system-ui, sans-serif;
    --ks-font-body: 'DM Sans', system-ui, -apple-system, sans-serif;
    --ks-font-mono: 'JetBrains Mono', monospace;
    /* Raios / sombras / espaço */
    --ks-r-sm: 10px; --ks-r: 14px; --ks-r-lg: 20px; --ks-r-pill: 999px;
    --ks-shadow-sm: 0 1px 3px rgba(15,23,42,0.06);
    --ks-shadow: 0 8px 28px rgba(15,23,42,0.08);
    --ks-shadow-lg: 0 24px 60px rgba(15,23,42,0.12);
    --ks-container: 1200px;
}

/* ===== Base ===== */
* { box-sizing: border-box; }
html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
body { margin: 0; font-family: var(--ks-font-body); color: var(--ks-ink); background: var(--ks-white); -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; line-height: 1.6; overflow-wrap: break-word; }
img { max-width: 100%; height: auto; display: block; }
a { color: inherit; }
[x-cloak] { display: none !important; }
::selection { background: var(--ks-green); color: #fff; }
:focus-visible { outline: 3px solid var(--ks-green); outline-offset: 2px; border-radius: 4px; }

/* ===== Tipografia ===== */
.ks-display { font-family: var(--ks-font-display); }
.ks-mono { font-family: var(--ks-font-mono); }
.ks-h1 { font-family: var(--ks-font-display); font-weight: 800; font-size: clamp(2.1rem, 5vw, 3.4rem); line-height: 1.08; letter-spacing: -0.02em; margin: 0; }
.ks-h2 { font-family: var(--ks-font-display); font-weight: 800; font-size: clamp(1.7rem, 3.6vw, 2.5rem); line-height: 1.15; letter-spacing: -0.02em; margin: 0; }
.ks-h3 { font-family: var(--ks-font-display); font-weight: 700; font-size: 1.25rem; line-height: 1.3; margin: 0; }
.ks-lead { font-size: clamp(1rem, 1.6vw, 1.18rem); color: var(--ks-gray-600); }
.ks-eyebrow { font-weight: 700; font-size: 0.78rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ks-green-dark); }

/* ===== Layout ===== */
.ks-container { max-width: var(--ks-container); margin: 0 auto; padding-left: 1.5rem; padding-right: 1.5rem; }
.ks-sec { padding: clamp(3.5rem, 7vw, 6rem) 0; }
.ks-sec--gray { background: var(--ks-gray-50); }
.ks-sec--ink { background: var(--ks-ink); color: var(--ks-white); }
.ks-sec--green { background: linear-gradient(135deg, var(--ks-green) 0%, var(--ks-green-dark) 100%); color: var(--ks-white); }
.ks-skip { position: absolute; left: -999px; top: 0; z-index: 200; background: var(--ks-ink); color: #fff; padding: 0.75rem 1.25rem; border-radius: 0 0 var(--ks-r-sm) 0; font-weight: 600; }
.ks-skip:focus { left: 0; }

/* ===== Botões (x-button) ===== */
.ks-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem; font-family: var(--ks-font-display); font-weight: 700; font-size: 0.95rem; line-height: 1; padding: 0.85rem 1.5rem; border-radius: var(--ks-r-pill); border: 1.5px solid transparent; cursor: pointer; text-decoration: none; transition: transform .15s, box-shadow .15s, background .15s, color .15s, border-color .15s; min-height: 46px; }
.ks-btn:disabled, .ks-btn[aria-disabled="true"] { opacity: .55; cursor: not-allowed; }
.ks-btn--primary { background: var(--ks-green); color: #fff; box-shadow: 0 6px 18px rgba(0,157,68,.26); }
.ks-btn--primary:hover:not(:disabled) { background: var(--ks-green-dark); transform: translateY(-1px); box-shadow: 0 10px 24px rgba(0,157,68,.34); }
.ks-btn--ghost { background: var(--ks-white); color: var(--ks-ink); border-color: var(--ks-gray-200); }
.ks-btn--ghost:hover { border-color: var(--ks-green); color: var(--ks-green-dark); }
.ks-btn--outline { background: transparent; color: #fff; border-color: rgba(255,255,255,.5); }
.ks-btn--outline:hover { background: rgba(255,255,255,.12); border-color: #fff; }
.ks-btn--block { width: 100%; }
.ks-btn--lg { padding: 1.05rem 1.85rem; font-size: 1rem; min-height: 54px; }
.ks-btn__spin { width: 16px; height: 16px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: ksSpin .7s linear infinite; }
@keyframes ksSpin { to { transform: rotate(360deg); } }

/* ===== Inputs (x-input) ===== */
.ks-field { display: block; margin-bottom: 1.1rem; }
.ks-field__label { display: block; font-weight: 600; font-size: 0.85rem; color: var(--ks-ink-soft); margin-bottom: 0.4rem; }
.ks-field__control { display: flex; align-items: center; background: var(--ks-gray-50); border: 1.5px solid var(--ks-gray-200); border-radius: var(--ks-r-sm); transition: border-color .15s, background .15s, box-shadow .15s; }
.ks-field__control:focus-within { border-color: var(--ks-green); background: #fff; box-shadow: 0 0 0 4px rgba(0,157,68,.1); }
.ks-field__control input { flex: 1; min-width: 0; border: none; background: transparent; outline: none; padding: 0.85rem 0.95rem; font-family: var(--ks-font-body); font-size: 1rem; color: var(--ks-ink); }
.ks-field__control.has-error { border-color: var(--ks-danger); }
.ks-field__error { display: block; color: var(--ks-danger); font-size: 0.8rem; font-weight: 500; margin-top: 0.35rem; }
.ks-field__toggle { background: none; border: none; cursor: pointer; color: var(--ks-gray-400); padding: 0 0.85rem; display: flex; align-items: center; }
.ks-field__toggle:hover { color: var(--ks-green-dark); }

/* ===== Card / badge ===== */
.ks-card { background: #fff; border: 1px solid var(--ks-gray-200); border-radius: var(--ks-r-lg); padding: 1.75rem; box-shadow: var(--ks-shadow-sm); }
.ks-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: var(--ks-green-pale); color: var(--ks-green-dark); border: 1px solid var(--ks-green-light); border-radius: var(--ks-r-pill); padding: 0.4rem 0.85rem; font-size: 0.8rem; font-weight: 600; }

/* ===== Reveal ao scroll ===== */
.ks-reveal { opacity: 0; transform: translateY(26px); transition: opacity .65s cubic-bezier(.4,0,.2,1), transform .65s cubic-bezier(.4,0,.2,1); will-change: opacity, transform; }
.ks-reveal.ks-in { opacity: 1; transform: translateY(0); }
.ks-reveal.d1 { transition-delay: .08s; } .ks-reveal.d2 { transition-delay: .16s; }
.ks-reveal.d3 { transition-delay: .24s; } .ks-reveal.d4 { transition-delay: .32s; }
@media (prefers-reduced-motion: reduce) {
    .ks-reveal, .ks-menu-panel .ks-menu-nav a { opacity: 1 !important; transform: none !important; transition: none !important; animation: none !important; }
    html { scroll-behavior: auto; }
}

/* ===== WhatsApp flutuante ===== */
.ks-wa-float { position: fixed; bottom: 24px; right: 24px; width: 56px; height: 56px; border-radius: 50%; background: #25d366; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 28px rgba(37,211,102,.42); z-index: 80; transition: transform .2s, background .2s; }
.ks-wa-float:hover { transform: scale(1.08); background: #1eb555; }
.ks-wa-float::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 3px solid #25d366; opacity: .4; animation: ksWaPulse 2.2s infinite; }
@keyframes ksWaPulse { 0%,100% { opacity: .4; transform: scale(1); } 50% { opacity: 0; transform: scale(1.25); } }
@media (prefers-reduced-motion: reduce) { .ks-wa-float::before { animation: none; } }

/* ===== Header ===== */
.ksh { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,0.9); backdrop-filter: blur(12px); border-bottom: 1px solid transparent; transition: box-shadow .3s, border-color .3s, background .3s; }
.ksh.scrolled { box-shadow: 0 6px 24px rgba(15,23,42,.07); border-bottom-color: var(--ks-gray-200); background: rgba(255,255,255,.97); }
.ksh__inner { max-width: var(--ks-container); margin: 0 auto; padding: 0.9rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; transition: padding .3s; }
.ksh.scrolled .ksh__inner { padding-top: 0.6rem; padding-bottom: 0.6rem; }
.ksh__logo { display: inline-flex; align-items: center; }
.ksh__logo img { height: 34px; width: auto; }
.ksh__logo .ic { display: none; }
.ksh__nav { display: flex; align-items: center; gap: 1.9rem; }
.ksh__nav a { color: var(--ks-gray-700); font-weight: 500; font-size: 0.92rem; text-decoration: none; transition: color .15s; position: relative; }
.ksh__nav a:not(.ks-btn):hover { color: var(--ks-green-dark); }
.ksh__nav a:not(.ks-btn)::after { content: ''; position: absolute; left: 0; right: 0; bottom: -6px; height: 2px; background: var(--ks-green); transform: scaleX(0); transition: transform .2s; border-radius: 2px; }
.ksh__nav a:not(.ks-btn):hover::after { transform: scaleX(1); }
.ksh__burger { display: none; width: 46px; height: 46px; border: none; background: none; cursor: pointer; position: relative; flex-shrink: 0; border-radius: 12px; transition: background .15s; }
.ksh__burger:hover { background: var(--ks-gray-100); }
.ksh__burger span { position: absolute; left: 13px; right: 13px; height: 2.5px; background: var(--ks-ink); border-radius: 3px; transition: transform .32s cubic-bezier(.4,0,.2,1), opacity .2s, top .32s cubic-bezier(.4,0,.2,1); }
.ksh__burger span:nth-child(1) { top: 16px; } .ksh__burger span:nth-child(2) { top: 22px; } .ksh__burger span:nth-child(3) { top: 28px; }
.ksh__burger.open span:nth-child(1) { top: 22px; transform: rotate(45deg); }
.ksh__burger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.ksh__burger.open span:nth-child(3) { top: 22px; transform: rotate(-45deg); }
.ksh-overlay { display: none; position: fixed; inset: 0; background: rgba(10,13,11,.45); backdrop-filter: blur(2px); opacity: 0; visibility: hidden; transition: opacity .3s, visibility .3s; z-index: 60; }
.ksh-overlay.show { opacity: 1; visibility: visible; }
.ksh-drawer { display: none; position: fixed; top: 0; right: 0; bottom: 0; width: min(85vw, 350px); background: #fff; z-index: 70; transform: translateX(105%); transition: transform .4s cubic-bezier(.4,0,.2,1); flex-direction: column; padding: 5.5rem 1.85rem 2rem; box-shadow: -16px 0 50px rgba(15,23,42,.12); }
.ksh-drawer.open { transform: translateX(0); }
.ksh-drawer__nav { display: flex; flex-direction: column; }
.ksh-drawer__nav a { font-family: var(--ks-font-display); font-weight: 700; font-size: 1.18rem; color: var(--ks-ink); text-decoration: none; padding: 0.85rem 0; border-bottom: 1px solid var(--ks-gray-100); opacity: 0; transform: translateX(24px); }
.ksh-drawer.open .ksh-drawer__nav a { animation: kshIn .45s cubic-bezier(.4,0,.2,1) forwards; animation-delay: calc(var(--i) * .06s); }
@keyframes kshIn { to { opacity: 1; transform: translateX(0); } }
.ksh-drawer__nav a:hover { color: var(--ks-green-dark); }
.ksh-drawer__nav .cta { color: var(--ks-green-dark) !important; }
.ksh-drawer__close { position: absolute; top: 1.4rem; right: 1.4rem; width: 42px; height: 42px; border: none; background: var(--ks-gray-100); border-radius: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--ks-ink); }
.ksh-drawer__foot { margin-top: auto; }
.ksh-wa { display: inline-flex; align-items: center; gap: .5rem; background: #25d366; color: #fff; text-decoration: none; font-weight: 700; font-size: .9rem; padding: .75rem 1.25rem; border-radius: var(--ks-r-pill); }
@media (max-width: 820px) {
    .ksh__logo img { height: 30px; }
    .ksh__logo .full { display: none; } .ksh__logo .ic { display: block; }
    .ksh__nav { display: none; }
    .ksh__burger { display: block; }
    .ksh-overlay { display: block; }
    .ksh-drawer { display: flex; }
}

/* ===== Hero banner ===== */
.ks-sronly { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
.ks-herobanner__link { display: block; }
.ks-herobanner__link img { width: 100%; height: auto; display: block; }
.ks-herobanner__trust { display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; padding: 1.5rem; background: var(--ks-green-pale); border-bottom: 1px solid var(--ks-green-light); }
.ks-htrust { display: inline-flex; align-items: center; gap: .45rem; font-size: .85rem; font-weight: 600; color: var(--ks-green-dark); }
.ks-htrust svg { flex-shrink: 0; }

/* ===== Carrossel do hero ===== */
.ks-bcar { position: relative; overflow: hidden; }
.ks-bcar-track { display: flex; transition: transform .7s cubic-bezier(.4,0,.2,1); }
.ks-bcar-slide { min-width: 100%; display: block; }
.ks-bcar-slide img { width: 100%; height: auto; display: block; }
.ks-bcar-dots { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 3; }
.ks-bcar-dot { width: 9px; height: 9px; border-radius: 50%; border: none; background: rgba(255,255,255,.55); cursor: pointer; padding: 0; transition: all .25s; }
.ks-bcar-dot:hover { background: rgba(255,255,255,.85); }
.ks-bcar-dot.active { background: #fff; width: 28px; border-radius: 5px; }
.ks-bcar-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 50%; border: none; background: rgba(255,255,255,.85); color: var(--ks-ink); font-size: 1.6rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 3; opacity: 0; transition: opacity .2s, background .2s; box-shadow: 0 4px 14px rgba(0,0,0,.18); }
.ks-bcar:hover .ks-bcar-arrow, .ks-bcar-arrow:focus-visible { opacity: 1; }
.ks-bcar-arrow:hover { background: #fff; }
.ks-bcar-arrow.prev { left: 16px; padding-right: 3px; }
.ks-bcar-arrow.next { right: 16px; padding-left: 3px; }
@media (max-width: 700px) { .ks-bcar-arrow { display: none; } .ks-bcar-dots { bottom: 10px; } }
@media (prefers-reduced-motion: reduce) { .ks-bcar-track { transition: none; } }

/* ===== Footer ===== */
.ksf { background: var(--ks-ink); color: var(--ks-gray-300); padding: 4rem 0 2rem; }
.ksf__grid { display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr; gap: 2.5rem; }
.ksf__brand { display: flex; align-items: center; gap: .6rem; margin-bottom: 1rem; }
.ksf__brand img { height: 30px; width: auto; }
.ksf__brand span { font-family: var(--ks-font-display); font-weight: 800; font-size: 1.1rem; color: #fff; }
.ksf__desc { font-size: .9rem; line-height: 1.7; color: var(--ks-gray-400); max-width: 38ch; }
.ksf__wa { display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.1rem; color: #fff; text-decoration: none; font-weight: 600; font-size: .9rem; }
.ksf__wa:hover { color: var(--ks-green-bright); }
.ksf h4 { font-family: var(--ks-font-display); font-weight: 700; font-size: .82rem; letter-spacing: .08em; text-transform: uppercase; color: #fff; margin: 0 0 1rem; }
.ksf ul { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: .65rem; }
.ksf ul a { color: var(--ks-gray-400); text-decoration: none; font-size: .9rem; transition: color .15s; }
.ksf ul a:hover { color: #fff; }
.ksf__seal { display: inline-flex; align-items: center; gap: .4rem; background: rgba(0,157,68,.16); color: var(--ks-green-bright); border: 1px solid rgba(0,157,68,.3); border-radius: var(--ks-r-pill); padding: .35rem .8rem; font-size: .72rem; font-weight: 700; margin-top: 1rem; }
.ksf__bottom { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 3rem; padding-top: 1.75rem; border-top: 1px solid rgba(255,255,255,.08); flex-wrap: wrap; }
.ksf__copy { font-size: .8rem; color: var(--ks-gray-500); }
.ksf__social a { color: var(--ks-gray-400); transition: color .15s; }
.ksf__social a:hover { color: var(--ks-green-bright); }
@media (max-width: 820px) { .ksf__grid { grid-template-columns: 1fr 1fr; gap: 2rem; } .ksf__grid > :first-child { grid-column: 1 / -1; } }
@media (max-width: 480px) { .ksf__grid { grid-template-columns: 1fr; } }
</style>
