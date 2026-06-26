{{--
|==========================================================================
| KwanzaSafe — Layout Partilhado das Páginas Legais
|==========================================================================
| Usado por: legal/terms.blade.php e legal/privacy.blade.php
| Variáveis esperadas:
|   $pageTitle       (string) - Título da página
|   $pageDescription (string) - Meta description
|   $pageKeywords    (string) - Meta keywords
|   $pageCanonical   (string) - URL canónica
|   $lastUpdated     (string) - Data última atualização "22 de Abril de 2026"
| Slots esperados:
|   $slot            (content) - Conteúdo principal da página
|==========================================================================
--}}
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO PRIMARY --}}
    <title>{{ $pageTitle }} | KwanzaSafe</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="{{ $pageKeywords }}">
    <meta name="author" content="KwanzaSafe">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $pageCanonical }}">

    {{-- OPEN GRAPH --}}
    <meta property="og:type" content="article">
    <meta property="og:locale" content="pt_AO">
    <meta property="og:url" content="{{ $pageCanonical }}">
    <meta property="og:site_name" content="KwanzaSafe">
    <meta property="og:title" content="{{ $pageTitle }} — KwanzaSafe">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ asset('assets/images/logos/logo1.png') }}">

    {{-- TWITTER --}}
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }} — KwanzaSafe">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    {{-- FAVICON --}}
    @include('partials.public-favicons')

    {{-- FONTES --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- TAILWIND + ALPINE --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --ks-green: #009d44;
            --ks-green-dark: #007a34;
            --ks-green-light: #d1f2e0;
            --ks-green-pale: #f0faf4;
            --ks-black: #000000;
            --ks-white: #ffffff;
            --ks-gray-50: #fafafa;
            --ks-gray-100: #f5f5f5;
            --ks-gray-200: #e5e5e5;
            --ks-gray-300: #d4d4d4;
            --ks-gray-500: #737373;
            --ks-gray-700: #404040;
            --ks-gray-900: #171717;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; color: var(--ks-black); background: var(--ks-white); margin: 0; padding: 0; -webkit-font-smoothing: antialiased; line-height: 1.6; }
        .font-display { font-family: 'Syne', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        a:focus-visible, button:focus-visible { outline: 3px solid var(--ks-green-dark); outline-offset: 2px; border-radius: 6px; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }

        /* ============ HEADER ============ */
        .lg-header { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,0.95); backdrop-filter: blur(12px); border-bottom: 1px solid var(--ks-gray-200); }
        .lg-header-inner { max-width: 1200px; margin: 0 auto; padding: 0.875rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }
        .lg-logo-link { display: flex; align-items: center; gap: 0.625rem; text-decoration: none; }
        .lg-logo-img { height: 36px; width: auto; }
        .lg-logo-img.mobile { display: none; }
        .lg-logo-img.tablet { display: none; }
        .lg-back { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; color: var(--ks-gray-700); text-decoration: none; transition: color 0.15s; }
        .lg-back:hover { color: var(--ks-green); }

        @media(max-width: 768px) {
            .lg-logo-img { display: none; }
            .lg-logo-img.mobile { display: block; height: 32px; }
        }
        @media(min-width: 769px) and (max-width: 1024px) {
            .lg-logo-img { display: none; }
            .lg-logo-img.tablet { display: block; height: 34px; }
        }

        /* ============ HERO LEGAL ============ */
        .lg-hero { background: linear-gradient(180deg, var(--ks-green-pale) 0%, var(--ks-white) 100%); padding: 3rem 1.5rem 2.5rem; border-bottom: 1px solid var(--ks-gray-200); }
        .lg-hero-inner { max-width: 860px; margin: 0 auto; text-align: center; }
        .lg-breadcrumb { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--ks-gray-500); margin-bottom: 1rem; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.08em; text-transform: uppercase; }
        .lg-breadcrumb a { color: var(--ks-green); text-decoration: none; }
        .lg-hero-title { font-family: 'Syne', sans-serif; font-size: 2.75rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1.1; margin: 0 0 0.875rem; color: var(--ks-black); }
        .lg-hero-sub { font-size: 1.05rem; color: var(--ks-gray-700); margin: 0 0 1rem; line-height: 1.6; }
        .lg-last-updated { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--ks-white); border: 1px solid var(--ks-gray-200); padding: 0.4rem 0.875rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; color: var(--ks-gray-700); }
        .lg-last-updated svg { color: var(--ks-green); }

        @media(max-width: 768px) { .lg-hero { padding: 2rem 1rem 2rem; } .lg-hero-title { font-size: 1.75rem; } }

        /* ============ CONTENT (TOC sticky + artigo) ============ */
        .lg-content { max-width: 1080px; margin: 0 auto; padding: 2.75rem 1.5rem 4rem; }
        .lg-layout { display: grid; grid-template-columns: 250px 1fr; gap: 3.5rem; align-items: start; }
        .lg-toc-side { position: relative; }
        .lg-toc-sticky { position: sticky; top: 88px; max-height: calc(100dvh - 110px); overflow-y: auto; padding-right: .5rem; }
        .lg-toc-title { font-family: 'Syne', sans-serif; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: var(--ks-gray-500); margin: 0 0 0.875rem; padding-left: .6rem; }
        .lg-toc-side ol { list-style: none; margin: 0; padding: 0; counter-reset: toc; }
        .lg-toc-side li { margin: 0; }
        .lg-toc-side a { display: block; padding: .4rem .7rem; border-left: 2px solid var(--ks-gray-200); color: var(--ks-gray-500); text-decoration: none; font-size: 0.82rem; line-height: 1.35; transition: all .15s; }
        .lg-toc-side a:hover { color: var(--ks-green-dark); border-left-color: var(--ks-green); }
        .lg-toc-side a.active { color: var(--ks-green-dark); border-left-color: var(--ks-green); font-weight: 700; background: var(--ks-green-pale); }
        .lg-article { min-width: 0; max-width: 70ch; }

        @media (max-width: 900px) {
            .lg-content { padding: 2rem 1rem 3rem; }
            .lg-layout { grid-template-columns: 1fr; gap: 1.75rem; }
            .lg-toc-sticky { position: static; max-height: none; padding: 1.25rem; background: var(--ks-gray-50); border: 1px solid var(--ks-gray-200); border-radius: 14px; }
            .lg-toc-side ol { columns: 2; column-gap: 1.5rem; }
            .lg-toc-side a { border-left: none; padding: .3rem 0; break-inside: avoid; }
            .lg-toc-side a.active { background: none; }
            .lg-article { max-width: 100%; }
        }

        /* Voltar ao topo */
        .lg-totop { position: fixed; bottom: 24px; left: 24px; width: 46px; height: 46px; border-radius: 50%; background: var(--ks-white); border: 1px solid var(--ks-gray-200); box-shadow: 0 6px 20px rgba(15,23,42,.12); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--ks-green-dark); z-index: 90; transition: transform .15s, background .15s; }
        .lg-totop:hover { transform: translateY(-2px); background: var(--ks-green-pale); }

        .lg-section { margin-bottom: 2.5rem; scroll-margin-top: 100px; }
        .lg-section h2 { font-family: 'Syne', sans-serif; font-size: 1.5rem; font-weight: 800; color: var(--ks-black); margin: 0 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--ks-green); display: inline-block; }
        .lg-section h3 { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; color: var(--ks-black); margin: 1.5rem 0 0.75rem; }
        .lg-section p { color: var(--ks-gray-700); margin: 0 0 1rem; font-size: 0.95rem; line-height: 1.7; }
        .lg-section ul, .lg-section ol { color: var(--ks-gray-700); padding-left: 1.5rem; margin: 0.5rem 0 1rem; font-size: 0.95rem; line-height: 1.7; }
        .lg-section li { margin-bottom: 0.5rem; }
        .lg-section strong { color: var(--ks-black); font-weight: 700; }
        .lg-section a { color: var(--ks-green-dark); text-decoration: underline; font-weight: 600; }
        .lg-section a:hover { color: var(--ks-green); }
        .lg-section code { background: var(--ks-gray-100); padding: 0.125rem 0.375rem; border-radius: 4px; font-family: 'JetBrains Mono', monospace; font-size: 0.85em; color: var(--ks-gray-900); }

        .lg-highlight { background: var(--ks-green-pale); border-left: 4px solid var(--ks-green); padding: 1rem 1.25rem; border-radius: 0 10px 10px 0; margin: 1.25rem 0; }
        .lg-highlight p { margin: 0; color: var(--ks-gray-900); font-size: 0.9rem; }
        .lg-highlight strong { color: var(--ks-green-dark); }

        .lg-warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem 1.25rem; border-radius: 0 10px 10px 0; margin: 1.25rem 0; }
        .lg-warning p { margin: 0; color: #78350f; font-size: 0.9rem; }
        .lg-warning strong { color: #92400e; }

        .lg-contact-box { background: var(--ks-black); color: var(--ks-white); border-radius: 16px; padding: 2rem; margin: 3rem 0 0; text-align: center; }
        .lg-contact-box h3 { font-family: 'Syne', sans-serif; font-size: 1.25rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--ks-white); }
        .lg-contact-box p { color: #a3a3a3; margin: 0 0 1.25rem; font-size: 0.9rem; }
        .lg-contact-box a { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--ks-green); color: var(--ks-white); padding: 0.75rem 1.5rem; border-radius: 999px; font-family: 'Syne', sans-serif; font-weight: 700; font-size: 0.85rem; text-decoration: none; transition: background 0.15s; margin: 0 0.25rem; }
        .lg-contact-box a:hover { background: var(--ks-green-dark); }
        .lg-contact-box a.wa { background: #25d366; }
        .lg-contact-box a.wa:hover { background: #1eb555; }

        /* ============ FOOTER ============ */
        .lg-footer { background: var(--ks-black); color: var(--ks-white); padding: 2.5rem 1.5rem; }
        .lg-footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .lg-footer-copy { font-size: 0.75rem; color: #737373; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em; }
        .lg-footer-links { display: flex; gap: 1.5rem; }
        .lg-footer-links a { color: #a3a3a3; font-size: 0.85rem; text-decoration: none; }
        .lg-footer-links a:hover { color: var(--ks-white); }

        @media(max-width: 600px) { .lg-footer-inner { flex-direction: column; text-align: center; } .lg-footer-links { flex-wrap: wrap; justify-content: center; } }

        /* ============ WHATSAPP FLUTUANTE ============ */
        .ks-wa-float { position: fixed; bottom: 24px; right: 24px; background: #25d366; color: white; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 8px 24px rgba(37,211,102,0.4); z-index: 100; transition: transform 0.2s; }
        .ks-wa-float:hover { transform: scale(1.1); background: #1eb555; }
        .ks-wa-float::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 3px solid #25d366; opacity: 0.4; animation: wa-pulse 2s infinite; }
        @keyframes wa-pulse { 0%,100%{opacity:0.4;transform:scale(1)} 50%{opacity:0;transform:scale(1.3)} }
    </style>
</head>
<body>

    {{-- ============ HEADER ============ --}}
    <header class="lg-header">
        <div class="lg-header-inner">
            <a href="{{ url('/') }}" class="lg-logo-link" aria-label="KwanzaSafe — página inicial">
                <img src="{{ asset('assets/images/logos/logo.png') }}" alt="KwanzaSafe" class="lg-logo-img mobile">
                <img src="{{ asset('assets/images/logos/logo2.png') }}" alt="KwanzaSafe" class="lg-logo-img tablet">
                <img src="{{ asset('assets/images/logos/logo1.png') }}" alt="KwanzaSafe" class="lg-logo-img">
            </a>

            <a href="{{ url('/') }}" class="lg-back">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Voltar ao início
            </a>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="lg-hero">
        <div class="lg-hero-inner">
            <div class="lg-breadcrumb">
                <a href="{{ url('/') }}">Início</a>
                <span>›</span>
                <span>Legal</span>
            </div>
            <h1 class="lg-hero-title">{{ $pageTitle }}</h1>
            <p class="lg-hero-sub">{{ $pageDescription }}</p>
            <div class="lg-last-updated">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Última atualização: {{ $lastUpdated }}
            </div>
        </div>
    </section>

    {{-- ============ CONTENT (TOC sticky + artigo) ============ --}}
    <main class="lg-content">
        <div class="lg-layout">
            @isset($toc)
                <nav class="lg-toc-side" aria-label="Índice do documento">
                    <div class="lg-toc-sticky">
                        <p class="lg-toc-title">Índice</p>
                        {{ $toc }}
                    </div>
                </nav>
            @endisset

            <article class="lg-article">
                {{ $slot }}

        {{-- CONTACT BOX FINAL --}}
        <div class="lg-contact-box">
            <h3>Alguma dúvida sobre este documento?</h3>
            <p>A nossa equipa está pronta para esclarecer qualquer ponto.</p>
            <a href="mailto:suporte@kwanzasafe.com">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                suporte@kwanzasafe.com
            </a>
            <a href="https://wa.me/5511933579009?text=Olá%2C+tenho+uma+dúvida+sobre+o+documento+legal." target="_blank" rel="noopener" class="wa">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
                WhatsApp: +55 11 93357-9009
            </a>
        </div>
            </article>
        </div>
    </main>

    {{-- Voltar ao topo --}}
    <button class="lg-totop" type="button" aria-label="Voltar ao topo"
            onclick="window.scrollTo({top:0,behavior:'smooth'})"
            x-data="{ show: false }" @scroll.window="show = window.scrollY > 700" x-show="show" x-cloak>
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    {{-- ============ FOOTER ============ --}}
    <footer class="lg-footer">
        <div class="lg-footer-inner">
            <div class="lg-footer-copy">
                © 2026 KWANZASAFE · BNA Compliant · All Rights Reserved
            </div>
            <div class="lg-footer-links">
                <a href="{{ url('/') }}">Início</a>
                <a href="{{ route('terms') }}">Termos</a>
                <a href="{{ route('privacy') }}">Privacidade</a>
                <a href="https://wa.me/5511933579009" target="_blank" rel="noopener">Suporte</a>
            </div>
        </div>
    </footer>

    {{-- ============ WHATSAPP FLUTUANTE ============ --}}
    <a href="https://wa.me/5511933579009?text=Olá%2C+preciso+de+ajuda+com+o+KwanzaSafe." target="_blank" rel="noopener" class="ks-wa-float" aria-label="Contactar via WhatsApp">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

    {{-- Scroll-spy: destaca a secção ativa no índice --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const links = {};
            document.querySelectorAll('.lg-toc-side a[href^="#"]').forEach(function (a) {
                links[a.getAttribute('href').slice(1)] = a;
            });
            const sections = document.querySelectorAll('.lg-section[id]');
            if (!sections.length || !('IntersectionObserver' in window)) return;
            let current = null;
            const spy = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) current = e.target.id;
                });
                Object.keys(links).forEach(function (id) { links[id].classList.toggle('active', id === current); });
            }, { rootMargin: '-15% 0px -75% 0px', threshold: 0 });
            sections.forEach(function (s) { spy.observe(s); });
        });
    </script>

</body>
</html>